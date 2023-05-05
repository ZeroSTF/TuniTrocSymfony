<?php

namespace App\Controller;

use App\Entity\Fidelite;
use App\Form\FideliteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/fidelite')]
class FideliteController extends AbstractController
{
    #[Route('/', name: 'app_fidelite_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $fidelites = $entityManager
            ->getRepository(Fidelite::class)
            ->findAll();

        return $this->render('fidelite/index.html.twig', [
            'fidelites' => $fidelites,
        ]);
    }

    #[Route('/new', name: 'app_fidelite_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $fidelite = new Fidelite();
        $form = $this->createForm(FideliteType::class, $fidelite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($fidelite);
            $entityManager->flush();

            return $this->redirectToRoute('app_fidelite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('fidelite/new.html.twig', [
            'fidelite' => $fidelite,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_fidelite_show', methods: ['GET'])]
    public function show(Fidelite $fidelite): Response
    {
        return $this->render('fidelite/show.html.twig', [
            'fidelite' => $fidelite,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_fidelite_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Fidelite $fidelite, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FideliteType::class, $fidelite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_fidelite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('fidelite/edit.html.twig', [
            'fidelite' => $fidelite,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_fidelite_delete', methods: ['POST'])]
    public function delete(Request $request, Fidelite $fidelite, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$fidelite->getId(), $request->request->get('_token'))) {
            $entityManager->remove($fidelite);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_fidelite_index', [], Response::HTTP_SEE_OTHER);
    }
}
