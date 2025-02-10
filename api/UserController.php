<?php

include_once './db.php';
use Controller as Controller;

class UserController extends Controller
{
    public function create()
    {
        $conn = $this->connectDatabase();
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], null);
        $result = $conn->query("SELECT COUNT(*) AS num FROM users WHERE email='$email' OR name='$name'")->fetch_assoc();
        if ($result['num'] > 0) {
            $this->response(403, ['message' => 'User already exists']);
        } else {
            $this->insertInto('users', [
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ]);
            $result = $conn->query("SELECT id from users WHERE email='$email' AND name='$name'")->fetch_assoc();
            $id = $result['id'];
            $uuid = $this->uuid();
            $expiry = date('Y-m-d', strtotime('+1 month', strtotime(date('Y-m-d'))));
            $this->insertInto('tokens', [
                'user_id' => $id,
                'token' => $uuid,
                'expiration' => $expiry,
            ]);
            $this->response(201, [
                'message' => 'User created successfully.',
                'token' => $uuid,
            ]);
        }
        $conn->close();
    }

    public function regenerateToken()
    {
        $conn = $this->connectDatabase();
        $email = $_POST['email'];
        $password = $_POST['password'];
        $user = $conn->query("SELECT id, password from users WHERE email='$email'")->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $uuid = $this->uuid();
            $expiry = date('Y-m-d', strtotime('+1 month', strtotime(date('Y-m-d'))));
            $this->deleteFrom('tokens', [
                'user_id' => $user['id'],
            ]);
            $this->insertInto('tokens', [
                'user_id' => $user['id'],
                'token' => $uuid,
                'expiration' => $expiry,
            ]);
            $this->response(200, [
                'message' => 'User token regenerated',
                'token' => $uuid,
            ]);
        }

        $conn->close();
    }
}
