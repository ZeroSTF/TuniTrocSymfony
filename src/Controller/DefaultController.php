<?php

namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class DefaultController extends AbstractController
{


    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    #[Route('/homepage', name: 'app_default')]
    public function index(): Response
    {
        return $this->render('homepage/shop.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }

    #[Route('/savepost', name: 'savepost')]
    public function save(): Response
    {
        $entityManager = $this->entityManager;
        $post = new Post();
        $post->setTitre('test');
        $post->setContenu('test');
        $post->setDate(new \DateTime());
        $post->setIdUser(5);
        $post->setLikes(50);
        $post->setDislikes(50);

        $entityManager->persist($post);
        $entityManager->flush();

        return new Response('Post enregistré avec id :' .$post->getId());
    }
}
