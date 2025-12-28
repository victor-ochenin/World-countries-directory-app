<?php

namespace App\Model\Exceptions;

use Exception;
use App\Model\Country;

class CountryValidationException extends Exception
{
    private array $errors;

    public function __construct(array $errors, Country $country)
    {
        $this->errors = $errors;
        
        $errorMessages = array_map(
            fn($field, $message) => sprintf("%s: %s", $field, $message),
            array_keys($errors),
            array_values($errors)
        );
        
        $message = sprintf(
            "Ошибки валидации для страны %s:\n%s",
            $country->shortName,
            implode("\n", $errorMessages)
        );
        
        parent::__construct($message, 422);
    }
}