<?php

namespace App\Scrubbers;

use App\Model\House;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Image;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Service\Attribute\Required;

class ImmobiliareScrabber extends AbstractScrabber implements ScrabberInterface
{
    #[Required]
    public HttpClientInterface $http;

    public function supportsUrl(string $url): bool
    {
        return parse_url($url, PHP_URL_HOST) === 'www.immobiliare.it';
    }

    public function scrab(string $url): House
    {
        $response = $this->http->request('GET', $url);
        $html = $response->getContent();

        $crawler = new Crawler($html, $url);
        $description = str_replace('<br>', "\n", trim($crawler->filter('.in-readAll div')->html()));
        $location = $crawler->filter('.re-title__location');

        $features = self::extractFeatures($crawler);
        var_dump($features);

        $script = $crawler->filter('script#__NEXT_DATA__');
        $data = json_decode($script->text(), true);
        $data = $data['props']['pageProps']['detailData']['realEstate'];
        $props = $data['properties'][0];
        $media = $props['multimedia'];
        var_dump($data);

        return new House(
            url: $url,
            company: 'immobiliare',
            code: $data['id'],
            contract: $data['contractValue'],
            typology: $data['typology']['name'],
            name: $crawler->filter('.re-title__title')->text(),
            description: $description,
            price: $data['price']['value'],
            address: $this->extractAddressFromLocation($location),
            zone: $location->first()->text(),
            floor: self::parseInt($features['Piano']),
            rooms: self::parseInt($features['Locali']),
            surface: self::parseInt($features['Superficie']),
            cucinaAbitabile: false !== stripos($features['Cucina'], 'abitabile'),
            arredato: self::parseYesNo($features['Arredato'] ?? false),
            lift: $props['elevator'],
            balcony: self::parseYesNo($features['Balcone'] ?? false),
            terrace: self::parseYesNo($features['Terrazzo'] ?? false),
            garden: self::parseYesNo($features['Giardino'] ?? false),
            basement: self::parseYesNo($features['Cantina'] ?? false),
            boxAuto: (bool) $features['Box, posti auto'] ?? false,
            riscaldamento: $features['Riscaldamento'],
            energyCertification: $props['energy']['class'],
            doppiServizi: $props['bathrooms'] > 1,
            buildYear: $props['buildingYear'],
            coordinates: [
                'longitude' => $props['location']['longitude'],
                'latitude' => $props['location']['latitude'],
            ],
            gallery: array_map(fn($item) => $item['urls']['large'], $media['photos']),
            floorPlans: array_map(fn($item) => $item['urls']['large'], $media['floorplans']),
            virtualTour: $media['virtualTours'][0],
        );
    }

    private static function extractReference(string $url): string
    {
        preg_match('/\/annunci\/(\d+)/', $url, $matches);

        if (empty($matches[1])) {
            throw new \RuntimeException(sprintf('Cannot detect reference from "%s"', $url));
        }

        return $matches[1];
    }

    private static function extractAddressFromLocation(Crawler $location): string
    {
        return implode(' • ', array_map(fn(\DOMElement $node): string => $node->textContent, $location->getIterator()->getArrayCopy()));
    }

    private static function extractFeatures(Crawler $crawler): array
    {
        $features = [];
        $crawler->filter('.re-featuresItem')->each(function(Crawler $item) use (&$features) {
            $features[$item->filter('.re-featuresItem__title')->text()] = $item->filter('.re-featuresItem__description')->text();
        });

        return $features;
    }
}
