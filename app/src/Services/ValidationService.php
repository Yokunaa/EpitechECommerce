<?php

namespace App\Services;

use Symfony\Component\Validator\ConstraintViolationList;

class ValidationService
{
    public static function getErrors(ConstraintViolationList $violations)
    {
        $errors = [];

        for ($i = 0; $i < $violations->count(); $i++) {
            $errors[$violations->get($i)->getPropertyPath()] = $violations->get($i)->getMessage();
        }

        return $errors;
    }
}