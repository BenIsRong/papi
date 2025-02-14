<?php

namespace Papi;

class Validation
{
    /**
     * Validation for email
     *
     * @return bool
     */
    protected function validateEmail(string $email)
    {
        return ! filter_var($email, FILTER_VALIDATE_EMAIL) ? false : true;
    }
}
