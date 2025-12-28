<?php

namespace App\Model\Exceptions;

use Exception;

class CountryNotFoundException extends Exception
{
    private string $codeValue;
    private string $codeType;

    public function __construct(string $codeValue, string $codeType = 'unknown')
    {
        $this->codeValue = $codeValue;
        $this->codeType = $codeType;
        parent::__construct(sprintf(
            'Страна с %s кодом "%s" не найдена',
            $this->getCodeTypeDescription(),
            $codeValue
        ), 404);
    }

    private function getCodeTypeDescription(): string
    {
        return match($this->codeType) {
            'alpha2' => 'двухбуквенным',
            'alpha3' => 'трехбуквенным',
            'numeric' => 'числовым',
            default => 'указанным'
        };
    }
}