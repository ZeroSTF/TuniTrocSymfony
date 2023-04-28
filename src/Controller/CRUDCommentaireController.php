<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Form\CommentaireType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CommentaireRepository;
use Knp\Component\Pager\PaginatorInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use DOMDocument;



#[Route('/c/r/u/d/commentaire')]
class CRUDCommentaireController extends AbstractController
{
    #[Route('/crudcommentaire', name: 'app_c_r_u_d_commentaire_index', methods: ['GET'])]
    public function index(CommentaireRepository $commentaireRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $commentaireRepository->createQueryBuilder('p')->orderBy('p.date', 'DESC');
        $commentaires = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1), // Get the current page or default to 1 if no page is set
            2 // Number of items per page
        );
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
    #[Route('/stat_commentaire', name: 'stat')]
    public function statAction(CommentaireRepository $commentaireRepository)
    {
        $commentaires = $commentaireRepository->findAll();
    
        if (empty($commentaires)) {
            throw $this->createNotFoundException('Aucun commentaire trouvé dans la base de données.');
        }
    
        $likes = 0;
        $dislikes = 0;
    
        foreach ($commentaires as $commentaire) {
            $likes += $commentaire->getLikes();
            $dislikes += $commentaire->getDislikes();
        }
    
        $total = $likes + $dislikes;
    
        $data = [
            'likes' => $likes,
            'dislikes' => $dislikes,
            'total' => $total,
            'likesPercentage' => ($total > 0) ? round($likes / $total * 100, 2) : 0,
            'dislikesPercentage' => ($total > 0) ? round($dislikes / $total * 100, 2) : 0,
        ];
    
        return $this->render('crud_commentaire/stat.html.twig', [
            'data' => $data,
        ]);
    }
    
    
    
        }