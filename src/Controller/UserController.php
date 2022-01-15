<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/manage', name: 'user_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager
            ->getRepository(User::class)
            ->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/delete/{id}', name: 'user_delete', methods: ['POST', 'GET'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        /** @var User */
        $getUser = $this->getUser();

        if($user != $getUser && $getUser->getAdmin()){

            $series = $user->getSeries(); 
            foreach($series as $serie){
                $user->removeSeries($serie);
            }

            $entityManager->createQueryBuilder('r')
                ->delete()
                ->from('App\Entity\Rating', 'r')
                ->where('r.user = :u')
                ->setParameter('u', $user->getId())
                ->getQuery()
                ->getResult();

            $entityManager->remove($user);
            $entityManager->flush();
        } else {
            return $this->redirectToRoute('default', [], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
    }
}
