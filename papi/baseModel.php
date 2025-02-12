<?php

namespace Papi\Models;

use DateTime;
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
    public function insertOrUpdate(array $data, array $conditions = [], bool $checkToken = true)
    {
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
    public function softDelete(array $conditions = [], bool $checkToken = true)
    {
        return $this->updateInto($this->table, ['deleted_at', new DateTime], $conditions, $checkToken);
    }

    /**
     * Retrieve gets all rows according to conditions
     * Results excludes soft deletes as they are "deleted"
     *
     * @return mixed
     */
    public function retrieve(array $conditions = [], bool $checkToken = true)
    {
        if (! array_key_exists('deleted_at', $conditions)) {
            array_push($conditions, [
                'col' => 'deleted_at',
                'operator' => '=',
                'value' => 'NULL',
            ]);
        }

        return $this->view($this->table, $conditions, $checkToken);
    }
}
