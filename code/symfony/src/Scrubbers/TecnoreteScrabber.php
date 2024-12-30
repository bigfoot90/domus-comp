<?php

namespace App\Scrubbers;

use App\Model\House;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Image;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Service\Attribute\Required;

class TecnoreteScrabber extends AbstractScrabber implements ScrabberInterface
{
    #[Required]
    public HttpClientInterface $http;

    public function supportsUrl(string $url): bool
    {
        return (bool) preg_match('/^https?:\/\/[\w-]+\.tecnorete\.it\//i', $url);
    }

    public function scrab(string $url): House
    {
        $response = $this->http->request('GET', $url);
        $html = $response->getContent();

        $crawler = new Crawler($html, $url);
        $description = str_replace('<br>', "\n", trim($crawler->filter('.description')->html()));

        $riscaldamento = $crawler->filter('.estate-dates .row')->eq(1)->text();
        if (false !== stripos($riscaldamento, 'riscaldamento: centralizzato')) {
            $riscaldamento = 'centralizzato';
        } elseif (false !== stripos($riscaldamento, 'riscaldamento: autonomo')) {
            $riscaldamento = 'autonomo';
        }

        return new House(
            url: $url,
            company: 'tecnorete',
            contract: 'vendita',
            typology: self::parseTipologia($description),
            code: $this->parseInt($crawler->filter('.schedaAnnuncioRiferimento')->text()),
            name: trim($crawler->filter('.schedaAnnuncioTitolo h1')->text()),
            surface: sscanf($crawler->filter('.estate-card-surface')->text(), '%i Mq')[0],
            rooms: sscanf($crawler->filter('.estate-card-rooms')->text(), '%i Locali')[0],
            price: $this->parseInt($crawler->filter('.estate-card-current-price')->text()),
            zone: trim($crawler->filter('.schedaAnnuncioSottoTitolo')->text()),
            address: trim($crawler->filter('.schedaAnnuncioSottoTitolo')->text()),
            description: $description,
            dirittoSuperficie: false !== stripos($description, 'diritto di superficie'),
            doppiServizi: $this->parseInt($crawler->filter('.estate-card-bathrooms')->text()) > 1,
            doppiaEsposizione: false !== stripos($description, 'doppia esposizione'),
            balcony: false !== stripos($description, 'balcone'),
            garden: false !== stripos($description, 'giardino'),
            terrace: false !== stripos($description, 'terraz'),
            lift: false !== stripos($description, 'ascensore'),
            energyCertification: $crawler->filter('.energy-graph span')->eq(0)->text(),
            riscaldamento: $riscaldamento ?? null,
            ariaCondizionata: false !== stripos($description, 'condizionata'),
            cucinaAbitabile: false !== stripos($description, 'cucina abitabile'),
            arredato: false !== stripos($description, 'arreda'),
            boxAuto: (bool) preg_match('/garage|box auto|posto auto|carrabile/i', $description),
            basement: false !== stripos($description, 'cantina'),
            videocitofono: false !== stripos($description, 'videocitofono'),
            coordinates: $this->extractCoordinates($crawler),
            gallery: $this->getImagesLink($crawler->filter('.js-thumbs-img')),
            floorPlans: $this->getImagesLink($crawler->filter('.js-thumbs-planimetria')),
            virtualTour: $crawler->filter('iframe.virtual-tour')->attr('data-src'),
            buildYear: $this->parseInt($crawler->filter('.estate-dates .row')->eq(0)->text()),
        );
    }

    private function extractCoordinates(Crawler $crawler): array
    {
        $mapElement = $crawler->filter('detail-map');

        return [
            'longitude' => (float) $mapElement->attr(':longitude'),
            'latitude' => (float) $mapElement->attr(':latitude'),
        ];
    }
}
