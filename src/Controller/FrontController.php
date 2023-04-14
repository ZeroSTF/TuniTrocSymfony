<?php

namespace App\Controller;
use App\Entity\Produit;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/')]
class FrontController extends AbstractController
{
   

    #[Route('/back', name: 'app_front')]
    public function index(): Response
    {
        return $this->render('front/index.html.twig', [
            'controller_name' => 'FrontController',
        ]);
    }
    
    #[Route('/front', name: 'app_produit_index2', methods: ['GET'])]
    public function index1(EntityManagerInterface $entityManager): Response
    {
        $produits = $entityManager
            ->getRepository(Produit::class)
            ->findAll();

        return $this->render('produit/index2.html.twig', [
            'produits' => $produits,
        ]);
    }



    
}
