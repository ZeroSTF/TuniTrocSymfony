<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Echange;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Transporteur;


class EchangeController extends AbstractController
{
    #[Route('/echange')]

    #[Route('/echange', name: 'app_echange', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $transporteurs = $entityManager->getRepository(Transporteur::class)->findAll();

        $echanges = $entityManager
            ->getRepository(Echange::class)
            ->findAll();

        return $this->render('echange/index.html.twig', [
            'echanges' => $echanges,
            'transporteurs' => $transporteurs,

        ]);
       
    }
    #[Route('/delete/{id}', name: 'delete_echange')]
    public function delete(ManagerRegistry $doctrine,$id): Response
    {
        $echange = $doctrine->getRepository(Echange::class)->find($id);
        $em = $doctrine->getManager();
        $em->remove($echange);
        $em->flush();

            return $this->redirectToRoute('app_echange');
    }
    
    #[Route('/update/{id}', name: 'update_echange')]
    public function update(Request $request,ManagerRegistry $doctrine, $id)
    {
        $entityManager = $doctrine->getManager();
        
        // Retrieve the Echange entity to be updated
        $echange = $entityManager->getRepository(Echange::class)->find($id);
        
        // Retrieve all available transporteurs
        $transporteurs = $entityManager->getRepository(Transporteur::class)->findAll();
        
        if (!$echange) {
            throw $this->createNotFoundException(
                'No echange found for id '.$id
            );
        }
        
        if ($request->isMethod('POST')) {
            // Get the selected transporteur id from the request
            $transporteurId = $request->request->get('transporteur');
            
            // Retrieve the transporteur entity by its id
            $transporteur = $entityManager->getRepository(Transporteur::class)->find($transporteurId);
            
            if (!$transporteur) {
                throw $this->createNotFoundException(
                    'No transporteur found for id '.$transporteurId
                );
            }
            
            // Update the idTransporteur attribute of the Echange entity
            $echange->setIdTransporteur($transporteur);
            $entityManager->flush();
            
            return $this->redirectToRoute('app_echange');
        }
        
        return $this->render('echange/index.html.twig', [
            'echange' => $echange,
            'transporteurs' => $transporteurs
        ]);
    }
    


}
