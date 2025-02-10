<?php

class Setup
{
    private $conn;

    public function __construct()
    {

        $this->connectDatabase();

        if (! is_null($this->conn)) {
            if ($this->io('Create a default user table? (y/n)', true, 'y')) {
                $this->createUserTable();
            }
        }
    }

    /**
     * Connects to the database
     *
     * @return null
     */
    private function connectDatabase()
    {
        $env = parse_ini_file('.env');

        $conn = new mysqli($env['DB_HOST'], $env['DB_USERNAME'], $env['DB_PASSWORD']);
        $conn->query('CREATE DATABASE IF NOT EXISTS '.$env['DB_NAME']);
        $conn->close();

        $this->conn = new mysqli($env['DB_HOST'], $env['DB_USERNAME'], $env['DB_PASSWORD'], $env['DB_NAME']);
    }

    private function createUserTable()
    {
        $this->conn->query('CREATE TABLE IF NOT EXISTS users(
        `id` INT NOT NULL AUTO_INCREMENT,
        `name` TEXT NOT NULL,
        `username` TEXT NOT NULL,
        `email` TEXT NOT NULL,
        `password` TEXT NOT NULL,
        `admin` TINYINT NOT NULL DEFAULT 1,
        PRIMARY KEY (`id`))');
    }

    /**
     * Gets user input and returns either a boolean for the result or a string for the answer
     *
     * @return mixed
     */
    private function io(string $prompt, bool $yn = false, string $true = '')
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
}

new Setup;
