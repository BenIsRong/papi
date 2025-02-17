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
        $auth = new Auth;
        $user = $auth->getUserFromToken($token);
        if (! is_null($user)) {
            $userRole = $user['role_id'];
            $res = $auth->viewOne('roles', [
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
        $auth = new Auth;
        $user = $auth->getUserFromToken($token);

        if (! is_null($user)) {
            $permissionIds = $auth->view('roles_with_permissions', [
                [
                    'col' => 'role_id',
                    'operator' => '=',
                    'value' => $user['role_id'],
                ],
            ]);
            $permissions = [];

            foreach ($permissionIds as $permissionId) {
                array_push($permissions, $auth->viewOne('permissions', [
                    [
                        'col' => 'id',
                        'operator' => '=',
                        'value' => $permissionId['permission_id'],
                    ],
                ], false));
            }

            $permissions = array_map(function ($permission) {
                return strtolower($permission['name']);
            }, $permissions);

            return in_array(strtolower($permission), $permissions);
        }

        return false;
    }
}
