<?php

namespace Papi\Commands;

use mysqli;
use Papi\Auth\Auth;
use Papi\Database;
use Throwable;

class Setup extends Auth
{
    private $conn;

    public function __construct()
    {
        try {
            $this->createDatabase();

            $tables = $this->jsonToArray('config.json', 'tables');

            if (! is_null($this->conn)) {
                if ($this->io('Create the default users and tokens tables? (y/n)', true, 'y')) {
                    $this->createTable('users', $tables['users']['columns'], $tables['users']['pk'], true, true, false);
                    $this->createTable('tokens', $tables['tokens']['columns'], $tables['tokens']['pk'], true, true, false);

                    unset($tables['users']);
                    unset($tables['tokens']);

                    if ($this->io('Create a default user? (y/n)', true, 'y')) {
                        $name = $this->io('Name');
                        $username = $this->io('Username');
                        do {
                            $email = $this->io('Email');
                        } while (! $this->validateEmail($email));
                        $password = $this->io('Password');
                        $this->register($name, $username, $email, $password, true);
                        $uuid = $this->registerToken($email, $password);
                        echo 'User created with token '.$uuid;
                        echo "\nPlease keep this token carefully as this is how you interact with your API!";
                    }

                    if (count($tables) > 0) {
                        if ($this->io("\n\nCreate the remaining tables left in config.json? (y/n)", true, 'y')) {
                            $errors = 0;
                            foreach ($tables as $key => $table) {
                                try {
                                    $this->createTable($key, $table['columns'], (array_key_exists('pk', $table)) ? $table['pk'] : '', true, true, false);

                                } catch (Throwable $t) {
                                    $errors += 1;

                                    continue;
                                }
                            }
                            echo "\nFinished creations of remaining tables with ".$errors.' fails and '.(count($tables) - $errors).' succeeded';
                        }

                    }
                    echo "\nFinished initialisation!";

                }
            }
        } catch (Throwable $t) {
            echo $t;
            echo "\nUnable to finish Initialisation. Please check if the database in .env has not been created.";
        }
    }

    /**
     * Connects to the database
     *
     * @return null
     */
    private function createDatabase()
    {
        $env = parse_ini_file('.env');

        $conn = new mysqli($env['DB_HOST'], $env['DB_USERNAME'], $env['DB_PASSWORD']);
        $conn->query('CREATE DATABASE '.$env['DB_NAME']);
        $conn->close();

        $this->conn = new mysqli($env['DB_HOST'], $env['DB_USERNAME'], $env['DB_PASSWORD'], $env['DB_NAME']);
    }
}
