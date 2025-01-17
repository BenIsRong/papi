<?php
include('./db.php');
use Controller as Controller;

$method = $_SERVER['REQUEST_METHOD'];
$headers = apache_request_headers();

class UserController extends Controller{
    private function get_bearer_token(string $auth):string | false{
        $strings = explode(' ', $auth);
        return strtolower($strings[0]) == 'bearer' ? $strings[1] : false;
    }

    public function createUser(){
        $conn = $this->connectDatabase();
        echo $_SERVER['REQUEST_URI'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], null);
        $query = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
        $conn->query($query);
        echo json_encode(["message" => "User added successfully"]); 
        $conn->close();
    }
}