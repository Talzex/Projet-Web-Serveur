<?php

namespace App\Controller;

use App\Entity\Season;
use App\Entity\User;
use App\Form\SeasonType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/season')]
class SeasonController extends AbstractController
{
    #[Route('/', name: 'season_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $seasons = $entityManager
            ->getRepository(Season::class)
            ->findAll();

        return $this->render('season/index.html.twig', [
            'seasons' => $seasons,
        ]);
    }

    #[Route('/new', name: 'season_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $season = new Season();
        $form = $this->createForm(SeasonType::class, $season);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($season);
            $entityManager->flush();

            return $this->redirectToRoute('season_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('season/new.html.twig', [
            'season' => $season,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'season_show', methods: ['GET'])]
    public function show(Season $season): Response
    {
        /** @var User */
        $user = $this->getUser();
        $serie = $season->getSeries();

        $isSerieWatched = false;
        $isSeasonWatched = false;
        if($user != NULL){
            $isSerieWatched = $serie->isFullyWatched($user);
            $isSeasonWatched = $season->isFullyWatched($user);
        }

        return $this->render('season/show.html.twig', [
            'serie' => $serie,
            'season' => $season,
            'is_serie_watched' => $isSerieWatched,
            'is_season_watched' => $isSeasonWatched,
        ]);
    }

    #[Route('/{id}/edit', name: 'season_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Season $season, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SeasonType::class, $season);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('season_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('season/edit.html.twig', [
            'season' => $season,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'season_delete', methods: ['POST'])]
    public function delete(Request $request, Season $season, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$season->getId(), $request->request->get('_token'))) {
            $entityManager->remove($season);
            $entityManager->flush();
        }

        return $this->redirectToRoute('season_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/watch/{id}', name: 'watch_season', methods: ['GET'])]
    public function watchSeason(Season $season, EntityManagerInterface $manager): Response
    {
        /** @var User */
        $user = $this->getUser();

        if($user != NULL){
            $season->toggleFullyWatched($user);
            $manager->flush();
        }

        return $this->redirectToRoute('season_show', ['id' => $season->getId()]);
    }
}
