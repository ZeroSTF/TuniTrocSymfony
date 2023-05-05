<?php

namespace App\Controller;
use App\Entity\Produit;
use App\Form\ProfileFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class FrontController extends AbstractController
{
    #[Route('/front', name: 'app_front')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)
        ->findBy([], ['valeurFidelite' => 'DESC'], 12);

    return $this->render('front/index.html.twig', [
        'users' => $users,
        'controller_name' => 'FrontController',
    ]);
    }

    #[Route('/frontProduit', name: 'app_produit_index2', methods: ['GET'])]
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

    #[Route('/{id}/edit_profile', name: 'app_edit_profile', methods: ['GET', 'POST'])]
    public function edit_profile(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        $oldPhotoFilename = $user->getPhoto();
        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);
        $produits = $entityManager
            ->getRepository(Produit::class)
            ->findBy(['idUser' => $id]);

        $nbProduits = count($produits);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $photoFilename = uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                        $photoFilename
                    );
                } catch (FileException $e) {
                    // handle exception if something happens during file upload
                }

                $user->setPhoto($photoFilename);
            } else {
                $user->setPhoto($oldPhotoFilename);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_user_profile', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);

        }

        return $this->renderForm('front/edit_profile.html.twig', [
            'user' => $user,
            'registrationForm' => $form,
            'produits' => $produits,
            'nbProduits' => $nbProduits,
        ]);
    }

}
