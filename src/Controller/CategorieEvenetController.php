<?php

namespace App\Controller;

use App\Entity\Categorieevent;
use App\Form\CategorieEvenetType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/categorie/post')]
class CategorieEvenetController extends AbstractController
{
    #[Route('/index', name: 'app_categorie_evenet_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $categorieEvenets = $entityManager
            ->getRepository(Categorieevent::class)
            ->findAll();

        return $this->render('categorie_evenet/index.html.twig', [
            'categorie_evenets' => $categorieEvenets,
        ]);
    }

    #[Route('/new', name: 'app_categorie_evenet_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categorieEvenet = new Categorieevent();
        $form = $this->createForm(CategorieEvenetType::class, $categorieEvenet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorieEvenet);
            $entityManager->flush();

            return $this->redirectToRoute('app_categorie_evenet_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('categorie_evenet/new.html.twig', [
            'categorie_evenet' => $categorieEvenet,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_categorie_evenet_show', methods: ['GET'])]
    public function show(Categorieevent $categorieEvenet): Response
    {
        return $this->render('categorie_evenet/show.html.twig', [
            'categorie_evenet' => $categorieEvenet,
        ]);
    }

    #[Route('/{id}/edits', name: 'app_categorie_evenet_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id, EntityManagerInterface $entityManager): Response
    {

        $categorieEvenets = $entityManager
        ->getRepository(Categorieevent::class)
        ->find($id);
        $form = $this->createForm(CategorieEvenetType::class, $categorieEvenets);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_categorie_evenet_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('categorie_evenet/edit.html.twig', [
            'categorie_evenet' => $categorieEvenets,
            'form' => $form,
        ]);
    }

    #[Route('/categorie/{id}', name: 'app_categorieevenet_delete')]
    public function delete($id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $res = $em->getRepository(Categorieevent::class)->find($id);
        $em->remove($res);
        $em->flush();
        return $this->redirectToRoute('app_categorie_evenet_index');
    }
}
