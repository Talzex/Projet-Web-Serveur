<?php

namespace App\Controller;

use App\Entity\Episode;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/episode')]
class EpisodeController extends AbstractController
{
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
            'episode' => $episode,
            'is_serie_watched' => $isSerieWatched,
            'is_episode_watched' => $isEpisodeWatched,
        ]);
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
