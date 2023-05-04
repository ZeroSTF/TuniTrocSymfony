<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\VoteComment;
use App\Form\CommentaireType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commentaire')]
class CommentaireController extends AbstractController
{


    public function count($id)
    {
        $count = 0;
        $em = $this->getDoctrine()->getManager();
        $commentaire = $em->getRepository("EvenementBundle:Commentaire")->findBy(array('idevenement'=>$id));
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

    #[Route('/', name: 'app_commentaire_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {

        $commentaires = $entityManager
            ->getRepository(Commentaire::class)
            ->findAll();

        return $this->render('commentaire/index.html.twig', [
            'commentaires' => $commentaires,
        ]);
    }



    #[Route('/{id}', name: 'app_commentaire_show'   )]
    public function show(Commentaire $commentaire): Response
    {
        return $this->render('commentaire/show.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }

    #[Route('/{idc}/edit/{ide}', name: 'app_commentaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request,$idc,$ide, EntityManagerInterface $entityManager): Response
    {
        $em = $this->getDoctrine()->getManager();

        $event = $em->getRepository(Post::class)->find($ide);
        $vote = $em->getRepository(VoteComment::class)->findAll();
        $Comm = $em->getRepository(Commentaire::class)->findBy(array("idPost" => $event));
        $Commentaire = $em->getRepository(Commentaire::class)->find($idc);
        $user = $em->getRepository(User::class)->find($this->getUser());

        $form = $this->createForm(CommentaireType::class, $Commentaire);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $Commentaire->setContenu($this->filterwords($Commentaire->getContenu()));

            $entityManager->flush();

            return $this->redirectToRoute('app_post_show', ['id' => $ide], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('commentaire/edit.html.twig', [
            'post' => $event,
            'user'=>$user,
            'vote'=>$vote,
            'form' => $form,
            'comment' => $Comm,
        ]);
    }



    #[Route('/deletec/{idc}/{ide}', name: 'app_commentaire_delete')]
    public function delete($idc,$ide): Response
    {
        $em = $this->getDoctrine()->getManager();
        $res = $em->getRepository(Commentaire::class)->find($idc);
        $em->remove($res);
        $em->flush();
        return $this->redirectToRoute('app_post_show', ['id' => $ide]);
    }
}
