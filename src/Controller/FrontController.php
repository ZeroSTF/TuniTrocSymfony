<?php

namespace App\Controller;
use App\Entity\Produit;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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
    public function index1(EntityManagerInterface $entityManager,HttpClientInterface $httpClient): Response
    {
        $produits = $entityManager
            ->getRepository(Produit::class)
            ->findAll();

        $response1 = $httpClient->request('GET', 'http://api.openweathermap.org/data/2.5/weather?q=tunis&appid=5b491eb9b69dd529d5cb765278c52609&units=metric&lang=fr');
        $content1 = $response1->getContent();
        $weatherData1 = json_decode($content1, true);
        $weather1 = $weatherData1['weather'];
        return $this->render('produit/index2.html.twig', [
            'produits' => $produits,
            'weather_data' => $weatherData1,
        ]);
    }



    
}
