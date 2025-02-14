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

    /**
     * Validation for password
     *
     * @return bool
     */
    protected function validatePassword(string $password)
    {
        return preg_match('/(?=.*[A-Z])(?=.*\d)(?=.*[^\w])[a-zA-Z\d\W]{12,}/s', $password);
    }
}
