<?php

namespace App\Controller;

use App\Model\House;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    public string $dataDir;

    public function __construct(string $dataDir)
    {
        $this->dataDir = $dataDir;
    }

    #[Route('/', 'root')]
    #[Template('index.html.twig')]
    public function indexAction(Request $request)
    {
        $catalog = $this->loadCatalog();

        $mapMarker = static fn($item) => ['name' => $item->name, 'lat' => $item->coordinates['latitude'], 'long' => $item->coordinates['longitude']];
        $markers = array_map($mapMarker, $catalog);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'catalog' => $catalog,
                'markers' => $markers,
            ]);
        } else {
            return [
                'catalog' => $catalog,
                'markers' => $markers,
            ];
        }
    }

    /**
     * @return House[]
     */
    private function loadCatalog(): array
    {
        try {
            $filename = $this->dataDir . 'catalog.bin';
            return file_exists($filename) ? unserialize(file_get_contents($filename)) : [];
        } catch (\Throwable) {
            return [];
        }
    }
}
