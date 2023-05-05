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
use Symfony\Contracts\Translation\TranslatorInterface;

class ReclamerController extends AbstractController
{

    #[Route('/reclamer', name: 'app_reclamer', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamerType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $photoFilename = uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                        $photoFilename
                    );
                } catch (FileException $e) {
                    // handle exception if something happens during file upload
                }

                $reclamation->setPhoto($photoFilename);
            } else {
                $reclamation->setPhoto("");
            }
            $reclamation->setCause($this->filterwords($form->get('cause')->getData()));
            $reclamation->setIdUsers($this->getDoctrine()->getRepository(User::class)->find(7));
            $reclamation->setIdUserr($this->getDoctrine()->getRepository(User::class)->find(9));
            $reclamation->setEtat(0);
            $reclamation-> setDate(new \DateTime());
            $entityManager->persist($reclamation);
            $entityManager->flush();
            $latestReclamation = $entityManager->getRepository(Reclamation::class)->findOneBy([], ['id' => 'DESC']);
            $id = $latestReclamation->getId();
            return $this->redirectToRoute('app_reclamation_notifier', ['id' => $id], Response::HTTP_SEE_OTHER);

        }
        return $this->renderForm('front/reclamer.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }
    function filterwords($text){
        $filterWords = array('fuck', 'pute','bitch');
        $filterCount = sizeof($filterWords);
        for ($i = 0; $i < $filterCount; $i++) {
            $text = preg_replace_callback('/\b' . $filterWords[$i] . '\b/i', function($matches){return str_repeat('*', strlen($matches[0]));}, $text);
        }
        return $text;
    }
}
