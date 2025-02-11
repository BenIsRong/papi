<?php

namespace Src\Commands;

class Model
{
    public function __construct(string $name)
    {
        if (! is_dir('models/')) {
            mkdir('models');
        }

        if (! file_exists("models/$name.php")) {
            copy('formats/model', "models/$name.php");

            $contents = file_get_contents("models/$name.php");
            $contents = str_replace('Model', ucfirst(strtolower($name)), $contents);
            file_put_contents("models/$name.php", $contents);
        }
    }
}
