<?php

namespace App\Controller;

use App\Entity\Episode;
use App\Entity\User;
use App\Form\EpisodeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/episode')]
class EpisodeController extends AbstractController
{
    #[Route('/', name: 'episode_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $episodes = $entityManager
            ->getRepository(Episode::class)
            ->findAll();

        return $this->render('episode/index.html.twig', [
            'episodes' => $episodes,
        ]);
    }

    #[Route('/new', name: 'episode_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $episode = new Episode();
        $form = $this->createForm(EpisodeType::class, $episode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($episode);
            $entityManager->flush();

            return $this->redirectToRoute('episode_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('episode/new.html.twig', [
            'episode' => $episode,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'episode_show', methods: ['GET'])]
    public function show(Episode $episode): Response
    {
        /** @var User */
        $user = $this->getUser();

        $season = $episode->getSeason();
        $serie = $season->getSeries();

        $isSerieWatched = false;
        $isEpisodeWatched = false;
        if($user != NULL){
            $isSerieWatched = $serie->isFullyWatched($user);
            $isEpisodeWatched = $episode->isFullyWatched($user);
        }

        return $this->render('episode/show.html.twig', [
            'serie' => $serie,
            'season' => $season,
            'episode' => $episode,
            'is_serie_watched' => $isSerieWatched,
            'is_episode_watched' => $isEpisodeWatched,
        ]);
    }

    #[Route('/{id}/edit', name: 'episode_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Episode $episode, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EpisodeType::class, $episode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('episode_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('episode/edit.html.twig', [
            'episode' => $episode,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'episode_delete', methods: ['POST'])]
    public function delete(Request $request, Episode $episode, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$episode->getId(), $request->request->get('_token'))) {
            $entityManager->remove($episode);
            $entityManager->flush();
        }

        return $this->redirectToRoute('episode_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/watch/{id}', name: 'watch_episode', methods: ['GET'])]
    public function watchSeason(Episode $episode, EntityManagerInterface $manager): Response
    {
        /** @var User */
        $user = $this->getUser();

        if($user != NULL){
            $episode->toggleFullyWatched($user);
            $manager->flush();
        }

        return $this->redirectToRoute('episode_show', ['id' => $episode->getId()]);
    }
}
