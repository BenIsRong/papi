<?php

namespace Papi\Commands;

use Papi\Base;

class Policy extends Base
{
    public function __construct(string $name)
    {
        if (! is_dir('policies/')) {
            mkdir('policies');
        }

        if (! str_ends_with(strtolower($name), 'policy')) {
            $name = $name.'Policy';
        }

        if (file_exists("policies/$name.php")) {
            echo 'This policy already exists!';

            return null;
        }

        copy('papi/formats/policy', "policies/$name.php");

        $contents = file_get_contents("policies/$name.php");
        $contents = str_replace('TempPolicy', $name, $contents);
        file_put_contents("policies/$name.php", $contents);
    }
}
