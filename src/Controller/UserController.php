<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\User;
use App\Form\UserProfileType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Writer;
use Endroid\QrCode\Writer\WriterInterface;
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

        // Calculate the highest valeurFidelite value
        $maxFidelite = 0;
        foreach ($users as $user) {
            if ($user->getValeurFidelite() > $maxFidelite) {
                $maxFidelite = $user->getValeurFidelite();
            }
        }

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'maxFidelite' => $maxFidelite,
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

        $usersByEtat = $entityManager
            ->createQueryBuilder()
            ->select('u.etat, COUNT(u) as count')
            ->from(User::class, 'u')
            ->groupBy('u.etat')
            ->getQuery()
            ->getResult();

        $usersByDate = $entityManager
            ->createQueryBuilder()
            ->select('u.date, COUNT(u) as count')
            ->from(User::class, 'u')
            ->groupBy('u.date')
            ->getQuery()
            ->getResult();


        $data = [];
        foreach ($usersByVille as $row) {
            $data[] = [
                'label' => $row['ville'],
                'value' => $row['count'],
            ];
        }

        $data2 = [];
        foreach ($usersByEtat as $row) {
            $data2[] = [
                'label' => $row['etat'],
                'value' => $row['count'],
            ];
        }

        $data3 = [];
        foreach ($usersByDate as $row) {
            $data3[] = [
                'date' => $row['date']->format('m-d'),
                'count' => $row['count'],
            ];
        }


        return $this->render('user/statistics.html.twig', [
            'data' => $data,
            'data2' => $data2,
            'data3' => $data3,
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
            $user->setDate(new \DateTime());
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
        // Calculate the highest valeurFidelite value
        $users = $entityManager
            ->getRepository(User::class)
            ->findAll();
        $maxFidelite = 0;
        foreach ($users as $user) {
            if ($user->getValeurFidelite() > $maxFidelite) {
                $maxFidelite = $user->getValeurFidelite();
            }
        }
        $user = $entityManager
            ->getRepository(User::class)
            ->find($id);

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'maxFidelite' => $maxFidelite,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        $oldPhotoFilename = $user->getPhoto();
        $form = $this->createForm(UserType::class, $user, [
            'include_password_fields' => false,
        ]);
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

    #[Route('/{id}/qr-code-image', name: 'app_user_qr_code_image', methods: ['GET'])]
    public function generateQrCodeImage(int $id)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $qrCode = new QrCode('Name: ' . $user->getPrenom() . " " . $user->getNom() . "\n" . 'Email: ' . $user->getEmail());

        $writer = new PngWriter();
        $data = $writer->write($qrCode);

        return new Response($data, 200, ['Content-Type' => $qrCode->getContentType()]);
    }

    #[Route('/{id}/qr-code', name: 'app_user_qr_code', methods: ['GET'])]
    public function showQrCode(int $id)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $qrCode = new QrCode('Name: ' . $user->getPrenom() . " " . $user->getNom() . "\n" . 'Email: ' . $user->getEmail());

        $writer = new PngWriter();
        $data = $writer->write($qrCode);

        return $this->render('user/qr_code.html.twig', [
            'qr_code_data' => base64_encode($data),
        ]);
    }


}
