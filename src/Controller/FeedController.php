<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Feed;
use App\Entity\FeedSource;
use App\Entity\Album;
use App\Entity\Template;
use App\Form\Type\Entity\TemplateType;
use App\Repository\TemplateRepository;
use App\Form\Type\Entity\FeedSourceType;
use App\Form\Type\Entity\DisplayConfigurationType;
use App\Repository\FeedSourceRepository;
use App\Repository\FeedRepository;
use App\Repository\AlbumRepository;
use App\Repository\PhotoRepository;
use App\Service\CachedValuesGetter;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class FeedController extends AbstractController
{
    #[Route(path: '/feeds', name: 'app_feed_index', methods: ['GET'])]
    //#[Route(path: '/user/{username}/feeds', name: 'app_shared_feed_index', methods: ['GET'])]
    public function index(TemplateRepository $templateRepository, FeedRepository $feedRepository, FeedSourceRepository $feedSourceRepository): Response
    {
        $this->denyAccessUnlessFeaturesEnabled(['templates']);

        // Obtener todas las fuentes de feeds ordenadas por nombre
        $feedSources = $feedSourceRepository->findBy([], ['url' => Criteria::ASC]);

        /*return $this->render('App/Feed/index.html.twig', [
            'results' => $templateRepository->findAllWithCounters(),
        ]);*/

        $feedSourcesCounter = \count($feedSources);
        $feedsCounter = $feedRepository->count([]);

        return $this->render('App/Feed/index.html.twig', [
            'feedSources' => $feedSources,
            'feedSourcesCounter' => $feedSourcesCounter,
            'feedsCounter' => $feedsCounter,
        ]);
    }

    #[Route(path: '/feeds/edit', name: 'app_feed_edit_index', methods: ['GET', 'POST'])]
    public function editIndex(
        Request $request,
        TranslatorInterface $translator,
        ManagerRegistry $managerRegistry
    ): Response {
        $form = $this->createForm(DisplayConfigurationType::class, $this->getUser()->getAlbumsDisplayConfiguration());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $managerRegistry->getManager()->flush();
            $this->addFlash('notice', $translator->trans('message.album_index_edited'));

            return $this->redirectToRoute('app_feed_index');
        }

        return $this->render('App/Album/edit_index.html.twig', [
            'form' => $form
        ]);
    }

    #[Route(path: '/feeds/add', name: 'app_feed_add', methods: ['GET', 'POST'])]
    public function add(Request $request, TranslatorInterface $translator, ManagerRegistry $managerRegistry): Response
    {
        $this->denyAccessUnlessFeaturesEnabled(['feeds']);

        $feed = new FeedSource();
        $form = $this->createForm(FeedSourceType::class, $feed);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $managerRegistry->getManager()->persist($feed);
            $managerRegistry->getManager()->flush();

            $this->addFlash('notice', $translator->trans('message.template_added', ['template' => $feed->getName()]));

            return $this->redirectToRoute('app_feed_show', ['id' => $feed->getId()]);
        }

        return $this->render('App/Feed/add.html.twig', [
            'feed' => $feed,
            'form' => $form,
        ]);
    }

    #[Route(path: '/feeds/{id}/edit', name: 'app_feed_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Album $album, TranslatorInterface $translator, ManagerRegistry $managerRegistry): Response
    {
        $this->denyAccessUnlessFeaturesEnabled(['albums']);

        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $managerRegistry->getManager()->flush();
            $this->addFlash('notice', $translator->trans('message.album_edited', ['album' => $album->getTitle()]));

            return $this->redirectToRoute('app_album_show', ['id' => $album->getId()]);
        }

        return $this->render('App/Album/edit.html.twig', [
            'form' => $form,
            'album' => $album,
        ]);
    }

    #[Route(path: '/feeds/{id}/delete', name: 'app_feeds_delete', methods: ['POST'])]
    public function delete(Request $request, Album $album, TranslatorInterface $translator, ManagerRegistry $managerRegistry): Response
    {
        $this->denyAccessUnlessFeaturesEnabled(['albums']);

        $form = $this->createDeleteForm('app_feeds_delete', $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $managerRegistry->getManager()->remove($album);
            $managerRegistry->getManager()->flush();
            $this->addFlash('notice', $translator->trans('message.album_deleted', ['album' => $album->getTitle()]));
        }

        return $this->redirectToRoute('app_feed_index');
    }

    #[Route(path: '/feed/{id}', name: 'app_feed_show', methods: ['GET'])]
    public function show(
        #[MapEntity(expr: 'repository.findWithItems(id)')] FeedSource $template
    ): Response {
        $this->denyAccessUnlessFeaturesEnabled(['templates']);

        return $this->render('App/Feed/show.html.twig', [
            'template' => $template,
        ]);
    }
}
