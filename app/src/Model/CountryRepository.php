<?php

namespace App\Model;

interface CountryRepository {
    public function getAll(): array;

    public function get(string $code): Country;

    public function store(Country $country): void;

    public function edit(string $code, Country $newData): void;

    public function delete(string $code): void;
    
}