<?php

namespace Papi\Auth;

class Perms extends Auth
{
    /**
     * Check if user has given role
     *
     * @return bool
     */
    public static function haveRole(string $token, string $role)
    {
        $user = parent::getUserFromToken($token);
        if (! is_null($user)) {
            $userRole = $user['role_id'];
            $res = parent::viewOne('roles', [
                [
                    'col' => 'id',
                    'operator' => '=',
                    'value' => $role,
                ],
            ], false);

            if (! is_null($res)) {
                if ($userRole == $res['id']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if user has given permission
     *
     * @return bool
     */
    public static function havePermission(string $token, string $permission)
    {
        $user = parent::getUserFromToken($token);

        if (! is_null($user)) {
            $permissions = parent::view('permissions', [
                [
                    'col' => 'id',
                    'operator' => '=',
                    'value' => $user['role_id'],
                ],
            ], false);

            $permissions = array_map(function ($permission) {
                return strtolower($permission['name']);
            }, $permissions);

            return in_array(strtolower($permission), $permissions);
        }

        return false;
    }
}
