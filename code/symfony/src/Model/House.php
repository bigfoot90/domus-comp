<?php

declare(strict_types=1);

namespace App\Model;

final readonly class House implements \JsonSerializable
{
    public function __construct(
        public string $url,
        public string $company,
        public string $code,
        public string $contract,
        public string $typology,
        public int $rooms,
        public int $price,
        public string $zone,
        public string $address,
        public string $name,
        public string $description,
        public ?int $surface = null,
        public bool $dirittoSuperficie = false,
        public bool $doppiServizi = false,
        public bool $doppiaEsposizione = false,
        public bool $balcony = false,
        public bool $garden = false,
        public bool $terrace = false,
        public bool $lift = false,
        public ?string $energyCertification = null,
        public ?string $riscaldamento = null,
        public bool $ariaCondizionata = false,
        public bool $cucinaAbitabile = false,
        public bool $arredato = false,
        public bool $boxAuto = false,
        public bool $basement = false,
        public bool $videocitofono = false,
        public ?array $coordinates = null,
        public array $gallery = [],
        public array $floorPlans = [],
        public ?string $virtualTour = null,
        public ?int $floor = null,
        public ?int $buildYear = null,
    ) {}

    public function getPricePerMq(): float
    {
        return round($this->price / $this->surface, 2);
    }

    public function jsonSerialize(): array
    {
        return [
            'company' => $this->company,
            'code' => $this->code,
            'contratto' => $this->contract,
            'tipologia' => $this->typology,
            'surface' => $this->surface,
            'buildYear' => $this->buildYear,
            'rooms' => $this->rooms,
            'price' => $this->price,
            'floor' => $this->floor,
            'zone' => $this->zone,
            'address' => $this->address,
            'name' => $this->name,
            'description' => $this->description,
            'dirittoSuperficie' => $this->dirittoSuperficie,
            'doppiServizi' => $this->doppiServizi,
            'doppiaEsposizione' => $this->doppiaEsposizione,
            'balcony' => $this->balcony,
            'garden' => $this->garden,
            'terrace' => $this->terrace,
            'lift' => $this->lift,
            'energyCertification' => $this->energyCertification,
            'riscaldamento' => $this->riscaldamento,
            'ariaCondizionata' => $this->ariaCondizionata,
            'cucinaAbitabile' => $this->cucinaAbitabile,
            'arredato' => $this->arredato,
            'box-auto' => $this->boxAuto,
            'basement' => $this->basement,
            'videocitofono' => $this->videocitofono,
            'coordinates' => $this->coordinates,
            'gallery' => $this->gallery,
            'floorPlans' => $this->floorPlans,
            'virtualTour' => $this->virtualTour,
            'url' => $this->url
        ];
    }
}
