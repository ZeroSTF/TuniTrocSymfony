<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class JSONController extends abstractcontroller
{
    #[Route('json/displayappointment', name: 'afficherUser', methods: ['GET', 'POST'])]
    public function indexUser(EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $users = $entityManager
            ->getRepository(User::class)
            ->findAll();

        // Calculate the highest valeurFidelite value
        $maxFidelite = 0;
        foreach ($users as $user) {
            if ($user->getValeurFidelite() > $maxFidelite) {
                $maxFidelite = $user->getValeurFidelite();
            }
        }

        $json = $serializer->serialize($users, 'json');
        $formatted = $serializer->serialize($json, 'json', ['groups' => ['normal']]);


        //$serializer = new Serializer([new ObjectNormalizer()]);
        return new JsonResponse(json_decode($json));


    }
}