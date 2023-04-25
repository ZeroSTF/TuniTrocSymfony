<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Reclamation;
use App\Entity\User;
use App\Form\ReclamerType;
use Doctrine\ORM\EntityManagerInterface;

class ReclamerController extends AbstractController
{

    #[Route('/reclamer', name: 'app_reclamer', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamerType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reclamation->setIdUsers($this->getDoctrine()->getRepository(User::class)->find(7));
            $reclamation->setIdUserr($this->getDoctrine()->getRepository(User::class)->find(9));
            $reclamation->setEtat(0);
            $entityManager->persist($reclamation);
            $entityManager->flush();

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('front/reclamer.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }
}
