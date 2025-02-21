<?php

namespace Papi;

use Papi\Databases\MySQL;

if (! defined('REQUEST_ALL')) {
    define('REQUEST_ALL', 0);
}

if (! defined('REQUEST_FORM_ONLY')) {
    define('REQUEST_FORM_ONLY', 1);
}

if (! defined('REQUEST_PARAMS_ONLY')) {
    define('REQUEST_PARAMS_ONLY', 2);
}

class Database extends Base
{
    private $db;

    /**
     * View data based on conditions
     *
     * @return array
     */
    public function view(string $table, array $conditions = [], bool $checkToken = true, string $token = '')
    {
        $this->getDatabaseType();

        return $this->db->{'view'}($table, $conditions, $checkToken, $token);
    }

    /**
     * View one based on conditions
     *
     * @return array
     */
    public function viewOne(string $table, array $conditions = [], bool $checkToken = true, string $token = '')
    {
        $this->getDatabaseType();

        return $this->db->{'viewOne'}($table, $conditions, $checkToken, $token);
    }

    /**
     * Create table based on given params
     *
     * @return mixed
     */
    public function createTable(string $table, array $data, string $primaryKey = '', bool $checkExists = true, bool $softDeleteable = true, bool $checkToken = true, string $token = '')
    {
        $this->getDatabaseType();

        return $this->db->{'createTable'}($table, $data, $primaryKey, $checkExists, $softDeleteable, $checkToken, $token);
    }

    /**
     * Insert into table based on given params
     *
     * @return mixed
     */
    public function insertInto(string $table, array $data, bool $checkToken = true, string $token = '')
    {
        $this->getDatabaseType();

        return $this->db->{'insertInto'}($table, $data, $checkToken, $token);
    }

    /**
     * Insert multiple rows into table based on given params
     *
     * @return mixed
     */
    public function insertMultiple(string $table, array $columns, array $datas, bool $checkToken = true, string $token = '')
    {
        $this->getDatabaseType();

        return $this->db->{'insertMultiple'}($table, $columns, $datas, $checkToken, $token);
    }

    /**
     * Update row in table based on given params
     *
     * @return mixed
     */
    public function updateInto(string $table, array $data, array $conditions = [], bool $checkToken = true, string $token = '')
    {
        $this->getDatabaseType();

        return $this->db->{'updateInto'}($table, $data, $conditions, $checkToken, $token);
    }

    /**
     * Get count in the table based on conditions
     *
     * @return int
     */
    public function getCount(string $table, array $conditions = [], bool $checkToken = true, string $token = '')
    {
        $this->getDatabaseType();

        return $this->db->{'getCount'}($table, $conditions, $checkToken, $token);
    }

    /**
     * Delete row in table based on given params
     *
     * @return mixed
     */
    public function deleteFrom(string $table, array $conditions = [], bool $checkToken = true, string $token = '')
    {
        $this->getDatabaseType();

        return $this->db->{'deleteFrom'}($table, $conditions, $checkToken, $token);
    }

    /**
     * Delete all in given table
     *
     * @return mixed
     */
    public function deleteAll(string $table, bool $checkToken = true, string $token = '')
    {
        $this->getDatabaseType();

        return $this->db->{'deleteAll'}($table, $checkToken, $token);
    }

    /**
     * Check if table exists in database
     *
     * @return bool
     */
    public function tableExists(string $table, bool $checkToken = true, string $token = '')
    {
        $this->getDatabaseType();

        return $this->db->{'tableExists'}($table, $checkToken, $token);
    }

    public function columnExists(string $table, string $column, bool $checkToken = true, string $token = '')
    {
        $this->getDatabaseType();

        return $this->db->{'columnExists'}($table, $column, $checkToken, $token);
    }

    /**
     * Check if API token exists
     *
     * @return bool
     */
    // TODO: check if expired or not
    public function checkToken(string $token = '')
    {
        $this->getDatabaseType();

        return $this->db->{'checkToken'}($token);
    }

    /**
     * Get which type of database that the user is using
     */
    private function getDatabaseType()
    {
        $dbType = $this->jsonToArray('config.json', 'db');

        switch ($dbType) {
            case 'mysql':
                $dbType = MySQL::class;
                break;
            default:
                $dbType = MySQL::class;
                break;
        }

        $this->db = new $dbType;
    }
}
