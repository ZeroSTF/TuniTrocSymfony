<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Form\CommentaireType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/c/r/u/d/commentaire')]
class CRUDCommentaireController extends AbstractController
{
    #[Route('/crudcommentaire', name: 'app_c_r_u_d_commentaire_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $commentaires = $entityManager
            ->getRepository(Commentaire::class)
            ->findAll();

        return $this->render('crud_commentaire/index.html.twig', [
            'commentaires' => $commentaires,
        ]);
    }


    #[Route('/new', name: 'app_c_r_u_d_commentaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commentaire);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_c_r_u_d_commentaire_index', [], Response::HTTP_SEE_OTHER);
        }
    
        // Vérification des données
        if ($form->isSubmitted() && !$form->isValid()) {
            $errors = $this->getFormErrors($form);
            // Gérer les erreurs
        }
    
        return $this->renderForm('crud_commentaire/new.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_c_r_u_d_commentaire_show', methods: ['GET'])]
public function show(Commentaire $commentaire): Response
{
    return $this->render('crud_commentaire/show.html.twig', [
        'commentaire' => $commentaire,
    ]);
}


    
    private function getFormErrors(FormInterface $form): array
    {
        $errors = [];
    
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
    
        foreach ($form->all() as $childForm) {
            if (!$childForm->isValid()) {
                $errors[$childForm->getName()] = $this->getFormErrors($childForm);
            }
        }
    
        return $errors;
    }
    

    #[Route('/{id}/edit', name: 'app_c_r_u_d_commentaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_c_r_u_d_commentaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('crud_commentaire/edit.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_c_r_u_d_commentaire_delete', methods: ['POST'])]
    public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commentaire->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commentaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_c_r_u_d_commentaire_index', [], Response::HTTP_SEE_OTHER);
    }
}
