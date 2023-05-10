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

    #[Route('json/ajouterUser', name: 'ajouterUser', methods: ['POST'])]
    public function addUser(EntityManagerInterface $entityManager, SerializerInterface $serializer, Request $request, UserPasswordHasherInterface $userPasswordHasher): JsonResponse
    {
        $user = new User();

        // Set user properties
        $user->setSalt('');
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
        $user->setRole($request->get('role') === 'Admin');
        $user->setEtat($request->get('etat'));

        $entityManager->persist($user);
        $entityManager->flush();

        $json = $serializer->serialize($user, 'json');
        return new JsonResponse(json_decode($json));
    }


    #[Route('json/modifierUser', name: 'modifierUser', methods: ['POST'])]
    public function editUser(EntityManagerInterface $entityManager, SerializerInterface $serializer, Request $request, UserPasswordHasherInterface $userPasswordHasher): JsonResponse
    {
        $userId = $request->get('id');
        $user = $entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Update user properties
        $user->setSalt('');
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
        $user->setRole($role === 'Admin');
        $user->setEtat($request->get('etat'));

        $entityManager->flush();

        $json = $serializer->serialize($user, 'json');
        return new JsonResponse(json_decode($json));
    }


    #[Route('json/suppUser', name: 'suppUser', methods: ['POST'])]
    public function deleteUser(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $userId = $request->get('id');
        $user = $entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'User deleted successfully']);
    }


}