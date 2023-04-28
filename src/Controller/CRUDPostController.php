<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\Post1Type;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use DOMDocument;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


#[Route('/c/r/u/d/post')]
class CRUDPostController extends AbstractController
{
    
    #[Route('/crudpost', name: 'app_c_r_u_d_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $postRepository->createQueryBuilder('p')->orderBy('p.date', 'DESC');
        $posts = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1), // Get the current page or default to 1 if no page is set
            2 // Number of items per page
        );
    
        return $this->render('crud_post/index.html.twig', [
            'posts' => $posts
        ]);
    }
    

    #[Route('/new', name: 'app_c_r_u_d_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PostRepository $postRepository): Response
    {
        $post = new Post();
        $form = $this->createForm(Post1Type::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $postRepository->save($post, true);

            return $this->redirectToRoute('app_c_r_u_d_post_index', [], Response::HTTP_SEE_OTHER);
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
        $form = $this->createForm(Post1Type::class, $post);
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
    #[Route('/stat_post', name: 'stat')]
    public function statAction(PostRepository $postRepository)
    {
        $posts = $postRepository->findAll();
    
        if (empty($posts)) {
            throw $this->createNotFoundException('Aucun post trouvé dans la base de données.');
        }
    
        $likes = 0;
        $dislikes = 0;
    
        foreach ($posts as $post) {
            $likes += $post->getLikes();
            $dislikes += $post->getDislikes();
        }
    
        $total = $likes + $dislikes;
    
        $data = [
            'likes' => $likes,
            'dislikes' => $dislikes,
            'total' => $total,
            'likesPercentage' => ($total > 0) ? round($likes / $total * 100, 2) : 0,
            'dislikesPercentage' => ($total > 0) ? round($dislikes / $total * 100, 2) : 0,
        ];
    
        return $this->render('crud_post/stat.html.twig', [
            'data' => $data,
        ]);
    }
    
}