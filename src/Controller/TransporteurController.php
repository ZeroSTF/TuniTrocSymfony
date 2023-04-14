<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Transporteur;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\TransporteurType;




class TransporteurController extends AbstractController
{
    #[Route('/transporteur')]

    #[Route('/transporteur', name: 'app_transporteur', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        
        $transporteurs = $entityManager
            ->getRepository(Transporteur::class)
            ->findAll();

        return $this->render('transporteur/index.html.twig', [
            'transporteurs' => $transporteurs,
        ]);
       
    }
    #[Route('/delete/{id}', name: 'delete_transporteur')]
    public function delete(ManagerRegistry $doctrine,$id): Response
    {
        $transporteur = $doctrine->getRepository(Transporteur::class)->find($id);
        $em = $doctrine->getManager();
        $em->remove($transporteur);
        $em->flush();

            return $this->redirectToRoute('app_transporteur');
    }
    #[Route('/update/{id}', name: 'update_transporteur')]
    public function update(Request $request, ManagerRegistry $doctrine, $id): Response
    {
        $transporteur = $doctrine->getRepository(Transporteur::class)->find($id);
        $form = $this->createForm(TransporteurType::class, $transporteur);
        
                $form->handleRequest($request);


    
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $em->flush();
    
            return $this->redirectToRoute('app_transporteur');
        }
    
       

        return $this->renderForm("transporteur/update.html.twig", [
            'form' => $form,
            'transporteur' => $transporteur,
        ]);
    }


    #[Route('/new', name: 'add_transporteur', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $transporteur = new Transporteur();
        $form = $this->createForm(TransporteurType::class, $transporteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($transporteur);
            $entityManager->flush();

            return $this->redirectToRoute('app_transporteur', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('transporteur/new.html.twig', [
            'transporteur' => $transporteur,
            'form' => $form,
        ]);
    }

}
