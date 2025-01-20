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

    public function checkToken(){
        $conn = $this->connectDatabase();
        $headers = apache_request_headers();
        $token = explode(" ", $headers['Authorization']);
        $token = end($token);

        $result = $conn->query("SELECT COUNT(*) as num FROM tokens WHERE token='$token'")->fetch_assoc();
        if($result['num'] > 0){
            return true;
        }else{
            return false;
        }
        $conn->close();
    }

    public function insertInto(string $table, array $data){
        $conn = $this->connectDatabase();
        $data_keys = array_keys($data);
        $data_values = $this->dataToValues($data);

        $query = "INSERT INTO $table (" . implode(", ", $data_keys) . ") VALUES (" . implode(", ", $data_values) . ")";
        
        $result = $conn->query($query);
        $conn->close();

        return $result;
    }

    public function updateInto(string $table, array $data, array $conditions=[]){
        $conn = $this->connectDatabase();
        [$data, $conditions] = $this->dataToValues($data, $conditions, true);

        $query = "UPDATE `$table` SET " . implode(", ", $data) . " WHERE " . (count($conditions) > 1 ? implode(" AND ", $conditions) : $conditions[0]);
        
        $result = $conn->query($query);
        $conn->close();

        return $result;
    }

    public function response(int $responseCode, ?array $res = []){
        http_response_code($responseCode);
        echo json_encode($res);
    }

    private function dataToValues(array $data, ?array $conditions=[], bool $update=false){
        if(! $update){
            $vals = [];
            if(! array_is_list($data)){
                $data = array_values($data);
            }
    
            foreach($data as $value){
                if(! is_numeric($value)){
                    array_push($vals, "'$value'");
                }else{
                    array_push($vals, $value);
                }
            }
            return $vals;
        }else{
            $vals = [];
            $where = [];
            foreach($data as $key=>$value){
                if(! is_numeric($value)){
                    array_push($vals, "`$key`='$value'");
                }else{
                    array_push($vals, "`$key`=$value");
                }
            }

            if(count($conditions) != 0){
                foreach($conditions as $key=>$value){
                    if(! is_numeric($value)){
                        array_push($where, "`$key`='$value'");
                    }else{
                        array_push($where, "`$key`=$value");
                    }
                }
            }else{
                array_push($where, 1);
            }

            return [$vals, $where];
        }
    }
}