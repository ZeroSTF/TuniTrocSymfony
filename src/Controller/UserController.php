<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use App\Form\LoginForm;


#[Route('/user')]
class UserController extends AbstractController
{
    

    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager
            ->getRepository(User::class)
            ->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setSalt(md5(uniqid()));
            $user->setPwd($this->encodePassword($user, $user->getPwd()));
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPwd($this->encodePassword($user, $user->getPwd()));
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }


        public function showImage(User $user)
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'image/jpeg');
        $response->setContent(stream_get_contents($user->getPhoto()));
        
        return $response;
    }

    #[Route('/login', name: 'app_user_login', methods: ['GET', 'POST'])]
    public function login(Request $request, EntityManagerInterface $entityManager): Response
    {
        $error='';
        // Create a new instance of the login form
        $form = $this->createForm(LoginForm::class);

        // Handle form submission
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Get the email and password from the form
            $email = $form->get('email')->getData();
            $password = $form->get('password')->getData();

            // Find the user by email
            $userRepository = $entityManager->getRepository(User::class);
            $user = $userRepository->findOneBy(['email' => $email]);

            // Authenticate the user
            if ($user && $this->isPasswordValid($user, $password)) {
                // Generate a new token for the user
                //$user->setToken($this->generateToken());
                //$entityManager->flush();

                // Redirect to the user index page
                return $this->redirectToRoute('app_user_index');
            }

            // If the user is not authenticated, display an error message
            $this->addFlash('error', 'Invalid email or password');
            $error='Invalid email or password';
        }

        // Render the login form
        return $this->render('user/login.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);
    }

    private function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    private function isPasswordValid(User $user, string $password): bool
    {
        $encoder = new MessageDigestPasswordEncoder('sha512', true, 10);
        $saltedPassword = $password . $user->getSalt();
        $encodedPassword = $encoder->encodePassword($saltedPassword, null);
        return $encodedPassword === $user->getPwd();
    }

    private function encodePassword(User $user, string $plainPassword): string
    {
        $encoder = new MessageDigestPasswordEncoder('sha512', true, 10);
        $saltedPassword = $plainPassword . $user->getSalt();
        return $encoder->encodePassword($saltedPassword, null);
    }


}
