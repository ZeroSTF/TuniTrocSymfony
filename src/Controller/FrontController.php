<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;

class FrontController extends AbstractController
{
    #[Route('/front', name: 'app_front')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)
        ->findBy([], ['valeurFidelite' => 'DESC'], 10);

    return $this->render('front/index.html.twig', [
        'users' => $users,
        'controller_name' => 'FrontController',
    ]);
    }
}
