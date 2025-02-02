<?php

namespace App\Controller;

use App\Repository\CollectionRepository;
use App\Repository\ItemRepository;
use App\Repository\DatumRepository;
use App\Repository\FeedSourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    #[Route(path: '/dashboard', name: 'app_homepage', methods: ['GET'])]
    public function index(CollectionRepository $collectionRepository, ItemRepository $itemRepository, DatumRepository $datumRepository, FeedSourceRepository $feedSourceRepository): Response
    {
        // Get all main collections
        $collections = $collectionRepository->findBy(['parent' => null]);

        // Get all the feed sources for the slide show
        $feedSources = $feedSourceRepository->findAll();

        // Counting the number of collections
        $collectionsCounter = count($collections);

        // Calculate the total number of elements
        $itemsCounter = 0;
        foreach ($collections as $collection) {
            $itemsCounter += $itemRepository->countByCollection($collection);
        }

        // Calculate the total spent using the function computeTotalPrices
        $visibility = 'public'; // Or the visibility you want
        $totalSpent = $datumRepository->computeTotalPrices($collections, $visibility);

        // Pass the variables to the template
        return $this->render('App/Dashboard/index.html.twig', [
            'feedSources' => $feedSources,
            'collectionsCounter' => $collectionsCounter,
            'itemsCounter' => $itemsCounter,
            'totalSpent' => $totalSpent,
        ]);
    }

    /**
     * Devuelve en formato JSON los feeds correspondientes a la fuente seleccionada.
     *
     * @Route("/dashboard/feeds", name="dashboard_feeds", methods={"GET"})
     */
    public function feeds(Request $request, FeedRepository $feedRepository): JsonResponse
    {
        // Recupera el ID de la fuente a través del parámetro GET 'sourceId'
        $sourceId = $request->query->get('sourceId');

        if (!$sourceId) {
            // Si no se envía una fuente, devolvemos un error 400 (Bad Request)
            return new JsonResponse(['error' => 'No source ID provided'], 400);
        }

        // Recupera los feeds filtrados por la fuente.
        // Se asume que la entidad Feed tiene una relación con la fuente,
        // por ejemplo, un campo 'source' que se puede comparar con $sourceId.
        $feeds = $feedRepository->findBy(
            ['source' => $sourceId],
            ['pubDate' => 'DESC'] // Ordena por fecha de publicación descendente, si aplica
        );

        // Prepara los datos para JSON
        $data = [];
        foreach ($feeds as $feed) {
            $data[] = [
                'title' => $feed->getTitle(),
                'description' => $feed->getDescription(),
                'link' => $feed->getLink(),
                'pubDate' => $feed->getPubDate() ? $feed->getPubDate()->format('Y-m-d H:i') : null,
            ];
        }

        return new JsonResponse($data);
    }
}
