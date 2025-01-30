<?php

namespace App\Controller;

use App\Repository\CollectionRepository;
use App\Repository\ItemRepository;
use App\Repository\DatumRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(CollectionRepository $collectionRepository, ItemRepository $itemRepository, DatumRepository $datumRepository): Response
    {
        // Obtener todas las colecciones principales
        $collections = $collectionRepository->findBy(['parent' => null]);

        // Contar el número de colecciones
        $collectionsCounter = count($collections);

        // Calcular el total de elementos
        $itemsCounter = 0;
        foreach ($collections as $collection) {
            $itemsCounter += $itemRepository->countByCollection($collection);
        }

        // Calcular el total gastado usando la función computeTotalPrices
        $visibility = 'public'; // O la visibilidad que desees
        $totalSpent = $datumRepository->computeTotalPrices($collections, $visibility);

        // Pasar las variables al template
        return $this->render('App/Dashboard/index.html.twig', [
            'collectionsCounter' => $collectionsCounter,
            'itemsCounter' => $itemsCounter,
            'totalSpent' => $totalSpent,
        ]);
    }
}
