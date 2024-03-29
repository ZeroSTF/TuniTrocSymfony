<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\EmailVerifier;
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


class UserJSONController extends abstractcontroller
{
    public EmailVerifier $emailVerifier;
    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

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
        $user->setEtat('PENDING');
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('tunitrocPI@gmail.com', 'TuniTroc'))
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );

        $json = $serializer->serialize($user, 'json');
        return new JsonResponse(json_decode($json));
    }


    #[Route('json/modifUser', name: 'modifierUser', methods: ['GET', 'POST'])]
    public function editUser(EntityManagerInterface $entityManager, SerializerInterface $serializer, Request $request, UserPasswordHasherInterface $userPasswordHasher): JsonResponse
    {
        $userId = $request->get('id');
        $user = $entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Update user properties
        $user->setSalt('');
        $user->setNom($request->get('nom'));
        $user->setPrenom($request->get('prenom'));
        $user->setNumTel($request->get('numTel'));
        $user->setVille($request->get('ville'));

        $entityManager->flush();

        $json = $serializer->serialize($user, 'json');
        return new JsonResponse(json_decode($json));
    }


    #[Route('json/suppUser', name: 'suppUser', methods: ['GET', 'POST'])]
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

    #[Route('json/signup', name: 'registerJson', methods:['GET', 'POST'])]
    public function signupAction(EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $userPasswordHasher, LoggerInterface $logger): JsonResponse
    {
        $user = new User();
        $user->setSalt('');
        $user->setPwd(
            $userPasswordHasher->hashPassword(
                $user,
                $request->get('pwd')
            )
        );
        $user->setPhoto("");
        $user->setDate(new \DateTime());
        $user->setEmail($request->get('email'));
        $user->setNom($request->get('nom'));
        $user->setPrenom($request->get('prenom'));
        $user->setNumTel($request->get('numTel'));
        $user->setVille($request->get('ville'));
        $user->setValeurFidelite(0);
        $user->setRole(false);
        $user->setEtat('ACTIVE');


        $entityManager->persist($user);
        $entityManager->flush();
        $user->setid(1);
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('tunitrocPI@gmail.com', 'TuniTroc'))
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
        return new JsonResponse('Compte créé.',200);
    }

    #[Route('json/signin', name: 'loginJson', methods:['GET', 'POST'])]
    public function signinAction(Request $request, EntityManagerInterface $entityManager,UserPasswordEncoderInterface $passwordEncoder, AuthenticationUtils $authenticationUtils){

        $email = $request->query->get('email');
        $password = $request->query->get('password');
        $user = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);
        if($user){
            if(password_verify($password,$user->getPwd())) {
                $serializer = new Serializer([new ObjectNormalizer()]);
                $formatted = $serializer->normalize($user);
                return new JsonResponse($formatted);
            }
            else{
                return new Response('password not found'. $user->getPwd()."####".$passwordEncoder->encodePassword(null, $password));
            }

        }
        else{
            return new Response('user not found');

        }
    }


}