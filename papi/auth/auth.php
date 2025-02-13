<?php

namespace Papi\Auth;

use Papi\Database;

class Auth extends Database
{
    /**
     * Check if API token exists
     *
     * @return bool
     */
    // TODO: check if expired or not
    public function checkToken(string $token = '')
    {
        $conn = $this->connectDatabase(false);
        if ($token == '') {
            $headers = apache_request_headers();
            $token = explode(' ', $headers['Authorization']);
            $token = end($token);
        }

        $result = $conn->query("SELECT COUNT(*) as num FROM tokens WHERE token='$token'")->fetch_assoc();
        $conn->close();

        if ($result['num'] > 0) {
            return true;
        } else {
            return false;
        }
    }

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
        $conn = $this->connectDatabase(false);
        $user = $conn->query("SELECT id,password from users WHERE email='$email'")->fetch_assoc();
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

        $conn->close();

        return false;
    }

    /**
     * Check if user is admin or not
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
