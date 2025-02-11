<?php

namespace Src\Commands;

use mysqli;
use Src\Database;
use Throwable;

class Setup extends Database
{
    private $conn;

    public function __construct()
    {
        try {
            $this->createDatabase();

            $tables = $this->jsonToArray('config.json', 'tables');

            if (! is_null($this->conn)) {
                if ($this->io('Create the default users and tokens tables? (y/n)', true, 'y')) {
                    $this->createTable('users', $tables['users']['columns'], $tables['users']['pk']);
                    $this->createTable('tokens', $tables['tokens']['columns'], $tables['tokens']['pk']);

                    unset($tables['users']);
                    unset($tables['tokens']);

                    if ($this->io('Create a default user? (y/n)', true, 'y')) {
                        $name = $this->io('Name');
                        $username = $this->io('Username');
                        $email = $this->io('Email');
                        $password = $this->io('Password');
                        $this->insertInto('users', [
                            'name' => $name,
                            'username' => $username,
                            'email' => $email,
                            'password' => $password,
                            'admin' => 1,
                        ]);

                        $result = $this->conn->query("SELECT id from users WHERE email='$email' AND name='$name'")->fetch_assoc();
                        $id = $result['id'];
                        $uuid = $this->uuid();
                        $expiry = date('Y-m-d', strtotime('+1 month', strtotime(date('Y-m-d'))));
                        $this->insertInto('tokens', [
                            'user_id' => $id,
                            'token' => $uuid,
                            'expiration' => $expiry,
                        ]);
                        echo ' User created with token '.$uuid;
                        echo "\nPlease keep this token carefully as this is how you interact with your API!";
                    }

                    if (count($tables) > 0) {
                        $errors = 0;
                        if ($this->io('Create the remaining tables left in config.json? (y/n)', true, 'y')) {
                            foreach ($tables as $key => $table) {
                                try {
                                    $this->createTable($key, $table['columns'], (array_key_exists('pk', $table)) ? $table['pk'] : '');

                                } catch (Throwable $t) {
                                    $errors += 1;

                                    continue;
                                }
                            }
                        }

                        echo "\nFinished creations of remaining tables with ".$errors.' fails and '.(count($tables) - $errors).' succeeded';
                    }
                    echo "\nFinished initialisation!";

                }
            }
        } catch (Throwable $t) {
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
