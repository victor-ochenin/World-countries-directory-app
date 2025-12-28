<?php
 
namespace App\Model;
 
class Country {
    public ?int $id = null;
    public string $shortName;
    public string $fullName;
    public string $isoAlpha2;
    public string $isoAlpha3;
    public string $isoNumeric;
    public int $population;

    public function __construct(
        string $shortName,
        string $fullName,
        string $isoAlpha2,
        string $isoAlpha3,
        string $isoNumeric,
        int $population,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->shortName = $shortName;
        $this->fullName = $fullName;
        $this->isoAlpha2 = $isoAlpha2;
        $this->isoAlpha3 = $isoAlpha3;
        $this->isoNumeric = $isoNumeric;
        $this->population = $population;
    }
}