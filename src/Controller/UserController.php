<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


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

    #[Route('/statistics', name: 'app_user_statistics', methods: ['GET'])]
    public function statistics(EntityManagerInterface $entityManager): Response
    {
        $usersByVille = $entityManager
            ->createQueryBuilder()
            ->select('u.ville, COUNT(u) as count')
            ->from(User::class, 'u')
            ->groupBy('u.ville')
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($usersByVille as $row) {
            $data[] = [
                'label' => $row['ville'],
                'value' => $row['count'],
            ];
        }

        return $this->render('user/statistics.html.twig', [
            'data' => $data,
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

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
                $user->setPhoto("");
            }
            // get the value of the "valeurFidelite" field
            $valeurFidelite = $form->get('valeurFidelite')->getData();

// check if the value is not set or is empty
            if (!isset($valeurFidelite) || empty($valeurFidelite)) {
                // if it's not set, set it to 0
                $user->setValeurFidelite(0);
            }
            $user->setSalt("");
            $user->setPwd(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('pwd')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/profile', name: 'app_user_profile', methods: ['GET'])]
    public function profile(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager
            ->getRepository(User::class)
            ->find($id);

        $produits = $entityManager
            ->getRepository(Produit::class)
            ->findBy(['idUser' => $id]);

        $nbProduits = count($produits);

        return $this->render('front/profile.html.twig', [
            'user' => $user,
            'produits' => $produits,
            'nbProduits' => $nbProduits,
        ]);
    }


    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager
            ->getRepository(User::class)
            ->find($id);

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        $oldPhotoFilename = $user->getPhoto();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

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
            // get the value of the "valeurFidelite" field
            $valeurFidelite = $form->get('valeurFidelite')->getData();

            // check if the value is not set or is empty
            if (!isset($valeurFidelite) || empty($valeurFidelite)) {
                // if it's not set, set it to 0
                $user->setValeurFidelite(0);
            }
            $user->setSalt("");
            $user->setPwd(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('pwd')->getData()
                )
            );
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }


    #[Route('/{id}/delete', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager
            ->getRepository(User::class)
            ->find($id);
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }


}
