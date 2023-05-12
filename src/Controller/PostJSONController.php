<?php
namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Psr\Log\LoggerInterface;


class PostJSONController extends abstractcontroller
{
    #[Route('json/afficherPost', name: 'afficherPostJSON', methods: ['GET', 'POST'])]
    public function indexPost(EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $Posts = $entityManager
            ->getRepository(Post::class)
            ->findAll();
        $reclamationsArray = [];
        foreach ($Posts as $post) {
            $reclamationArray = [
                'idPost' => $post->getIdPost(),
                'description' => $post->getDescription(),
                'dateP' => $post->getDateP(),
                'idUser' => $post->getIdUser()->getId(),
            ];
            $reclamationsArray[] = $reclamationArray;
        }

        $json = $serializer->serialize($reclamationsArray, 'json');
        return new JsonResponse(json_decode($json));
    }

    #[Route('json/ajouterPost', name: 'ajouterPostJSON', methods: ['GET', 'POST'])]
    public function addPost(EntityManagerInterface $entityManager, SerializerInterface $serializer, Request $request, UserPasswordHasherInterface $userPasswordHasher): JsonResponse
    {
        $user = new Post();
        // Set user properties
        $user->setDescription($request->get('description'));
        $user->setDateP(new \DateTime());
        $user->setImage("");
        $UserP = $entityManager
            ->getRepository(User::class)
            ->find($request->get('idUser'));
        $user->setIdUser($UserP);
        $categorieP=$entityManager
            ->getRepository(User::class)
            ->find(3);
        $user->setIdCategorie($categorieP);

        $entityManager->persist($user);
        $entityManager->flush();

        $json = $serializer->serialize($user, 'json');
        return new JsonResponse(json_decode($json));
    }


    #[Route('json/modifPost', name: 'modifierPost', methods: ['GET', 'POST'])]
    public function editPost(EntityManagerInterface $entityManager, SerializerInterface $serializer, Request $request, UserPasswordHasherInterface $userPasswordHasher): JsonResponse
    {
        $userId = $request->get('id');
        $user = $entityManager->getRepository(Post::class)->find($userId);

        if (!$user) {
            return new JsonResponse(['message' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }

        // Update post properties
        $user->setDescription($request->get('description'));
        $entityManager->flush();

        $json = $serializer->serialize($user, 'json');
        return new JsonResponse(json_decode($json));
    }

    #[Route('json/suppPost', name: 'suppPost', methods: ['GET', 'POST'])]
    public function deletePost(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $Id = $request->get('id');
        $post = $entityManager->getRepository(Post::class)->find($Id);
        if (!$post) {
            return new JsonResponse(['message' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $entityManager->remove($post);
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'An error occurred while deleting the post'], Response::HTTP_OK);
        }


        return new JsonResponse(['message' => 'Post deleted successfully'], Response::HTTP_OK);
    }
}