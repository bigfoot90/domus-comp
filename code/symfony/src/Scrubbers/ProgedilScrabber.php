<?php

namespace App\Scrubbers;

use App\Model\House;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Image;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Service\Attribute\Required;

class ProgedilScrabber extends AbstractScrabber implements ScrabberInterface
{
    #[Required]
    public HttpClientInterface $http;

    public function supportsUrl(string $url): bool
    {
        return parse_url($url, PHP_URL_HOST) === 'www.progedil.it';
    }

    public function scrab(string $url): House
    {
        $response = $this->http->request('GET', $url);
        $html = $response->getContent();

        $crawler = new Crawler($html, $url);
        $description = str_replace('<br>', "\n", trim($crawler->filter('.descrizione-immobile')->html()));

        if (false !== stripos($description, 'riscaldamento centralizzato')) {
            $riscaldamento = 'centralizzato';
        } elseif (false !== stripos($description, 'riscaldamento autonomo')) {
            $riscaldamento = 'autonomo';
        }

        return new House(
            url: $url,
            company: 'progedil',
            code: $crawler->filter('.codice-annuncio strong')->text(),
            name: trim($crawler->filter('.info-immobile h1')->text()),
            typology: strtolower($crawler->filter('#menu-locale span')->eq(0)->text()),
            contract: strtolower($crawler->filter('#menu-locale span')->eq(1)->text()),
            surface: sscanf($crawler->filter('#menu-locale span')->eq(3)->text(), '%i MQ')[0],
            rooms: sscanf($crawler->filter('#menu-locale span')->eq(2)->text(), '%i Locali')[0],
            price: self::parseInt($crawler->filter('.prezzo-immobile strong')->text()),
            zone: trim($crawler->filter('.zona-immobile span')->text()),
            address: trim($crawler->filter('.indirizzo')->text()),
            description: $description,
            dirittoSuperficie: false !== stripos($description, 'diritto di superficie'),
            doppiServizi: false !== stripos($description, 'doppi servizi'),
            doppiaEsposizione: false !== stripos($description, 'doppia esposizione'),
            balcony: false !== stripos($description, 'balcone'),
            garden: false !== stripos($description, 'giardino'),
            terrace: false !== stripos($description, 'terraz'),
            lift: false !== stripos($description, 'ascensore'),
            energyCertification: $this->extractEnergyCertification($description),
            riscaldamento: $riscaldamento ?? null,
            ariaCondizionata: false !== stripos($description, 'condizionata'),
            cucinaAbitabile: false !== stripos($description, 'cucina abitabile'),
            arredato: false !== stripos($description, 'arreda'),
            boxAuto: (bool) preg_match('/garage|box auto|posto auto|carrabile/i', $description),
            basement: false !== stripos($description, 'cantina'),
            videocitofono: false !== stripos($description, 'videocitofono'),
            coordinates: $this->extractCoordinates($html),
            gallery: $this->getImagesLink($crawler->filter('#modal-gallery img')),
            floorPlans: $this->getImagesLink($crawler->filter('#modal-planimetrie img')),
        );
    }

    private function extractEnergyCertification(string $text): ?string
    {
        if (preg_match('/Classe [Ee]nergetica:?\s*([A-G])/i', $text, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function extractCoordinates(string $html): array
    {
        $coordsPattern = '/\.fromLonLat\(\[\s*([\d.]+)\s*,\s*([\d.]+)\s*]\),/';
        preg_match($coordsPattern, $html, $coordinates);

        return [
            'longitude' => (float) $coordinates[1],
            'latitude' => (float) $coordinates[2],
        ];
    }
}
