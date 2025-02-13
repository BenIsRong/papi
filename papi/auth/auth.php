<?php

namespace Papi\Auth;

use Papi\Database;

abstract class Auth extends Database
{
    /**
     * Register a user into the database
     *
     * @return bool
     */
    public function register(string $name, string $username, string $email, string $password, int $role)
    {
        if ($this->validateEmail($email)) {
            return $this->insertInto('users', [
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'role_id' => $role,
            ], false);
        } else {
            return false;
        }
    }

    /**
     * Register a token to user based on credentials
     *
     * @return mixed
     */
    public function registerToken(string $email, string $password)
    {
        $user = $this->viewOne('users', [
            'col' => 'email',
            'operator' => '=',
            'value' => $email
        ], false);
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

        $user = $this->viewOne('users', [
            [
                'col' => 'id',
                'operator' => '=',
                'value' => $userId,
            ],
        ], false);

        return $user;
    }
}
