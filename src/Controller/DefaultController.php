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
        $genres = ['Sci-Fi', 'Action', 'Fantasy'];
        $series = [];
        foreach($genres as $genre){
            $series[$genre] = $sr->getRandomSeries($genre);
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
