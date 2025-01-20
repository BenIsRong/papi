<?php

class Controller
{
    public function connectDatabase()
    {
        $env = parse_ini_file('.env');

        $conn = new mysqli($env['DB_HOST'], $env['DB_USERNAME'], $env['DB_PASSWORD'], $env['DB_NAME']);

        if ($conn->connect_error) {
            exit('Connection failed: '.$conn->connect_error);
        }

        return $conn;
    }

    public function checkToken()
    {
        $conn = $this->connectDatabase();
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

    public function view(string $table, array $conditions = [])
    {
        $conn = $this->connectDatabase();
        $res = [];
        $conditionString = $this->generateConditionString($conditions);

        $query = "SELECT * FROM $table WHERE ".$conditionString;
        $result = $conn->query($query);
        $conn->close();

        while ($row = $result->fetch_assoc()) {
            array_push($res, $row);
        }

        return $res;
    }

    public function insertInto(string $table, array $data)
    {
        $conn = $this->connectDatabase();
        $data_keys = array_keys($data);
        $data_values = $this->dataToValues($data);

        $query = "INSERT INTO $table (".implode(', ', $data_keys).') VALUES ('.implode(', ', $data_values).')';

        $result = $conn->query($query);
        $conn->close();

        return $result;
    }

    public function insertMultiple(string $table, array $columns, array $datas)
    {
        $conn = $this->connectDatabase();
        $query = "INSERT INTO $table (".implode(', ', $columns).') VALUES ';
        foreach ($datas as $data) {
            $query = $query.'('.implode(', ', $this->dataToValues($data)).'),';
        }

        $query = rtrim($query, ',');

        $result = $conn->query($query);
        $conn->close();

        return $result;
    }

    public function updateInto(string $table, array $data, array $conditions = [])
    {
        $conn = $this->connectDatabase();
        [$data, $conditions] = $this->dataToValues($data, $conditions, true);

        $query = "UPDATE `$table` SET ".implode(', ', $data).' WHERE '.$conditions;

        $result = $conn->query($query);
        $conn->close();

        return $result;
    }

    public function deleteFrom(string $table, array $conditions = [])
    {
        $conn = $this->connectDatabase();
        $where = [];

        if (count($conditions) != 0) {
            $where = $this->generateConditionString($conditions);
        } else {
            $conn->close();

            return false;
        }

        $select = $conn->query("SELECT COUNT(*) as num FROM $table WHERE ".$where)->fetch_assoc();

        if ($select['num'] > 0) {
            $query = "DELETE FROM `$table` WHERE ".$where;
            $result = $conn->query($query);
            $conn->close();

            return $result;
        } else {
            $conn->close();

            return false;
        }

    }

    public function deleteAll(string $table)
    {
        $conn = $this->connectDatabase();

        $select = $conn->query("SELECT COUNT(*) as num FROM $table")->fetch_assoc();

        if ($select['num'] > 0) {
            $query = "DELETE FROM `$table` WHERE 1";
            $result = $conn->query($query);
            $conn->close();

            return $result;
        } else {
            $conn->close();

            return false;
        }
    }

    public function response(int $responseCode, array $res = [])
    {
        http_response_code($responseCode);
        echo json_encode($res);
    }

    private function generateConditionString(array $conditions)
    {
        if (count($conditions) > 0) {
            $conditionStrings = [];

            foreach ($conditions as $condition) {
                if (! is_numeric($condition['value'])) {
                    $conditionString = $condition['col'].$condition['operator']."'".$condition['value']."'";
                    array_push($conditionStrings, $conditionString);
                } else {
                    $conditionString = $condition['col'].$condition['operator'].$condition['value'];
                    array_push($conditionStrings, $conditionString);
                }
            }

            return implode(' AND ', $conditionStrings);
        } else {
            return '1';
        }
    }

    private function dataToValues(array $data, array $conditions = [], bool $update = false)
    {
        if (! $update) {
            $vals = [];
            if (! array_is_list($data)) {
                $data = array_values($data);
            }

            foreach ($data as $value) {
                if (! is_numeric($value)) {
                    array_push($vals, "'$value'");
                } else {
                    array_push($vals, $value);
                }
            }

            return $vals;
        } else {
            $vals = [];
            $where = '';
            foreach ($data as $key => $value) {
                if (! is_numeric($value)) {
                    array_push($vals, "`$key`='$value'");
                } else {
                    array_push($vals, "`$key`=$value");
                }
            }

            if (count($conditions) != 0) {
                $where = $this->generateConditionString($conditions);
            } else {
                $where = '1';
            }

            return [$vals, $where];
        }
    }
}
