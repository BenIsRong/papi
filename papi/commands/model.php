<?php

namespace Papi\Commands;

use Papi\Database;

class Model extends Database
{
    public function __construct(string $name, ?string $db = null)
    {
        if (! is_dir('models/')) {
            mkdir('models');
        }

        if (! file_exists("models/$name.php")) {
            copy('src/formats/model', "models/$name.php");

            $contents = file_get_contents("models/$name.php");
            $contents = str_replace('Model', ucfirst(strtolower($name)), $contents);
            if (! is_null($db) && $this->tableExists($db)) {
                $contents = str_replace('TempDatabaseName', $db, $contents);
            } else {
                $contents = str_replace("private \$db = 'TempDatabaseName';", '', $contents);
            }

            file_put_contents("models/$name.php", $contents);
        }
    }
}
