<?php

include_once './db.php';
use DB as DB;

class Setup extends DB
{
    private $conn;

    public function __construct()
    {

        try {
            $this->createDatabase();

            if (! is_null($this->conn)) {
                if ($this->io('Create the default users and tokens tables? (y/n)', true, 'y')) {
                    $this->createTable('users',
                        [
                            [
                                'name' => 'id',
                                'type' => 'INT',
                                'null' => false,
                            ],
                            [
                                'name' => 'name',
                                'type' => 'TEXT',
                                'null' => false,
                            ],
                            [
                                'name' => 'username',
                                'type' => 'TEXT',
                                'null' => false,
                            ],
                            [
                                'name' => 'email',
                                'type' => 'TEXT',
                                'null' => false,
                            ],
                            [
                                'name' => 'password',
                                'type' => 'TEXT',
                                'null' => false,
                            ],
                            [
                                'name' => 'admin',
                                'type' => 'TINYINT',
                                'null' => false,
                                'default' => '1',
                            ],
                        ], 'id');

                    $this->createTable('tokens', [
                        [
                            'name' => 'id',
                            'type' => 'INT',
                            'null' => false,
                        ],
                        [
                            'name' => 'user_id',
                            'type' => 'INT',
                            'null' => false,
                        ],
                        [
                            'name' => 'token',
                            'type' => 'TEXT',
                            'null' => false,
                        ],
                        [
                            'name' => 'expiration',
                            'type' => 'DATETIME',
                            'null' => false,
                        ],
                    ], 'id');

                    if ($this->io('Create a default user? (y/n)', true, 'y')) {
                        $name = $this->io('Name');
                        $username = $this->io('Username');
                        $email = $this->io('Email');
                        $password = $this->io('Password');
                        $this->conn->query("INSERT INTO users (
                        `name`,
                        `username`,
                        `email`,
                        `password`,
                        `admin`
                        ) VALUES (
                        '$name',
                        '$username',
                        '$email',
                        '$password',
                        1
                        )");

                        $result = $this->conn->query("SELECT id from users WHERE email='$email' AND name='$name'")->fetch_assoc();
                        $id = $result['id'];
                        $uuid = $this->uuid();
                        $expiry = date('Y-m-d', strtotime('+1 month', strtotime(date('Y-m-d'))));
                        $this->insertInto('tokens', [
                            'user_id' => $id,
                            'token' => $uuid,
                            'expiration' => $expiry,
                        ]);
                        echo 'Finished initialisation!';
                        echo ' User created with token '.$uuid;
                        echo "\nPlease keep this token carefully as this is how you interact with your API!";
                    }
                }
            }
        } catch (Throwable $t) {
            echo $t;
            echo 'Unable to finish Initialisation. Please check if the database in .env has not been created.';
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

new Setup;
