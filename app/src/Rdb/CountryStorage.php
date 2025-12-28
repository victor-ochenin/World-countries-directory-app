<?php

namespace App\Rdb;

use mysqli;

use App\Model\Exceptions\CountryNotFoundException;
use App\Model\Exceptions\CountryDuplicateException;

use App\Model\Country;
use App\Model\CountryRepository;
use App\Rdb\SqlHelper;

class CountryStorage implements CountryRepository{

    private SqlHelper $sqlHelper;

    public function __construct(SqlHelper $sqlHelper)
    {
        $this->sqlHelper = $sqlHelper;
    }

    public function getAll(): array
    {
        $connection = $this->sqlHelper->openDbConnection();

    try {
        $sql = "SELECT 
                    id,
                    short_name_f as shortName,
                    full_name_f as fullName,
                    iso_alpha2_f as isoAlpha2,
                    iso_alpha3_f as isoAlpha3,
                    iso_numeric_f as isoNumeric,
                    population_f as population
                FROM country_t 
                ORDER BY short_name_f";
        
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $countries = [];
        while ($row = $result->fetch_assoc()) {
            $country = new Country(
                shortName: $row['shortName'],
                fullName: $row['fullName'],
                isoAlpha2: $row['isoAlpha2'],
                isoAlpha3: $row['isoAlpha3'],
                isoNumeric: $row['isoNumeric'],
                population: $row['population']
            );
            $countries[] = $country;
        }
        
        return $countries;
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        $connection->close(); 
    }
    }

    public function get(string $code): Country{
        $codeType = $this->determineCodeType($code);

        $connection = $this->sqlHelper->openDbConnection();

    try {
        $sql = "SELECT 
                    id,
                    short_name_f as shortName,
                    full_name_f as fullName,
                    iso_alpha2_f as isoAlpha2,
                    iso_alpha3_f as isoAlpha3,
                    iso_numeric_f as isoNumeric,
                    population_f as population
                FROM country_t
                WHERE " . $this->getWhereClauseByCodeType($codeType);
                
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return new Country(
                shortName: $row['shortName'],
                fullName: $row['fullName'],
                isoAlpha2: $row['isoAlpha2'],
                isoAlpha3: $row['isoAlpha3'],
                isoNumeric: $row['isoNumeric'],
                population: $row['population']
            );
        }
        
        throw new CountryNotFoundException($code, "Country with code '$code' not found");
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        $connection->close(); 
    }
    }

    public function store(Country $country): void
    {
        $connection = $this->sqlHelper->openDbConnection();

    try {
        $this->validateNoDuplicate($country, $connection);

        $sql = "INSERT INTO country_t (
                    short_name_f,
                    full_name_f,
                    iso_alpha2_f,
                    iso_alpha3_f,
                    iso_numeric_f,
                    population_f
                ) VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param(
            "ssssii", 
            $country->shortName,
            $country->fullName,
            $country->isoAlpha2,
            $country->isoAlpha3,
            $country->isoNumeric,
            $country->population
        );
        $stmt->execute();
    }    
    finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        $connection->close();
    }
    }

    public function edit(string $code, Country $newData): void
    {
        $connection = $this->sqlHelper->openDbConnection();

        $codeType = $this->determineCodeType($code);
    try {
        $sql = "UPDATE country_t SET 
                short_name_f = ?, 
                full_name_f = ?, 
                iso_alpha2_f = ?, 
                iso_alpha3_f = ?, 
                iso_numeric_f = ?, 
                population_f = ?
            WHERE " . $this->getWhereClauseByCodeType($codeType);

        $stmt = $connection->prepare($sql);

        $stmt->bind_param(
        "ssssiis",
        $newData->shortName,
        $newData->fullName,
        $newData->isoAlpha2,
        $newData->isoAlpha3,
        $newData->isoNumeric,
        $newData->population,
        $code
        );
        $stmt->execute();
    }
    finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        $connection->close();
    }
    }

    public function delete(string $code): void
{
    $connection = $this->sqlHelper->openDbConnection();
    
    try {
        $sql = "DELETE FROM country_t WHERE iso_alpha2_f = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("s", $code);
        $stmt->execute();
        
    } finally {
        if ($stmt) {
            $stmt->close();
        }
        $connection->close();
    }
}

   private function determineCodeType(string $code): string
    {
        // Проверяем двухбуквенный код (2 заглавные буквы)
        if (preg_match('/^[A-Z]{2}$/', $code)) {
            return 'alpha2';
        }
        
        // Проверяем трехбуквенный код (3 заглавные буквы)
        if (preg_match('/^[A-Z]{3}$/', $code)) {
            return 'alpha3';
        }
        
        // Проверяем числовой код (3 цифры)
        if (preg_match('/^\d{3}$/', $code)) {
            return 'numeric';
        }
        
        // Если код не соответствует ни одному формату, проверяем возможные варианты
        // Может быть передан код в нижнем регистре
        $upperCode = strtoupper($code);
        
        if (preg_match('/^[A-Z]{2}$/', $upperCode)) {
            return 'alpha2';
        }
        
        if (preg_match('/^[A-Z]{3}$/', $upperCode)) {
            return 'alpha3';
        }
        
        // Если все проверки не прошли - невалидный код
        return 'invalid';
    }

     // getWhereClauseByCodeType - Возвращает WHERE условие для SQL запроса в зависимости от типа кода

    //  вход: Тип кода
    //  выход: строка WHERE условие
    //  исключения: \InvalidArgumentException Если передан невалидный тип кода
    private function getWhereClauseByCodeType(string $codeType): string
    {
        return match($codeType) {
            'alpha2' => 'iso_alpha2_f = ?',
            'alpha3' => 'iso_alpha3_f = ?',
            'numeric' => 'iso_numeric_f = ?',
            default => throw new \InvalidArgumentException("Invalid country code format")
        };
    }

    // validateNoDuplicate - Проверяет отсутствие дубликатов страны в базе данных по уникальным полям
    // вход: Объект страны, Подключение к базе данных
    // выход: -
    // исключения: CountryDuplicateException Если дубликат найден
    private function validateNoDuplicate(Country $country, $connection): void
    {
    // Проверяем дубликаты по различным полям
    $sql = "SELECT COUNT(*) FROM country_t 
            WHERE iso_alpha2_f = ? OR iso_alpha3_f = ? OR iso_numeric_f = ?";
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ssi", 
        $country->isoAlpha2,
        $country->isoAlpha3,
        $country->isoNumeric
    );
    
    $stmt->execute();
    $stmt->bind_result($country);
    $stmt->fetch();
    $stmt->close();
    
    if ($country > 0) {
        throw new CountryDuplicateException(
            $country->isoAlpha2,
            "Country with similar data already exists (duplicate ISO code or numeric)"
        );
    }
    }   
}