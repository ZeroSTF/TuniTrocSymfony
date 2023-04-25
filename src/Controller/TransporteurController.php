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
use App\Service\TwilioService;




class TransporteurController extends AbstractController
{    private $twilioService;
    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

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
    #[Route('/tr/delete/{id}', name: 'delete_transporteur')]
    public function delete(ManagerRegistry $doctrine,$id): Response
    {
        $transporteur = $doctrine->getRepository(Transporteur::class)->find($id);
        $em = $doctrine->getManager();
        $em->remove($transporteur);
        $em->flush();

            return $this->redirectToRoute('app_transporteur');
    }



    #[Route('/tr/update/{id}', name: 'update_transporteur')]
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

    #[Route('/new', name: 'add_transporteur')]

    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $transporteur = new Transporteur();
        $form = $this->createForm(TransporteurType::class, $transporteur);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($transporteur);
            $entityManager->flush();

            
            $twilioService = new TwilioService('AC0ce74c8f65b20a8e927d7f39a8abe10f', '3d0ff5057e0962b0e69eec1afb2637bd', '+16204558085');

            // Send SMS to the new transporteur's phone number
            $this->twilioService->sendSms($transporteur->getNumTel(), 'Bienvenue chez Tunitroc Transport! Nous sommes ravis de vous compter parmi nos membres.
             N oubliez pas de consulter notre plateforme pour trouver des opportunités de transport et faire croître votre entreprise. ');
    
            return $this->redirectToRoute('app_transporteur', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->renderForm('transporteur/new.html.twig', [
            'transporteur' => $transporteur,
            'form' => $form,
        ]);
    }
    

}
