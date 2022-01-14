<?php

namespace App\Command;

use App\Entity\Rating;
use App\Entity\User;
use App\Entity\Series;
use App\Repository\SeriesRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class UserCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:generate-ratings';

    private $entityManager;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->entityManager = $em;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Command to generate ratings on each page')
            ->addArgument('ratingNumber', InputArgument::REQUIRED, 'Number of ratings per page')
            ->setHelp('This command allows you to generate ratings on each series.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ratingNumber = $input->getArgument('ratingNumber') == NULL ? 10 : $input->getArgument('ratingNumber');

        $output->writeln([
            'Generating ' . $ratingNumber . ' random ratings on each series...',
            '====================='
        ]);

        $em = $this->entityManager;

        $userRepo = $em->getRepository(User::class);
        $seriesRepo = $em->getRepository(Series::class);

        $date = new \DateTime();
        $date->format('Y-m-d H:i:s');
         
        $user = $userRepo->findOneBy(['userId' => 'FakeRating']);
        if($user == NULL) {
            $user = new User;
            $user->setName('FakeRating')
                 ->setEmail('fakeEmail@fake.com')
                 ->setPassword('fakePassword')
                 ->setRegisterDate($date)
                 ->setUserId('FakeRating')
            ;
            $em->persist($user);
            $em->flush();
        }
        
        $series = $seriesRepo->findAll();

        foreach($series as $serie) {

            for($i = 0; $i < $ratingNumber; $i++){
                $mark = rand(0, 10);
                switch ($mark) {
                    case 0:
                        $comment = "Je déteste, vraiment...";
                        break;
                    case 1:
                        $comment = "C'est nul.. 1 point pour les pop-corns.";
                        break;
                    case 2:
                        $comment = "Mouais... on a connu mieux.";
                        break;
                    case 3:
                        $comment = "J'ai pas aimé.";
                        break;
                    case 4:
                        $comment = "Je me suis endormi, mais j'ai quand même aimé le début.";
                        break;
                    case 5:
                        $comment = "C'est à la fois bien et nul !";
                        break;
                    case 6:
                        $comment = "Bonne série.";
                        break;
                    case 7:
                        $comment = "Série intéressante, je recommande.";
                        break;
                    case 8:
                        $comment = "Vraiment super, j'ai beaucoup aimé.";
                        break;
                    case 9:
                        $comment = "J'adooooooooore.";
                        break;
                    case 10:
                        $comment = "La meilleure des séries !!!";
                        break;
                }

                $r = new Rating;

                $r->setComment($comment);
                $r->setValue($mark);
                $r->setDate($date);
                $r->setUser($user);
                $r->setSeries($serie);

                $em->persist($r);
                $em->flush();
            }
        }

        return Command::SUCCESS;
    }
}
