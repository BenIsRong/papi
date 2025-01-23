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
        $token = filter_var($token, FILTER_SANITIZE_SPECIAL_CHARS);
        $query = $conn->prepare('SELECT COUNT(*) as num FROM tokens WHERE token=?');
        $query->bind_param('s', $token);
        $query->execute();

        $result = $query->get_result()->fetch_assoc();
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
        [$conditionString, $types, $conditions] = $this->generateConditionString($conditions);

        $query = "SELECT * FROM $table WHERE ".$conditionString;

        $query = $conn->prepare("SELECT * FROM $table WHERE ".$conditionString);
        $query->bind_param($types, ...$conditions);
        $query->execute();

        $result = $query->get_result();
        while ($row = $result->fetch_all()) {
            array_push($res, $row);
        }
        $conn->close();

        return $res;
    }

    public function insertInto(string $table, array $data)
    {
        $conn = $this->connectDatabase();
        $dataKeys = array_keys($data);
        $dataValues = $this->dataToValues($data);

        echo "INSERT INTO $table (".implode(', ', $dataKeys).') VALUES ('.rtrim(str_repeat('?,', count($dataValues)), ',').')';

        $query = $conn->prepare("INSERT INTO $table (".implode(', ', $dataKeys).') VALUES ('.rtrim(str_repeat('?,', count($dataValues)), ',').')');
        $query->bind_param($this->generateBindParamTypes($dataValues), ...$dataValues);
        $result = $query->execute();
        $conn->close();

        return $result;
    }

    public function insertMultiple(string $table, array $columns, array $datas)
    {
        $conn = $this->connectDatabase();
        $queryString = "INSERT INTO $table (".implode(', ', $columns).') VALUES ';
        $values = [];

        foreach ($datas as $data) {
            $value = $this->dataToValues($data);
            $values = [...$values, ...$value];
            $queryString = $queryString.'('.rtrim(str_repeat('?,', count($value)), ',').'),';
        }

        $queryString = rtrim($queryString, ',');

        $query = $conn->prepare($queryString);
        $query->bind_param($this->generateBindParamTypes($values), ...$values);
        $result = $query->execute();
        $conn->close();

        return $result;
    }

    public function updateInto(string $table, array $data, array $conditions = [])
    {
        $conn = $this->connectDatabase();
        $dataKeys = array_keys($data);
        $dataValues = $this->dataToValues($data);

        [$conditionString, $types, $conditions] = $this->generateConditionString($conditions);

        $queryString = "UPDATE `$table` SET ";

        foreach ($dataKeys as $dataKey) {
            $queryString = $queryString."`$dataKey`=?,";
        }

        $queryString = rtrim($queryString, ',').' WHERE '.$conditionString;

        $query = $conn->prepare($queryString);
        $query->bind_param($this->generateBindParamTypes($dataValues).$types, ...[...$dataValues, ...$conditions]);

        $result = $query->execute();
        $conn->close();

        return $result;
    }

    public function deleteFrom(string $table, array $conditions = [])
    {
        $conn = $this->connectDatabase();

        if (count($conditions) != 0) {
            [$conditionString, $types, $conditions] = $this->generateConditionString($conditions);

            // $selectQuery = $conn->prepare("SELECT COUNT(*) as num FROM `$table` WHERE " . $conditionString);
            // $selectQuery->bind_param($types, ...$conditions);
            // $selectResult = $selectQuery->get_result();
            // $selectResult = $selectResult->fetch_all(MYSQLI_ASSOC);
            // if($selectResult['num'] > 0){
            //     $query = $conn->prepare("DELETE FROM `$table` WHERE " . $conditionString);
            //     $query->bind_param($types, ...$conditions);
            //     $result = $query->execute();

            //     $conn->close();
            //     return $result;
            // }

            $query = $conn->prepare("DELETE FROM `$table` WHERE ".$conditionString);
            $query->bind_param($types, ...$conditions);
            $result = $query->execute();

            $conn->close();

            return $result;

            $conn->close();

            return false;
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

    public function response(int $responseCode = 500, array $res = [])
    {
        http_response_code($responseCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($res);
    }

    private function generateBindParamTypes(array $data): string
    {
        $types = [];
        foreach ($data as $value) {
            if (! is_numeric($value)) {
                array_push($types, 's');
            } else {
                array_push($types, str_contains($value, '.') ? 'd' : 'i');
            }
        }

        return implode('', $types);

    }

    private function generateConditionString(array $conditions): array
    {
        $preparedStatements = [];
        $preparedStatementTypes = [];
        if (count($conditions) > 0) {
            $conditionStrings = [];

            foreach ($conditions as $condition) {
                $preparedStatement = $condition['col'].$condition['operator'].'?';

                array_push($preparedStatements, $preparedStatement);
                array_push($conditionStrings, filter_var($condition['value'], FILTER_SANITIZE_SPECIAL_CHARS));

                if (! is_numeric($condition['value'])) {
                    array_push($preparedStatementTypes, 's');
                } else {
                    array_push($preparedStatementTypes, str_contains($condition['value'], '.') ? 'd' : 'i');
                }
            }

            return [implode(' AND ', $preparedStatements), $this->generateBindParamTypes($conditionStrings), $conditionStrings];
        } else {
            return '1';
        }
    }

    private function dataToValues(array $data)
    {
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
    }
}
