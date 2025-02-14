<?php

namespace Papi;

use DateTime;
use Exception;

abstract class BaseModel extends Database
{
    protected $table;

    protected $pk = 'id';

    protected $softDelete = false;

    /**
     * Insert into table based on given params
     *
     * @return mixed
     */
    protected function create(array $data, bool $checkToken = true)
    {
        $this->checkTable();

        return $this->insertInto($this->table, $data, $checkToken);
    }

    /**
     * Check if record with primary key exists,
     * If exists, then update, else create
     *
     * @return mixed
     */
    protected function createOrUpdate(array $data, array $conditions = [], bool $checkToken = true)
    {
        $this->checkTable();
        if ($this->getCount($this->table, $conditions) == 0) {
            return $this->insertInto($this->table, $data, $checkToken);
        } else {
            return $this->updateInto($this->table, $data, $conditions, $checkToken);
        }
    }

    /**
     * Retrieve one row based on condition
     * Results excludes soft deletes as they are "deleted"
     *
     * @return mixed
     */
    protected function getOne(array $conditions = [], bool $checkToken = true)
    {
        $this->checkTable();
        if (! array_key_exists('deleted_at', $conditions)) {
            array_push($conditions, [
                'col' => 'deleted_at',
                'operator' => '=',
                'value' => 'NULL',
            ]);
        }

        return $this->viewOne($this->table, $conditions, $checkToken);
    }

    /**
     * Retrieve gets all rows according to conditions
     * Results excludes soft deletes as they are "deleted"
     *
     * @return mixed
     */
    protected function getAll(array $conditions = [], bool $checkToken = true)
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

    /**
     * Update row based on condition
     *
     * @return mixed
     */
    protected function update(array $data, array $conditions = [], bool $checkToken = true)
    {
        $this->checkTable();
        $this->updateInto($this->table, $data, $conditions, $checkToken);
    }

    /**
     * Deletes the record
     * Depending on $softDelete, it will either do a soft delete or total removal
     *
     * @return mixed
     */
    protected function delete(array $conditions = [], bool $checkToken = true)
    {
        $this->checkTable();

        if ($this->softDelete) {
            return $this->updateInto($this->table, ['deleted_at', new DateTime], $conditions, $checkToken);
        } else {
            return $this->deleteFrom($this->table, $conditions, $checkToken);
        }

    }

    /**
     * Deletes all the records, no soft resets because it's meant to clear all
     *
     * @return mixed
     */
    public function clear(bool $checkToken = true)
    {
        $this->checkTable();
        $this->deleteAll($this->table, $checkToken);
    }

    /**
     * Checks if $table has been set, if no, it will try to
     * If all else fails, it throws an exception and... idk gives up i guess kinda like life eh
     *
     * @return void
     */
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
