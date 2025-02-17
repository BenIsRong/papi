<?php

namespace Papi\Commands;

use Papi\Database;

class Model extends Database
{
    /**
     * Construct a new model
     *
     * @return void
     */
    public function __construct(string $name, ?string $db = null)
    {
        if (! is_dir('models/')) {
            mkdir('models');
        }

        if (file_exists("models/$name.php")) {
            if ($this->io('This model already exists! Are you sure you want to continue? If you continue, everything will be reset! (y/n)', true, 'n')) {
                return null;
            }
        }

        copy('papi/formats/model', "models/$name.php");

        $contents = file_get_contents("models/$name.php");
        $contents = str_replace('TempModel', ucfirst(strtolower($name)), $contents);
        if (! is_null($db) && $this->tableExists($db, false)) {
            $contents = str_replace('TempDatabaseName', $db, $contents);
        } else {
            $contents = str_replace('TempDatabaseName', $this->pluralise($name), $contents);
        }

        file_put_contents("models/$name.php", $contents);
    }
}
