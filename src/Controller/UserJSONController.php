<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserJSONController extends abstractcontroller
{
    #[Route('json/afficherUser', name: 'afficherUser', methods: ['GET', 'POST'])]
    public function indexUser(EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $users = $entityManager
            ->getRepository(User::class)
            ->findAll();

        $json = $serializer->serialize($users, 'json');
        return new JsonResponse(json_decode($json));
    }

    #[Route('json/ajouterUser', name: 'ajouterUser', methods: ['GET', 'POST'])]
    public function addUser(EntityManagerInterface $entityManager, SerializerInterface $serializer, Request $request, UserPasswordHasherInterface $userPasswordHasher): JsonResponse
    {
        $user = new User();

        $user->setSalt("");
        $user->setPwd(
            $userPasswordHasher->hashPassword(
                $user,
                $request->get('pwd')
            )
        );
        $user->setPhoto('photo');
        $user->setDate(new \DateTime());
        $user->setEmail($request->get('email'));
        $user->setNom($request->get('nom'));
        $user->setPrenom($request->get('prenom'));
        $user->setNumTel($request->get('numTel'));
        $user->setVille($request->get('ville'));
        $user->setValeurFidelite($request->get('valeurFidelite'));
        $role = $request->get('role');

        if ($role === "Admin") {
            $user->setRole(true);
        } else {
            $user->setRole(false);
        }
        $user->setEtat($request->get('etat'));

        $entityManager->persist($user);
        $entityManager->flush();

        $json = $serializer->serialize($user, 'json');
        return new JsonResponse(json_decode($json));
    }

    #[Route('json/modifierUser', name: 'modifierUser', methods: ['GET', 'POST'])]
    public function editUser(EntityManagerInterface $entityManager, SerializerInterface $serializer, Request $request, UserPasswordHasherInterface $userPasswordHasher): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($request->get('id'));
        $user->setSalt("");
        $user->setPwd(
            $userPasswordHasher->hashPassword(
                $user,
                $request->get('pwd')
            )
        );
        $user->setPhoto($request->get('photo'));
        $user->setDate(new \DateTime());
        $user->setEmail($request->get('email'));
        $user->setNom($request->get('nom'));
        $user->setPrenom($request->get('prenom'));
        $user->setNumTel($request->get('numTel'));
        $user->setVille($request->get('ville'));
        $user->setValeurFidelite($request->get('valeurFidelite'));
        $role = $request->get('role');

        if ($role === "Admin") {
            $user->setRole(true);
        } else {
            $user->setRole(false);
        }
        $user->setEtat($request->get('etat'));
        $entityManager->flush();

        $json = $serializer->serialize($user, 'json');
        return new JsonResponse(json_decode($json));
    }
}