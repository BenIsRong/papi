<?php

namespace Papi;

use Exception;

trait CheckPolicy
{
    public function allowedTo(string $policy)
    {
        $function = debug_backtrace()[1]['function'];

        $headers = apache_request_headers();
        $token = explode(' ', $headers['Authorization']);
        $token = end($token);
        $policy = new $policy;
        if ($policy->{$function}($token)) {
            return true;
        } else {
            throw new Exception('An error has occurred.');
        }
    }
}
