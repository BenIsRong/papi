<?php
include('./db.php');
use Controller as Controller;

$method = $_SERVER['REQUEST_METHOD'];

class UserController extends Controller{
    public function create(){
        $conn = $this->connectDatabase();
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], null);
        $result = $conn->query("SELECT COUNT(*) AS num FROM users WHERE email='$email' AND name='$name'")->fetch_assoc();
        if($result['num'] > 0){
            http_response_code(403);
            echo json_encode(["message" => "User already exists"]); 
        }else{
            $conn->query("INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')");
            $result = $conn->query("SELECT id from users WHERE email='$email' AND name='$name'")->fetch_assoc();
            $id = $result['id'];
            $uuid = $this->uuid();
            $expiry = date('Y-m-d', strtotime('+1 month', strtotime(date('Y-m-d'))));
            $conn->query("INSERT INTO tokens (user_id, token, expiration) VALUES('$id','$uuid','$expiry')");
            http_response_code(201);
            echo json_encode(["message" => "User created successfully.", "token"=>$uuid]); 
        }
        $conn->close();
    }

    private function uuid()
    {
      $data = random_bytes(16);
    
      $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
      $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        
      return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}