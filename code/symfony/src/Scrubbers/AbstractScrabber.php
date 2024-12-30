<?php

namespace App\Scrubbers;

use App\Model\House;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Image;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractScrabber implements ScrabberInterface
{
    protected static function parseInt(string $input): int
    {
        return (int) preg_replace('/\D/', '', $input);
    }

    protected static function parseYesNo(string $input): bool
    {
        switch (strtolower(trim($input))) {
            case 'yes':
            case 'si':
            case 'sì':
                return true;
        }

        return false;
    }

    protected static function parseTipologia(string $input): string
    {
        preg_match('/(appartamento|monolocale|bilocale|trilocale|quadrilocale|villa|villetta|monofamiliare|bifamiliare|trifamiliare|quadrifamiliare)/i', $input, $matches);
        return strtolower($matches[1]);
    }

    protected static function getImagesLink(Crawler $node): array
    {
        $map = static fn(Image $image) => $image->getUri();

        return array_map($map, $node->images());
    }
}
