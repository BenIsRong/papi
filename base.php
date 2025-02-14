<?php

namespace Papi;

use Throwable;

spl_autoload_register(function ($class) {
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    require_once __DIR__."\\$path.php";
});

// DEFINITIONS
// request definitions
define('REQUEST_ALL', 0);
define('REQUEST_FORM_ONLY', 1);
define('REQUEST_PARAMS_ONLY', 2);

class Base extends Validation
{
    /**
     * Gets user input and returns either a boolean for the result or a string for the answer
     *
     * @return mixed
     */
    public function io(string $prompt, bool $yn = false, string $true = '')
    {
        $handler = fopen('php://stdin', 'r');
        $answer = null;

        while ($answer == '' || is_null($answer)) {
            echo $prompt.(! $yn ? ': ' : "\n");
            $answer = trim(fgets($handler));
        }

        if ($yn) {
            if (str_contains(strtolower($answer), $true)) {
                return true;
            }

            return false;
        }

        return $answer;

    }

    /**
     * Generate an HTTP response
     *
     * @return void
     */
    public function response($responseCode, array $res = [])
    {
        if (! is_int($responseCode)) {
            $responseCode = $responseCode->get();
        }
        header('Content-Type: application/json', true, $responseCode);
        echo json_encode($res);
    }

    /**
     * Generate uuid v4
     *
     * @return string
     */
    public function uuid()
    {
        $data = random_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0F | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3F | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Get json file as an associative array
     *
     * @return array|null
     */
    public function jsonToArray(string $filepath, ?string $key = null)
    {
        try {
            $jsonFile = json_decode(file_get_contents($filepath), true);

            if (! is_null($key)) {
                if (array_key_exists($key, $jsonFile)) {
                    return $jsonFile[$key];
                } else {
                    return $jsonFile;
                }
            }

            return $jsonFile;
        } catch (Throwable $t) {
            return null;
        }
    }

    public function pluralise(string $name, bool $lower = true)
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
