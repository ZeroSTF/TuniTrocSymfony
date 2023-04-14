<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/c/r/u/d/post')]
class CRUDPostController extends AbstractController
{
    #[Route('/crudpost', name: 'app_c_r_u_d_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('crud_post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_c_r_u_d_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PostRepository $postRepository): Response
{
    $post = new Post();
    $form = $this->createForm(PostType::class, $post);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Validation réussie, on enregistre le post en base de données
        $postRepository->save($post, true);

        return $this->redirectToRoute('app_c_r_u_d_post_index', [], Response::HTTP_SEE_OTHER);
    }

    // Vérification des champs vides
    if ($form->isSubmitted() && (!$form->get('titre')->getData() || !$form->get('contenu')->getData())) {
        $this->addFlash('error', 'Veuillez remplir tous les champs.');

        return $this->redirectToRoute('app_c_r_u_d_post_new', [], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('crud_post/new.html.twig', [
        'post' => $post,
        'form' => $form,
    ]);
}


    #[Route('/{id}', name: 'app_c_r_u_d_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('crud_post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_c_r_u_d_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, PostRepository $postRepository): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $postRepository->save($post, true);

            return $this->redirectToRoute('app_c_r_u_d_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('crud_post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_c_r_u_d_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, PostRepository $postRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $postRepository->remove($post, true);
        }

        return $this->redirectToRoute('app_c_r_u_d_post_index', [], Response::HTTP_SEE_OTHER);
    }
}
