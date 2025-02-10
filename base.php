<?php
class Base
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
    

    public function uuid()
    {
        $data = random_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0F | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3F | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}