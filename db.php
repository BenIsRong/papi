<?php
class Controller {
    public function connectDatabase(){
        $host = "localhost";
        $username = "root";
        $password = "";
        $db = "api";
        
        $conn = new mysqli($host, $username, $password, $db);
        
        if($conn->connect_error){
            die("Connection failed: ".$conn->connect_error);
        }

        return $conn;
    }
}