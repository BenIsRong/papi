<?php

namespace Papi\Auth;

use Papi\Database;

class Auth extends Database
{
    private $table = 'users';

    /**
     * Register a user into the database
     *
     * @return bool
     */
    public function register(array $request)
    {
        if ($this->checkIfAllKeysExists(array_keys($request), ['name', 'username', 'email', 'password', 'role'])) {
            if ($this->validateEmail($request['email']) && $this->validatePassword($request['password']) && ! $this->checkUserExists($request['email'], $request['password'])) {
                $creatUserResult = $this->insertInto($this->table, [
                    'name' => $request['name'],
                    'username' => $request['username'],
                    'email' => $request['email'],
                    'password' => password_hash($request['password'], PASSWORD_BCRYPT),
                    'role_id' => $request['role'],
                ], false);
                if ($creatUserResult) {
                    return [
                        'uuid' => $this->registerToken($request['email'], $request['password']),
                    ];
                }
            }
        }

        return false;
    }

    /**
     * Update a user's name and username
     *
     * @return bool
     */
    public function updateNames(?string $name, ?string $username, string $token)
    {
        $user = $this->getUserFromToken($token);
        if (! is_null($user)) {
            $this->updateInto($this->table, [
                'name' => $name ?? $user['name'],
                'username' => $username ?? $user['username'],
            ], [
                [
                    'col' => 'id',
                    'operator' => '=',
                    'value' => $user['id'],
                ],
            ], true, $token);

            return true;
        }

        return false;
    }

    /**
     * Update a user's email
     *
     * @return bool
     */
    public function updateEmail(string $email, string $token)
    {
        $user = $this->getUserFromToken($token);
        if (! is_null($user) && $this->validateEmail($email)) {
            $this->updateInto($this->table, [
                'email' => $email,
            ], [
                [
                    'col' => 'id',
                    'operator' => '=',
                    'value' => $user['id'],
                ],
            ], true, $token);

            return true;
        }

        return false;
    }

    /**
     * Update a user's password
     *
     * @return bool
     */
    public function updatePassword(string $oldPassword, string $newPassword, string $token)
    {
        $user = $this->getUserFromToken($token);
        if (! is_null($user) && password_verify($oldPassword, $user['password']) && $this->validatePassword($newPassword)) {
            $this->updateInto($this->table, [
                'password' => password_hash($newPassword, PASSWORD_BCRYPT),
            ], [
                [
                    'col' => 'id',
                    'operator' => '=',
                    'value' => $user['id'],
                ],
            ], true, $token);

            return true;
        }

        return false;
    }

    /**
     * Update a user's role
     *
     * @return bool
     */
    public function updateRole(int $role, string $token)
    {
        $user = $this->getUserFromToken($token);
        if (! is_null($user)) {
            $this->updateInto($this->table, [
                'role_id' => $role,
            ], [
                [
                    'col' => 'id',
                    'operator' => '=',
                    'value' => $user['id'],
                ],
            ], true, $token);

            return true;
        }

        return false;
    }

    /**
     * Register a token to user based on credentials
     *
     * @return mixed
     */
    public function registerToken(string $email, string $password)
    {
        $user = $this->viewOne($this->table, [[
            'col' => 'email',
            'operator' => '=',
            'value' => $email,
        ]], false);
        if (password_verify($password, $user['password'])) {
            $id = $user['id'];
            $uuid = $this->uuid();
            $expiry = date('Y-m-d', strtotime('+1 month', strtotime(date('Y-m-d'))));
            if ($this->getCount('tokens', [['col' => 'user_id', 'operator' => '=', 'value' => $id]], false) == 0) {
                $this->insertInto('tokens', [
                    'user_id' => $id,
                    'token' => $uuid,
                    'expiration' => $expiry,
                ], false);
            } else {
                $this->updateInto('tokens', [
                    'user_id' => $id,
                    'token' => $uuid,
                    'expiration' => $expiry,
                ], [
                    [
                        'col' => 'user_id',
                        'operator' => '=',
                        'value' => $id,
                    ],
                ], false);
            }

            return $uuid;
        }

        return false;
    }

    /**
     * Check if user is an Admin or not
     *
     * @return bool
     */
    public function isAdmin(string $token)
    {
        $user = $this->getUserFromToken($token);
        $role = $this->viewOne('roles', [
            [
                'col' => 'id',
                'operator' => '=',
                'value' => $user['role_id'],
            ],
        ], true, $token);

        return str_contains($role['name'], 'admin') ? true : false;
    }

    /**
     * Check if user has given role
     *
     * @return bool
     */
    public function haveRole(string $token, string $role)
    {
        $user = $this->getUserFromToken($token);
        if (! is_null($user)) {
            $userRole = $user['role_id'];
            $res = $this->viewOne('roles', [
                [
                    'col' => 'id',
                    'operator' => '=',
                    'value' => $role,
                ],
            ], false);

            if (! is_null($res)) {
                if ($userRole == $res['id']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if user has given permission
     *
     * @return bool
     */
    public function havePermission(string $token, string $permission)
    {
        $user = $this->getUserFromToken($token);

        if (! is_null($user)) {
            $permissions = $this->view('permissions', [
                [
                    'col' => 'id',
                    'operator' => '=',
                    'value' => $user['role_id'],
                ],
            ], false);

            $permissions = array_map(function ($permission) {
                return strtolower($permission['name']);
            }, $permissions);

            return in_array(strtolower($permission), $permissions);
        }

        return false;
    }

    /**
     * get the User from given Token
     *
     * @return array|null
     */
    public function getUserFromToken(string $token)
    {
        $userId = $this->viewOne('tokens', [
            [
                'col' => 'token',
                'operator' => '=',
                'value' => $token,
            ],
        ], false)['user_id'];

        $user = $this->viewOne($this->table, [
            [
                'col' => 'id',
                'operator' => '=',
                'value' => $userId,
            ],
        ], false);

        return $user;
    }

    /**
     * Get user role id
     *
     * @return int|null
     */
    public function getRoleIdofUser(string $token)
    {
        $user = $this->getUserFromToken($token);

        return $user['role_id'];
    }

    /**
     * Get token of Request
     *
     * @return string
     */
    public function getToken()
    {
        $headers = apache_request_headers();
        $token = explode(' ', $headers['Authorization']);
        $token = end($token);

        return $token;
    }

    /**
     * Check if user exists using email and password
     *
     * @return bool
     */
    public function checkUserExists(string $email, string $password)
    {
        $result = $this->viewOne($this->table, [
            [
                'col' => 'email',
                'operator' => '=',
                'value' => $email,
            ],
        ], false);

        if ($result) {
            return password_verify($password, $result['password']);
        }

        return false;
    }
}
