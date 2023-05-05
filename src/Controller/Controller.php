<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    /** 
     *  #[Route('/', name: 'display_transport')]

     */
    #[Route('/admin', name: 'app_back')]
    public function index(): Response
    {
        return $this->render('front/index.html.twig', [
            'controller_name' => 'BackController',
        ]);
    }
}
