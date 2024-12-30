<?php

namespace App\Scrubbers;

use App\Model\House;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(self::TAG)]
interface ScrabberInterface
{
    const TAG = 'app.scrabber';

    public function supportsUrl(string $url): bool;

    public function scrab(string $url): House;
}
