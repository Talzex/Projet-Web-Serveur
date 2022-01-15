<?php

namespace App\Controller;

use App\Entity\ExternalRatingSource;
use App\Entity\Genre;
use App\Entity\Actor;
use App\Entity\ExternalRating;
use App\Entity\Season;
use App\Entity\Series;
use App\Entity\Rating;
use App\Entity\User;
use App\Entity\Episode;
use App\Form\SeriesType;
use App\Form\RatingType;
use App\Repository\RatingRepository;
use App\Repository\SeriesRepository;
use Container5pYjq0X\PaginatorInterface_82dac15;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/series')]
class SeriesController extends AbstractController
{

    #[Route('/', name: 'series_index', methods: ['GET'])]
    public function index(Request $request, SeriesRepository $seriesRepo, PaginatorInterface $paginator): Response
    {
        $sort = $request->query->get('s');
        $query = $request->query->get('query') != NULL ? $request->query->get('query') : NULL; 
        
        $series = $seriesRepo->getSeries($sort, $query);

        $numPage = $request->query->get('page') != NULL ? $request->query->get('page') : 1;
        $series = $paginator->paginate(
            $series, // Requête contenant les données à paginer (ici nos articles)
            $numPage, // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            24 // Nombre de résultats par page
        );
        
        return $this->render('series/index.html.twig', [
            'series' => $series,
            'order' => $sort,
        ]);
    }

    #[Route('/new', name: 'series_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        return $this->renderForm('series/new.html.twig', [
            'error' => NULL,
        ]);

    }
    #[Route('/add', name: 'series_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $imdb = $request->query->get('imdb');
        $api_key = '3c98a627';

        $data = [
            'i' => $imdb,
            'apikey' => $api_key,
        ];
        
        $url = 'http://www.omdbapi.com/?' . http_build_query($data) . '&plot=full';
        $poster = 'http://img.omdbapi.com/?' . http_build_query($data);
        
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        
        //dump($data); die;

        $serieExist = $em->getRepository(Series::class)->findOneBy(['imdb' => $imdb]);

        $episodeExist = $em->getRepository(Episode::class)->findOneBy(['imdb' => $imdb]);
        
        if($data['Response'] == "True"){
            if(isset($data['Type']) &&  $data['Type'] == 'series'){
                if(!$serieExist){
                    $poster = file_get_contents($data['Poster']);

                    $serie = new Series;

                    $years = explode('–', $data['Year']);
                    $yearStart = $years[0];
                    $isYearEnd = strpos($data['Year'], '–');
                    $actors = explode(', ', $data['Actors']);
                    $genres = explode(', ', $data['Genre']);
                    $ratings = $data['Ratings'];

                    $serie->setTitle($data['Title'])
                        ->setYearStart(intval($yearStart));
                        if($isYearEnd){
                            $serie->setYearEnd(intval($years[1]));
                        } else {
                            $serie->setYearEnd(NULL);
                        }
                    $serie->setPlot($data['Plot'])
                        ->setImdb($data['imdbID'])
                        ->setDirector($data['Director'])
                        ->setAwards($data['Awards'])
                        ->setPoster($poster);
                    $em->persist($serie);
                    $em->flush();
                        
                    foreach($ratings as $rating){
                        $r = new ExternalRating;
                        $ratingSource = $em->getRepository(ExternalRatingSource::class)->findOneBy(['name' => $rating['Source']]);
                        if($ratingSource != NULL){
                            $rc = $ratingSource;
                        } else {
                            $rc = new ExternalRatingSource;
                            $rc->setName($rating['Source']);
                            $em->persist($rc);
                            $em->flush();
                        }
                        $r->setSeries($serie)
                            ->setSource($rc)
                            ->setValue($rating['Value'])
                            ->setVotes(intval(trim($data['imdbVotes'], ',')));
                            $em->persist($r);
                            $em->flush();
                    }

                    foreach($actors as $actor){
                        $a = new Actor;
                        $a->setName($actor)
                            ->addSeries($serie);
                        $serie->addActor($a);
                        $em->persist($a);
                        $em->flush();
                    }

                    foreach($genres as $genre){
                        $g = new Genre;
                        $g->setName($genre)
                            ->addSeries($serie);
                        $serie->addGenre($g);
                        $em->persist($r);
                        $em->flush($r);
                    }

                    return $this->redirectToRoute('series_show', [
                        'id' => $serie->getId(),
                    ], Response::HTTP_SEE_OTHER);
                } else {
                    return $this->render('series/new.html.twig', [
                        'error' => 'Cette série est déjà dans la base.',
                    ]);
                }
            } elseif(isset($data['Type']) && $data['Type'] == 'episode'){
                if(!$episodeExist){
                    $episode = new Episode;
                    $episode->setTitle($data['Title'])
                    ->setImdb($imdb)
                    ->setImdbrating(floatval($data['imdbRating']))
                    ->setNumber(intval($data['Episode']));

                    $serie = $em->getRepository(Series::class)->findOneBy(['imdb' => 't' . $data['seriesID']]);
                    $season = $em->getRepository(Season::class)->findOneBy(['number' => $data['Season'], 'series' => $serie]);

                    if($season == NULL){
                        $season = new Season;
                    }
                    if($serie != NULL){
                        $season->setSeries($serie);
                    } else {
                        return $this->render('series/new.html.twig', [
                            'error' => 'Veuillez importer la série avant les épisodes.',
                        ]);
                    }
    
                    $season->addEpisode($episode)
                        ->setNumber($data['Season']);
                    $em->persist($season);
                    $em->flush();
                    $episode->setSeason($season);
                    $date = new \DateTime();
                    $date->format('Y-m-d');
                    $episode->setDate($date);
                    $em->persist($episode);
                    $em->flush();

                    return $this->redirectToRoute('episode_show', [
                        'id' => $episode->getId(),
                    ], Response::HTTP_SEE_OTHER);
                } else {
                    return $this->render('series/new.html.twig', [
                        'error' => 'Cette épisode est déjà dans la base.',
                    ]);
                }
            } else {
                return $this->render('series/new.html.twig', [
                    'error' => 'Ce que vous tentez d\'ajouter n\'est pas considéré comme un épisode ou une série.',
                ]);
            }
        } else {
            return $this->render('series/new.html.twig', [
                'error' => 'Cette série n\'existe pas.',
            ]);
        }
        
        return $this->render('series/new.html.twig', [
            'error' => 'Succès',
        ]);
    }

    #[Route('/view/{id}', name: 'series_show', methods: ['GET', 'POST'])]
    public function show(EntityManagerInterface $entityManager, Series $serie, RatingRepository $ratingRepository, Request $request): Response
    {
        /** @var User */
        $user = $this->getUser();
        $rating = new Rating;
        $submitText = "Envoyer";
        $isSerieFullyWatched = false;
        $seasonWatched = [];
        
        if ($user != NULL) {
            if ($ratingRepository->isRated($user, $serie)) {
                $rating = $ratingRepository->getRating($user, $serie);
                $submitText = "Modifier";
            }

            // Série vue/non vue
            $isSerieFullyWatched = $serie->isFullyWatched($user);
            foreach($serie->getSeasons() as $s){
                if($s->isFullyWatched($user)){
                    array_push($seasonWatched, $s);
                }
            }
        }
        
        $ratingForm = $this->createForm(RatingType::class, $rating, [
            'label' => $submitText,
        ]);

        $ratingForm->handleRequest($request);

        if ($ratingForm->isSubmitted() && $ratingForm->isValid() && $user != NULL) {
            $isRated = $ratingRepository->isRated($user, $serie);
            $date = new \DateTime();
            $date->format('Y-m-d H:i:s');
            $rating->setDate($date);
            if (!$isRated) {
                $rating->setSeries($serie);
                $rating->setUser($user);
                $serie->addRating($rating);
            }
            $entityManager->persist($rating);
            $entityManager->flush();
        }

        $histo = [];
        for($i = 0; $i <= 10; $i++){
            $histo[$i] = 0;
        }
        foreach($serie->getRatings() as $rating){
            $histo[$rating->getValue()]++;
        }

        return $this->render('series/show.html.twig', [
            'series' => $serie,
            'ratingForm' => $ratingForm->createView(),
            'is_serie_watched' => $isSerieFullyWatched,
            'seasons_watched' => $seasonWatched,
            'histogramme' => $histo,
        ]);
    }
    #[Route('/view/{id}/trailer', name: 'series_trailer', methods: ['GET'])]
    public function trailer(Series $series): Response
    {
        $series_url = $series->getYoutubeTrailer();
        if($series_url == NULL)
            return $this->redirectToRoute('series_show', [
                'id' => $series->getId(),
            ]);
        return $this->redirect($series_url);
    }

    #[Route('/view/{id}/imdb', name: 'series_imdb', methods: ['GET'])]
    public function imdb(Series $series): Response
    {
        $series_url = $series->getImdb();
        return $this->redirect('https://www.imdb.com/title/' . $series_url);
    }

    #[Route('/view/{id}/imdb_episode', name: 'episode_imdb', methods: ['GET'])]
    public function imdb_episode(Episode $episode): Response
    {
        $episode_url = $episode->getImdb();
        return $this->redirect('https://www.imdb.com/title/' . $episode_url);
    }


    #[Route('/{id}/edit', name: 'series_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Series $series, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SeriesType::class, $series);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('series_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('series/edit.html.twig', [
            'series' => $series,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'series_delete', methods: ['POST'])]
    public function delete(Request $request, Series $series, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $series->getId(), $request->request->get('_token'))) {
            $entityManager->remove($series);
            $entityManager->flush();
        }

        return $this->redirectToRoute('series_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/poster/{id}', name: 'series_poster')]
    public function getPoster(Series $serie): Response
    {
        $poster = $serie->getPoster();
        $headers = array(
            'Content-Type'     => 'image/png',
            'Content-Disposition' => 'inline; filename="' . $poster . '"'
        );
        return new Response(stream_get_contents($poster, -1, 0), 200, $headers);
    }

    #[Route('/follow/{id}', name: 'follow_serie', methods: ['GET'])]
    public function followSerie(Series $serie, EntityManagerInterface $manager): Response
    {
        /** @var User */
        $user = $this->getUser();
        dump($user);
        if ($user != NULL) {
            $user->followToggle($serie);
            $manager->flush();
        }
        return $this->redirectToRoute('series_show', ['id' => $serie->getId()]);
    }

    #[Route('/following', name: 'user_series', methods: ['GET'])]
    public function userSeries(Request $request, PaginatorInterface $paginator, SeriesRepository $seriesRepo): Response
    {
        $numPage = $request->query->get('page') != NULL ? $request->query->get('page') : 1;
        $sort = $request->query->get('s');
        /** @var User */
        $user = $this->getUser();
        $series = $user->getSeries() != NULL ? $user->getSeries() : "Vous ne suivez aucune série.";

        // On récupère les notes depuis un tableau de séries existant
        $series = $seriesRepo->getSeries(null, null, $series);

        $series = $paginator->paginate(
            $series,
            $numPage,
            24
        );

        return $this->render('series/index.html.twig', [
            'series' => $series,
            'order' => $sort
        ]);
    }

    #[Route('/watch/{id}', name: 'watch_serie', methods: ['GET'])]
    public function watchSerie(Request $request, Series $serie, EntityManagerInterface $manager): Response
    {
        /** @var User */
        $user = $this->getUser();

        if($user != NULL){
            $serie->toggleFullyWatched($user);
            $manager->flush();
        }

        return $this->redirectToRoute('series_show', ['id' => $serie->getId()]);
    }

    #[Route('/deleteRating/{id}', name: 'delete_rating', methods: ['GET'])]
    public function deleteRating(Rating $rating, RatingRepository $rr, EntityManagerInterface $entityManager): Response
    {
        $rr->deleteRating($rating);
        return $this->redirectToRoute('series_show', ['id' => $rating->getSeries()->getId()]);
    }
}
