<?php

namespace Src\Commands;

use Src\Database;

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
                $contents = str_replace('TempDatabaseName', $this->pluralise($name), $contents);
            }

            file_put_contents("models/$name.php", $contents);
        }
    }

    private function pluralise(string $name, bool $lower = true)
    {
        switch (true) {
            case str_ends_with($name, 'y'):
                if (in_array($name[strlen($name) - 2], ['a', 'e', 'i', 'o', 'u'])) {
                    $name = $name.'s';
                } else {
                    $name = substr($name, 0, strlen($name) - 1).'ies';
                }
                break;
            case str_ends_with($name, 'o'):
                if (in_array($name[strlen($name) - 2], ['a', 'e', 'i', 'o', 'u'])) {
                    $name = $name.'s';
                } else {
                    $name = $name.'es';
                }
                break;
            case str_ends_with($name, 'f'):
                $name = substr($name, 0, strlen($name) - 1).'ves';
                break;
            case str_ends_with($name, 'fe'):
                $name = substr($name, 0, strlen($name) - 2).'ves';
                break;
            case str_ends_with($name, 's'):
            case str_ends_with($name, 'x'):
            case str_ends_with($name, 'z'):
            case str_ends_with($name, 'ch'):
            case str_ends_with($name, 'sh'):
            case str_ends_with($name, 'ss'):
                $name = $name.'es';
                break;
            default:
                $name = $name.'s';
        }

        return $lower ? strtolower($name) : $name;
    }
}
