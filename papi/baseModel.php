<?php

namespace Papi\Models;

use Papi\Database;

class BaseModel extends Database
{
    private $table;

    private $pk;

    public function __construct(string $table, string $pk = 'id')
    {
        $this->table = $table;
        $this->pk = $pk;

    }

    /**
     * Check if record with primary key exists,
     * If exists, then update, else create
     *
     * @return mixed
     */
    public function insertOrUpdate(array $data, array $conditions = []) {}

    /**
     * Soft deletes the record, such that it still exists, but would not be brought up in queries
     *
     * @return mixed
     */
    public function softDelete(array $conditions = []) {}

    /**
     * Retrieve gets all rows according to conditions
     * Results excludes soft deletes as they are "deleted"
     *
     * @return mixed
     */
    public function retrieve(array $conditions = []) {}
}
