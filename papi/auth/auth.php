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
    public function checkToken()
    {
        $conn = $this->connectDatabase(false);
        $headers = apache_request_headers();
        $token = explode(' ', $headers['Authorization']);
        $token = end($token);

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
    public function register(string $name, string $username, string $email, string $password, bool $admin, int $role)
    {
        if ($this->validateEmail($email)) {
            return $this->insertInto('users', [
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'admin' => $admin,
                'role_id' => $role,
            ], false);
        } else {
            return false;
        }
    }

    /**
     * Register a token to user based on credentials
     */
    public function registerToken(string $email, string $password)
    {
        $conn = $this->connectDatabase(false);
        $result = $conn->query("SELECT id,password from users WHERE email='$email'")->fetch_assoc();
        if (password_verify($password, $result['password'])) {
            $id = $result['id'];
            $uuid = $this->uuid();
            $expiry = date('Y-m-d', strtotime('+1 month', strtotime(date('Y-m-d'))));
            $this->insertInto('tokens', [
                'user_id' => $id,
                'token' => $uuid,
                'expiration' => $expiry,
            ], false);

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
    public function isAdmin(string $email, string $password, string $token)
    {
        $result = $this->viewOne('users', [
            [
                'col' => 'email',
                'operator' => '=',
                'value' => $email,
            ],
        ], false);

        return $result['admin'];
    }
}
