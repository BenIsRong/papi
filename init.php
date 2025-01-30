<?php

class Setup
{
    private $conn;

    public function __construct()
    {
        $this->connectDatabase();

        if (! is_null($this->conn)) {
            echo 'Setup completed';
        }
    }

    private function connectDatabase()
    {
        $env = parse_ini_file('.env');

        $conn = new mysqli($env['DB_HOST'], $env['DB_USERNAME'], $env['DB_PASSWORD']);
        $conn->query('CREATE DATABASE IF NOT EXISTS '.$env['DB_NAME']);
        $conn->close();

        $this->conn = new mysqli($env['DB_HOST'], $env['DB_USERNAME'], $env['DB_PASSWORD'], $env['DB_NAME']);
    }
}

new Setup;
