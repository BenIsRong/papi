<?php

namespace Papi;

class Validation
{
    /**
     * Validation
     */
    public function validateEmail(string $email)
    {
        return ! filter_var($email, FILTER_VALIDATE_EMAIL) ? false : true;
    }
}
