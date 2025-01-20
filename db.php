<?php
class Controller {

    private $conn;

    public function connectDatabase(){
        $host = "localhost";
        $username = "root";
        $password = "";
        $db = "api";
        
        $conn = new mysqli($host, $username, $password, $db);
        
        if($conn->connect_error){
            die("Connection failed: ".$conn->connect_error);
        }

        $this->conn = $conn;

        return $conn;
    }

    public function checkToken(){
        $headers = apache_request_headers();
        $token = explode(" ", $headers['Authorization']);
        $token = end($token);

        $result = $this->conn->query("SELECT COUNT(*) as num FROM tokens WHERE token='$token'")->fetch_assoc();
        if($result['num'] > 0){
            return true;
        }else{
            return false;
        }
        $this->conn->close();
    }

    public function insert(string $table, array $data){
        $data_keys = array_keys($data);
        $data_values = $this->dataToValues($data);

        $query = "INSERT INTO $table (" . implode(", ", $data_keys) . ") VALUES (" . implode(", ", $data_values) . ")";
        
        $result = $this->conn->query($query);
        return $result;
    }

    public function response(int $responseCode, ?array $res = []){
        http_response_code($responseCode);
        echo json_encode($res);
    }

    private function dataToValues(array $data){
        $res = [];

        if(! array_is_list($data)){
            $data = array_values($data);
        }

        foreach($data as $value){
            if(! is_numeric($value)){
                array_push($res, "'" . $value . "'");
            }else{
                array_push($res, $value);
            }
        }

        return $res;
    }
}