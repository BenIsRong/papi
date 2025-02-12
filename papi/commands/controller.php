<?php

namespace Papi\Commands;

class Controller
{
    public function __construct(string $name)
    {
        if (! is_dir('controllers/')) {
            mkdir('controllers');
        }

        if (! file_exists("controllers/$name.php")) {
            copy('papi/formats/controller', "controllers/$name.php");

            $contents = file_get_contents("controllers/$name.php");
            $contents = str_replace('TempController', ucfirst($name), $contents);
            file_put_contents("controllers/$name.php", $contents);
        }
    }
}
