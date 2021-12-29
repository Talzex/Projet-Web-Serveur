<?php

namespace App\Controller;

use App\Entity\ExternalRating;
use App\Repository\SeriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'search')]
    public function index(): Response
    {
        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
        ]);
    }

    public function searchBar(){
        $form = $this->createFormBuilder(null)
            ->add('query', TextType::class)
            ->add('submit', SubmitType::class)
            ->getForm()
        ;

        return $this->render('search/searchBar.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/results', name: 'search_series')]
    public function handleSearch(EntityManagerInterface $entityManager, Request $request, SeriesRepository $seriesRepo) {
        $series = [];
        if($request){
            $request = $request->request->all();
            if($request){
                $query = $request['form']['query'];
                $series = $seriesRepo->getSeriesByName($query);
            }
        }

        return $this->render('series/index.html.twig', [
            'series' => $series,
            'num_page' => 1,
        ]);
    }
}
