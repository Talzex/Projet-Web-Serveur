<?php

namespace App\Controller;

use App\Repository\SeriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'default')]
    public function index(SeriesRepository $sr): Response
    {
        $genres = ['Fantasy', 'Action', 'Horror', 'Crime'];
        $series = [];
        foreach($genres as $genre){
            $series[$genre] = $sr->getRandomSeries($genre, 6);
        }
        return $this->render('default/index.html.twig', [
            'seriesList' => $series,
        ]);
    }

    #[Route('/apropos', name: 'apropos')]
    public function apropos() : Response
    {
        return $this->render('default/apropos.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }
}
