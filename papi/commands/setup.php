<?php

namespace Papi\Commands;

use mysqli;
use Papi\Auth\Auth;
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
                    $this->createTable('permissions', $tables['permissions']['columns'], $tables['permissions']['pk'], true, false, false);
                    $this->createTable('roles', $tables['roles']['columns'], $tables['roles']['pk'], true, false, false);
                    $this->createTable('roles_with_permissions', $tables['roles_with_permissions']['columns'], '', true, false, false);

                    unset($tables['users']);
                    unset($tables['tokens']);
                    unset($tables['permissions']);
                    unset($tables['roles']);
                    unset($tables['roles_with_permissions']);

                    $adminRoleId = $this->initPermsAndRoles();

                    if ($this->io('Create a default user? (y/n)', true, 'y')) {
                        $name = $this->io('Name');
                        $username = $this->io('Username');
                        do {
                            $email = $this->io('Email');
                        } while (! $this->validateEmail($email));
                        $password = $this->io('Password');
                        $this->register($name, $username, $email, $password, $adminRoleId);
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

    /**
     * Initialises the following tables:
     * - permissions
     * - roles
     * - roles_with_permissions
     * As well as returning the id of the "super admin" role to assign it
     *
     * @return int
     */
    private function initPermsAndRoles()
    {
        $adminRoleId = 0;

        $roles = $this->jsonToArray('config.json', 'roles');
        $perms = $this->jsonToArray('config.json', 'permissions');

        foreach ($perms as $perm) {
            $this->insertInto('permissions', [
                'name' => $perm['name'],
                'login' => $perm['login'],
            ], false);
        }

        foreach ($roles as $role) {
            $this->insertInto('roles', [
                'name' => $role['name'],
            ], false);

            $permsToRole = [];
            if ($role['permissions']['include'][0] == '*') {
                $permsToRole = $perms;
            } else {
                foreach ($perms as $perm) {
                    if (in_array($perm['name'], $role['permissions']['include'])) {
                        array_push($permsToRole, $perm);
                    }
                }
            }

            if (array_key_exists('exclude', $role['permissions'])) {
                if ($roles['permissions']['exclude'][0] == '*') {
                    $permsToRole = [];

                    continue;
                } else {
                    foreach ($permsToRole as $idx => $perm) {
                        if (in_array($perm['name'], $role['permissions']['include'])) {
                            unset($permsToRole[$idx]);
                        }
                    }
                }
            }

            foreach ($permsToRole as $perm) {
                $permResult = $this->viewOne('permissions', [
                    [
                        'col' => 'name',
                        'operator' => '=',
                        'value' => $perm['name'],
                    ],
                ], false);
                $roleResult = $this->viewOne('roles', [
                    [
                        'col' => 'name',
                        'operator' => '=',
                        'value' => $role['name'],
                    ],
                ], false);

                if ($role['name'] == 'super admin') {
                    $adminRoleId = $roleResult['id'];
                }

                $this->insertInto('roles_with_permissions', [
                    'role_id' => $roleResult['id'],
                    'permission_id' => $permResult['id'],
                ], false);

            }
        }

        return $adminRoleId;
    }
}
