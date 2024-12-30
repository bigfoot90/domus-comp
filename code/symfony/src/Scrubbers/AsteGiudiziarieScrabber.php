<?php

namespace App\Scrubbers;

use App\Model\House;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Image;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Service\Attribute\Required;

class AsteGiudiziarieScrabber extends AbstractScrabber implements ScrabberInterface
{
    #[Required]
    public HttpClientInterface $http;

    public function supportsUrl(string $url): bool
    {
        return parse_url($url, PHP_URL_HOST) === 'www.astegiudiziarie.it';
    }

    public function scrab(string $url): House
    {
        $domain = parse_url($url, PHP_URL_HOST);
        $response = $this->http->request('GET', $url);
        $html = $response->getContent();

        $crawler = new Crawler($html, $url);
        $bidTime = new \DateTimeImmutable($crawler->filter('[data-pvp-dativendita-dataoravendita]')->attr('data-pvp-dativendita-dataoravendita'));
        $description = str_replace('<br>', "\n", trim($crawler->filter('[data-pvp-bene-descrizioneit]')->html()));

        if (false !== stripos($description, 'riscaldamento centralizzato')) {
            $riscaldamento = 'centralizzato';
        } elseif (false !== stripos($description, 'riscaldamento autonomo')) {
            $riscaldamento = 'autonomo';
        }

        $features = self::extractFeatures($crawler);

        $virtualTour = $crawler->filter('iframe[allow*="gyroscope"]');

        return new House(
            url: $url,
            company: 'aste-giudiziarie',
            code: $this->extractCode($crawler),
            name: trim($crawler->filter('h1')->text()),
            contract: 'asta '.$bidTime->format('d-m-Y'),
            typology: self::parseTipologia($description),
            price: $this->parseInt($crawler->filter('[data-pvp-dativendita-offertaminima]')->text()) / 100,
            description: $description,
            floor: (int) $features['piano'],
            surface: sscanf($features['metri quadri'], '%i,00')[0],
            doppiServizi: $features['bagni'] > 1,
            rooms: sscanf($features['vani'], '%i,00')[0],
            zone: $crawler->filter('[data-pvp-lotto-citta]')->text(),
            address: $features['indirizzo'],
            energyCertification: $features['certificazione energetica'],
            balcony: false !== stripos($description, 'balcone'),
            garden: false !== stripos($description, 'giardino'),
            terrace: false !== stripos($description, 'terraz'),
            lift: false !== stripos($description, 'ascensore'),
            riscaldamento: $riscaldamento ?? null,
            ariaCondizionata: false !== stripos($description, 'condizionata'),
            cucinaAbitabile: false !== stripos($description, 'cucina abitabile'),
            arredato: false !== stripos($description, 'arreda'),
            boxAuto: (bool) preg_match('/garage|box auto|posto auto|carrabile/i', $description),
            basement: false !== stripos($description, 'cantina'),
            videocitofono: false !== stripos($description, 'videocitofono'),
            coordinates: $this->parseCoordinates($crawler->selectLink('Ingrandisci')->attr('href')),
            gallery: $this->getImagesLink($crawler->filter('a[data-fslightbox="galleryFoto"] img')),
            floorPlans: $this->getImagesLink($crawler->filter('a[data-fslightbox="galleryPlani"] img')),
            virtualTour: $virtualTour->count() ? 'http://'.$domain.$virtualTour->attr('src') : null,
        );
    }

    private function parseCoordinates(string $url): ?array
    {
        if(preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return [
                'latitude' => (float) $matches[1],  // 41.5852897000
                'longitude' => (float) $matches[2], // 12.5017739000
            ];
        }

        return null;
    }

    public function extractCode(Crawler $crawler): string
    {
        return sprintf('%s/%s',
            $this->parseInt($crawler->filter('[data-pvp-numeroprocedura]')->text()),
            $this->parseInt($crawler->filter('[data-pvp-annoprocedura]')->text()),
        );
        return $this->parseInt($crawler->filter('.dettagliLotto__item')->eq(1)->text());
    }

    private static function extractFeatures(Crawler $crawler): array
    {
        $features = [];
        $crawler->filter('.dettagliLotto__item')->each(function(Crawler $item) use (&$features) {
            $label = strtolower($item->filter('strong')->text());
            $value = substr($item->text(), strlen($label) +1);
            $features[$label] = $value;
        });

        return $features;
    }
}
