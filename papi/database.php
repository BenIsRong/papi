<?php

namespace Papi;

use mysqli;

class Database extends Base
{
    /**
     * Connect to database
     *
     * @return mixed
     */
    private function connectDatabase(bool $withToken = true, string $token = '')
    {
        $env = parse_ini_file('.env');

        $conn = new mysqli($env['DB_HOST'], $env['DB_USERNAME'], $env['DB_PASSWORD'], $env['DB_NAME']);

        if ($conn->connect_error) {
            exit('Connection failed: '.$conn->connect_error);
        }

        if ($withToken) {
            return $this->checkToken($token) ? $conn : null;
        }

        return $conn;
    }

    /**
     * View data based on conditions
     *
     * @return array
     */
    public function view(string $table, array $conditions = [], bool $checkToken = true, string $token = '')
    {
        $conn = $this->connectDatabase($checkToken, $token);
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

    /**
     * View one based on conditions
     *
     * @return array
     */
    public function viewOne(string $table, array $conditions = [], bool $checkToken = true, string $token = '')
    {
        $conn = $this->connectDatabase($checkToken, $token);
        $res = [];
        $conditionString = $this->generateConditionString($conditions);

        $query = "SELECT * FROM $table WHERE ".$conditionString.' LIMIT 1';
        $result = $conn->query($query)->fetch_assoc();
        $conn->close();

        return $result;
    }

    /**
     * Create table based on given params
     *
     * @return mixed
     */
    public function createTable(string $table, array $data, string $primaryKey = '', bool $checkExists = true, bool $softDeleteable = true, bool $checkToken = true, string $token = '')
    {
        if ($softDeleteable) {
            array_push($data, [
                'name' => 'deleted_at',
                'type' => 'DATETIME',
                'null' => true,
            ]);
        }

        $conn = $this->connectDatabase($checkToken, $token);
        $query = $checkExists ? "CREATE TABLE $table(" : "CREATE TABLE IF NOT EXISTS $table(";

        foreach ($data as $idx => $column) {
            $query .= '`'.$column['name'].'` ';
            $query .= $column['type'].' ';
            $query .= $column['null'] ? 'NULL ' : 'NOT NULL ';
            $query .= (strtolower($column['type']) == 'int' && strtolower($column['name']) == strtolower($primaryKey)) ? 'AUTO_INCREMENT ' : '';
            $query .= array_key_exists('default', $column) ? "DEFAULT '".$column['default']."'" : '';
            $query .= $primaryKey == '' ? ($idx < count($data) - 1 ? ',' : '') : ',';
        }
        $query .= $primaryKey == '' ? ')' : "PRIMARY KEY (`$primaryKey`))";

        $result = $conn->query($query);
        $conn->close();

        return $result;
    }

    /**
     * Insert into table based on given params
     *
     * @return mixed
     */
    public function insertInto(string $table, array $data, bool $checkToken = true, string $token = '')
    {
        $conn = $this->connectDatabase($checkToken, $token);
        $data_keys = array_keys($data);
        $data_values = $this->dataToValues($data);

        $query = "INSERT INTO $table (".implode(', ', $data_keys).') VALUES ('.implode(', ', $data_values).')';

        $result = $conn->query($query);
        $conn->close();

        return $result;
    }

    /**
     * Insert multiple rows into table based on given params
     *
     * @return mixed
     */
    public function insertMultiple(string $table, array $columns, array $datas, bool $checkToken = true, string $token = '')
    {
        $conn = $this->connectDatabase($checkToken, $token);
        $query = "INSERT INTO $table (".implode(', ', $columns).') VALUES ';

        foreach ($datas as $data) {
            $query = $query.'('.implode(', ', $this->dataToValues($data)).'),';
        }

        $query = rtrim($query, ',');

        $result = $conn->query($query);
        $conn->close();

        return $result;
    }

    /**
     * Update row in table based on given params
     *
     * @return mixed
     */
    public function updateInto(string $table, array $data, array $conditions = [], bool $checkToken = true, string $token = '')
    {
        $conn = $this->connectDatabase($checkToken, $token);
        [$data, $conditions] = $this->dataToValues($data, $conditions, true);

        $query = "UPDATE `$table` SET ".implode(', ', $data).' WHERE '.$conditions;

        $result = $conn->query($query);
        $conn->close();

        return $result;
    }

    public function getCount(string $table, array $conditions = [], bool $checkToken = true, string $token = '')
    {
        $conn = $this->connectDatabase($checkToken, $token);
        $conditionString = $this->generateConditionString($conditions);

        $result = $conn->query("SELECT COUNT(*) as num FROM $table WHERE $conditionString")->fetch_assoc();
        $conn->close();

        return $result['num'];
    }

    /**
     * Delete row in table based on given params
     *
     * @return mixed
     */
    public function deleteFrom(string $table, array $conditions = [], bool $checkToken = true, string $token = '')
    {
        $conn = $this->connectDatabase($checkToken, $token);
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

    /**
     * Delete all in given table
     *
     * @return mixed
     */
    public function deleteAll(string $table, bool $checkToken = true, string $token = '')
    {
        $conn = $this->connectDatabase($checkToken, $token);

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

    /**
     * Check if table exists in database
     *
     * @return bool
     */
    public function tableExists(string $table, bool $checkToken = true, string $token = '')
    {
        $conn = $this->connectDatabase($checkToken, $token);

        $result = $conn->query("SHOW TABLES LIKE '$table'");

        return mysqli_num_rows($result) == 1;
    }

    public function columnExists(string $table, string $column, bool $checkToken = true, string $token = '')
    {
        $conn = $this->connectDatabase($checkToken, $token);
        $result = $conn->query("SHOW COLUMNS FROM $table");

        while ($row = $result->fetch_assoc()) {
            if ($row['Field'] == $column) {
                return true;
            }
        }

        return false;
    }

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
     * Generate a condition string for queries with WHERE clause
     *
     * @return string
     */
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

    /**
     * Get data from given array based on conditions
     *
     * @return array
     */
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
