<?php

namespace Papi\Commands;

class Controller
{
    public function __construct(string $name, string $model)
    {
        if (! is_dir('controllers/')) {
            mkdir('controllers');
        }

        if (is_null($model)) {
            $model = str_ends_with(strtolower($name), 'controller') ? substr($name, 0, -10) : $name;
        }

        if (! file_exists("controllers/$name.php")) {
            copy('papi/formats/controller', "controllers/$name.php");

            $contents = file_get_contents("controllers/$name.php");
            $contents = str_replace('TempController', ucfirst($name), $contents);
            $contents = str_replace('Database', $model, $contents);
            $contents = str_replace('use Papi\\', 'use Models\\', $contents);
            file_put_contents("controllers/$name.php", $contents);
        }
    }
}
