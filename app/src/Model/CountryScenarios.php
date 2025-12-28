<?php

namespace App\Model;

use App\Model\Exceptions\CountryNotFoundException;
use App\Model\Exceptions\CountryValidationException;
use App\Model\Exceptions\CountryDuplicateException;

use App\Model\CountryRepository;

class CountryScenarios {

    private CountryRepository $repository;

     public function __construct(CountryRepository $repository)
    {
        $this->repository = $repository;
    }

    // getAll - получение всех стран
    // вход: -
    // выход: массив объектов Country
    public function getAll(): array
    {
        return $this->repository->getAll();
    }

    
    // get - получение страны по коду
    // вход: string $code - код страны (2-буквенный, 3-буквенный или числовой)
    // выход: объект Country
    // исключения: 
    // - CountryNotFoundException - если страна с указанным кодом не найдена
    // - InvalidArgumentException - если передан некорректный код
    public function get(string $code): Country
    {
        $this->validateCodeFormat($code);
        try {
            return $this->repository->get($code);
        } catch (CountryNotFoundException $e) {
            throw $e; // Пробрасываем дальше
        }
        
    }

    
     // store - сохранение новой страны
     // вход: Country $country - объект страны для сохранения
     // выход: void
     // исключения:
     //  - CountryDuplicateException - при дублировании кодов или названий
     //  - CountryValidationException - при нарушении правил валидации
    public function store(Country $country): void
    {
        $this->validateCountryData($country);
        $this->checkUniquenessBeforeStore($country);
        $this->repository->store($country);
    }

    
     // edit - редактирование страны по коду
     // вход: 
     //   - string $code - код страны для поиска (любого типа)
     //   - Country $newData - объект с новыми данными страны
     // выход: void
     // исключения:
     //   - CountryNotFoundException - если страна с указанным кодом не найдена
     //   - CountryDuplicateException - если новые названия дублируют названия других стран
     //   - CountryValidationException - при нарушении правил валидации данных
     //   - InvalidArgumentException - при попытке изменить коды страны
    public function edit(string $code, Country $newData): void
    {
        $this->validateCodeFormat($code);
        $this->validateCountryData($newData);

        try {
            $this->repository->get($code);
        } catch (CountryNotFoundException $e) {
            throw $e;
        }

        $this->checkUniquenessBeforeEdit($code, $newData);
        $this->repository->edit($code, $newData);
    }

    
     // delete - удаление страны по коду
     // вход: string $code - код страны (любого типа)
     // выход: void
     // исключения:
     //   - CountryNotFoundException - если страна с указанным кодом не найдена
     //   - CountryDeleteException - если страну нельзя удалить (есть связанные данные)
    public function delete(string $code): void
    {
        $this->validateCodeFormat($code);

        try {
            $this->repository->get($code);
        } catch (CountryNotFoundException $e) {
            throw $e;
        }

        $this->repository->delete($code);
    }

    private function validateCodeFormat(string $code): void
    {
        if (empty($code)) {
            throw new \InvalidArgumentException("Country code cannot be empty");
        }

        if (preg_match('/^[A-Z]{2}$/', $code) || 
            preg_match('/^[A-Z]{3}$/', $code) ||
            preg_match('/^\d{3}$/', $code)) {
            return;
        }
        
         $upperCode = strtoupper($code);
        if (preg_match('/^[A-Z]{2}$/', $upperCode) || 
            preg_match('/^[A-Z]{3}$/', $upperCode)) {
            return;
        }
        
        throw new \InvalidArgumentException(
            "Invalid country code format."
        );
    }

    private function validateCountryData(Country $country): void
{
    $errors = [];
    
    // Проверка короткого названия
    if (empty($country->shortName)) {
        $errors['shortName'] = "Короткое название обязательно";
    } elseif (strlen($country->shortName) > 100) {
        $errors['shortName'] = "Короткое название не может превышать 100 символов";
    }
    
    // Проверка полного названия
    if (empty($country->fullName)) {
        $errors['fullName'] = "Полное название обязательно";
    } elseif (strlen($country->fullName) > 255) {
        $errors['fullName'] = "Полное название не может превышать 255 символов";
    }
    
    // Проверка ISO Alpha-2 кода
    if (!preg_match('/^[A-Z]{2}$/', $country->isoAlpha2)) {
        $errors['isoAlpha2'] = "ISO Alpha-2 код должен состоять из 2 заглавных букв";
    }
    
    // Проверка ISO Alpha-3 кода
    if (!preg_match('/^[A-Z]{3}$/', $country->isoAlpha3)) {
        $errors['isoAlpha3'] = "ISO Alpha-3 код должен состоять из 3 заглавных букв";
    }
    
    // Проверка числового кода
    if (!is_numeric($country->isoNumeric)) {
        $errors['isoNumeric'] = "Числовой код ISO должен быть числом";
    } elseif ($country->isoNumeric < 0 || $country->isoNumeric > 999) {
        $errors['isoNumeric'] = "Числовой код ISO должен быть от 0 до 999";
    }
    
    // Проверка населения
    if (!is_numeric($country->population)) {
        $errors['population'] = "Население должно быть числом";
    } elseif ($country->population < 0) {
        $errors['population'] = "Население не может быть отрицательным";
    }


    if (!empty($errors)) {
        throw new CountryValidationException($errors, $country);
    }
}

private function checkUniquenessBeforeStore(Country $country): void
{
    // Alpha-2
    try {
        $existing = $this->repository->get($country->isoAlpha2);
        throw new CountryDuplicateException('isoAlpha2', $country->isoAlpha2, $existing);
    } catch (CountryNotFoundException $e) {
        // Дубликата нет — это нормально
    }

    // Alpha-3
    if ($country->isoAlpha2 !== $country->isoAlpha3) {
        try {
            $existing = $this->repository->get($country->isoAlpha3);
            throw new CountryDuplicateException('isoAlpha3', $country->isoAlpha3, $existing);
        } catch (CountryNotFoundException $e) {
            // Нет дубликата — нормально
        }
    }

    // Numeric
    $numericCode = str_pad((string)$country->isoNumeric, 3, '0', STR_PAD_LEFT);
    try {
        $existing = $this->repository->get($numericCode);
        throw new CountryDuplicateException('isoNumeric', (string)$country->isoNumeric, $existing);
    } catch (CountryNotFoundException $e) {
        // Нет дубликата — нормально
    }

    // Проверка уникальности shortName
    $allCountries = $this->repository->getAll();
    foreach ($allCountries as $existingCountry) {
        if ($existingCountry->shortName === $country->shortName) {
            throw new CountryDuplicateException('shortName', $country->shortName, $existingCountry);
        }
    }
}

private function checkUniquenessBeforeEdit(string $oldCode, Country $newData): void
{
    // Получаем текущую страну
    $currentCountry = $this->repository->get($oldCode);
    
    // Проверяем, изменился ли alpha2 код и уникален ли он
    if ($currentCountry->isoAlpha2 !== $newData->isoAlpha2) {
        try {
            $existing = $this->repository->get($newData->isoAlpha2);
            throw new CountryDuplicateException('isoAlpha2', $newData->isoAlpha2, $existing);
        } catch (CountryNotFoundException $e) {
            // Нет дубликата — это нормально
        }
    }
    
    // Проверяем alpha3 код
    if ($currentCountry->isoAlpha3 !== $newData->isoAlpha3) {
        try {
            $existing = $this->repository->get($newData->isoAlpha3);
            throw new CountryDuplicateException('isoAlpha3', $newData->isoAlpha3, $existing);
        } catch (CountryNotFoundException $e) {
            // Нет дубликата — это нормально
        }
    }
    
    // Проверяем числовой код
    if ($currentCountry->isoNumeric !== $newData->isoNumeric) {
        try {    
            $numericCode = str_pad((string)$newData->isoNumeric, 3, '0', STR_PAD_LEFT);
            $existing = $this->repository->get($numericCode);
            throw new CountryDuplicateException('isoNumeric', (string)$newData->isoNumeric, $existing);
        } catch (CountryNotFoundException $e) {
            // Нет дубликата — это нормально
        }    
    }
    
    // Проверяем уникальность названий (если они изменились)
    if ($currentCountry->shortName !== $newData->shortName || 
        $currentCountry->fullName !== $newData->fullName) {
            
        $allCountries = $this->repository->getAll();
            
        foreach ($allCountries as $existingCountry) {
            // Пропускаем текущую редактируемую страну
            if ($existingCountry->isoAlpha2 === $currentCountry->isoAlpha2) {
                continue;
            }
                
            // Проверка короткого названия
            if ($currentCountry->shortName !== $newData->shortName && 
                $existingCountry->shortName === $newData->shortName) {
                throw new CountryDuplicateException('shortName', $newData->shortName, $existingCountry);
            }
                
            // Проверка полного названия
            if ($currentCountry->fullName !== $newData->fullName && 
                $existingCountry->fullName === $newData->fullName) {
                throw new CountryDuplicateException('fullName', $newData->fullName, $existingCountry);
            }
        }
    }
}

    
}
