<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\VoteComment;
use App\Form\CommentaireType;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/post')]
class PostController extends AbstractController
{
    #[Route('/', name: 'app_post_indexB', methods: ['GET'])]
    public function indexB(EntityManagerInterface $entityManager): Response
    {
        $posts = $entityManager
            ->getRepository(Post::class)
            ->findAll();

        return $this->render('post/indexB.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/mespost', name: 'app_post_index', methods: ['GET'])]
    public function index(Request $request,EntityManagerInterface $entityManager, PaginatorInterface $paginator, Security $security): Response
    {
        $user = $security->getUser();
        $posts = $entityManager
            ->getRepository(Post::class)
            ->findBy(['idUser' => $user]);
        $articles = $paginator->paginate(
            $posts, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            1 // Nombre de résultats par page
        );        return $this->render('post/index.html.twig', [
            'posts' => $articles,
        ]);
    }

    #[Route('/postClient', name: 'app_post_indexUser', methods: ['GET'])]
    public function indexUser(EntityManagerInterface $entityManager): Response
    {
        $posts = $entityManager
            ->getRepository(Post::class)
            ->findAll();

        return $this->render('post/indexUser.html.twig', [
            'posts' => $posts,
        ]);
    }


    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user=$entityManager->getRepository(User::class)->find($this->getUser());
            $post->setIdUser($user);
            $post->setDateP(new \DateTime());
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/newB', name: 'app_post_newB', methods: ['GET', 'POST'])]
    public function newB(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user=$entityManager->getRepository(User::class)->find($this->getUser());
            $post->setIdUser($user);
            $post->setDateP(new \DateTime());
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_post_indexB', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('post/newB.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_show')]
    public function show(Request $request,$id): Response
    {

        $count = $this->count($id);

        $em = $this->getDoctrine()->getManager();

        $event = $em->getRepository(Post::class)->find($id);
        $vote = $em->getRepository(VoteComment::class)->findAll();
        $Comm = $em->getRepository(Commentaire::class)->findBy(array("idPost" => $event));
        $user = $em->getRepository(User::class)->find($this->getUser());
        $comment = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $comment->setContenu($this->filterwords($comment->getContenu()));
            $comment->setIdUser($user);
            $comment->setIdPost($event);
            $comment->setDate(new \DateTime());
            $em->persist($comment);
            $em->flush();
            return $this->redirectToRoute('app_post_show', ['id' => $id]);
        }
        return $this->render('post/show.html.twig', [
            'comment' => $Comm,
            'form' => $form->createView(),
            'post' => $event,
            'c'=>$count ,
            'user'=>$user,
            'vote'=>$vote
        ]);
    }

    #[Route('/{idPost}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request,$idPost,EntityManagerInterface $entityManager): Response
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($idPost);
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{idPost}/editB', name: 'app_post_editB', methods: ['GET', 'POST'])]
    public function editB(Request $request,$idPost,EntityManagerInterface $entityManager): Response
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($idPost);
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            return $this->redirectToRoute('app_post_indexB', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('post/editB.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Request $request, EntityManagerInterface $entityManager,$id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $res = $em->getRepository(Post::class)->find($id);

        $em->remove($res);
        $em->flush();
        return $this->redirectToRoute('app_post_indexUser');
    }

    #[Route('/deleteB/{id}', name: 'deleteB')]
    public function deleteB(Request $request, EntityManagerInterface $entityManager,$id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $res = $em->getRepository(Post::class)->find($id);

        $em->remove($res);
        $em->flush();
        return $this->redirectToRoute('app_post_indexB');
    }


    public function count($id)
    {
        $count = 0;
        $em = $this->getDoctrine()->getManager();
        $commentaire = $em->getRepository(Commentaire::class)->findBy(array('idPost'=>$id));
        foreach ($commentaire as $e){
            $count = $count + 1;
        }

        return $count;

    }
    function filterwords($text){
        $filterWords = array('fuck', 'nike', 'pute','bitch');
        $filterCount = sizeof($filterWords);
        for ($i = 0; $i < $filterCount; $i++) {
            $text = preg_replace_callback('/\b' . $filterWords[$i] . '\b/i', function($matches){return str_repeat('*', strlen($matches[0]));}, $text);
        }
        return $text;
    }
}
