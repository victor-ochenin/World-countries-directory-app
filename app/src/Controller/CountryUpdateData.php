<?php

namespace App\Controller;

class CountryUpdateData
{
    public string $shortName;
    public string $fullName;
    public int $population;

    public function __construct(array $data)
    {
        $fields = ['shortName', 'fullName', 'population'];
        foreach ($fields as $field) {
            if (!array_key_exists($field, $data)) {
                throw new \InvalidArgumentException("Field '".$field."' is required");
            }
            $this->{$field} = $data[$field];
        }
    }
}
