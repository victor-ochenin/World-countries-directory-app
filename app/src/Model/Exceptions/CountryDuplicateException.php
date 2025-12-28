<?php

namespace App\Model\Exceptions;

use Exception;
use App\Model\Country;

class CountryDuplicateException extends Exception
{
    private string $duplicateField;
    private string $duplicateValue;
    private ?Country $existingCountry;

    public function __construct(
        string $duplicateField,
        string $duplicateValue,
        ?Country $existingCountry = null
    ) {
        $this->duplicateField = $duplicateField;
        $this->duplicateValue = $duplicateValue;
        $this->existingCountry = $existingCountry;

        $fieldDescription = $this->getFieldDescription();
        $message = sprintf(
            'Страна с %s "%s" уже существует',
            $fieldDescription,
            $duplicateValue
        );

        parent::__construct($message, 409);
    }

    private function getFieldDescription(): string
    {
        return match($this->duplicateField) {
            'isoAlpha2' => 'двухбуквенным кодом',
            'isoAlpha3' => 'трехбуквенным кодом',
            'isoNumeric' => 'числовым кодом',
            'shortName' => 'коротким названием',
            'fullName' => 'полным названием',
            default => 'таким значением поля'
        };
    }
}