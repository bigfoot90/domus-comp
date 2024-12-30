<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class YesNoExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('yesno', [$this, 'renderYesNo'], ['is_safe' => ['html']]),
        ];
    }

    public function renderYesNo(?bool $value): string
    {
        return $value
            ? '<span style="color: #22c55e">✔️</span>'
            : '<span style="color: #ef4444">❌</span>';
    }
}
