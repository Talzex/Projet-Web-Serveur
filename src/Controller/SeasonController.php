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
    #[Route('/{id}', name: 'season_show', methods: ['GET'])]
    public function show(Season $season): Response
    {
        /** @var User */
        $user = $this->getUser();
        $serie = $season->getSeries();
        $episodesWatched = [];

        $isSerieWatched = false;
        $isSeasonWatched = false;
        if($user != NULL){
            $isSerieWatched = $serie->isFullyWatched($user);
            $isSeasonWatched = $season->isFullyWatched($user);

            foreach($season->getEpisodes() as $e){
                if($e->isFullyWatched($user)){
                    array_push($episodesWatched, $e);
                }
            }
        }

        return $this->render('season/show.html.twig', [
            'serie' => $serie,
            'season' => $season,
            'is_serie_watched' => $isSerieWatched,
            'is_season_watched' => $isSeasonWatched,
            'episodes_watched' => $episodesWatched,
        ]);
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
