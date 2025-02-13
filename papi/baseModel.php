<?php

namespace Papi;

use DateTime;
use Exception;

abstract class BaseModel extends Database
{
    protected $table;

    protected $pk = 'id';

    /**
     * Check if record with primary key exists,
     * If exists, then update, else create
     *
     * @return mixed
     */
    protected function insertOrUpdate(array $data, array $conditions = [], bool $checkToken = true)
    {
        $this->checkTable();
        if ($this->getCount($this->table, $conditions) == 0) {
            return $this->insertInto($this->table, $data, $checkToken);
        } else {
            return $this->updateInto($this->table, $data, $conditions, $checkToken);
        }
    }

    /**
     * Soft deletes the record, such that it still exists, but would not be brought up in queries
     *
     * @return mixed
     */
    protected function softDelete(array $conditions = [], bool $checkToken = true)
    {
        $this->checkTable();

        return $this->updateInto($this->table, ['deleted_at', new DateTime], $conditions, $checkToken);
    }

    /**
     * Retrieve gets all rows according to conditions
     * Results excludes soft deletes as they are "deleted"
     *
     * @return mixed
     */
    protected function retrieve(array $conditions = [], bool $checkToken = true)
    {
        $this->checkTable();
        if (! array_key_exists('deleted_at', $conditions)) {
            array_push($conditions, [
                'col' => 'deleted_at',
                'operator' => '=',
                'value' => 'NULL',
            ]);
        }

        return $this->view($this->table, $conditions, $checkToken);
    }

    private function checkTable()
    {
        if (is_null($this->table)) {
            $className = debug_backtrace(2)[1]['class'];
            $className = explode('\\', $className);
            $className = $this->pluralise($className[1]);
            if ($this->tableExists($className, false)) {
                $this->table = $this->tableExists($className, false);
            } else {
                throw new Exception("Table not found! Please declare it by doing\nprotected \$table = '[table_name]'.");
            }
        } else {
            if (! $this->tableExists($this->table, false)) {
                throw new Exception("Table not found! Please declare it by doing\nprotected \$table = '[table_name]'.");
            }
        }
    }
}
