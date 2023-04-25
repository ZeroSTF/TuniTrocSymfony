<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Form\ReclamationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;



#[Route('/reclamation')]
class ReclamationController extends AbstractController
{
    private $pdf;

    public function __construct(Pdf $pdf)
    {
        $this->pdf = $pdf;
    }

    #[Route('/', name: 'app_reclamation_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $reclamations = $entityManager
            ->getRepository(Reclamation::class)
            ->findAll();

        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }

    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reclamation);
            $entityManager->flush();

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/reclamation/statistics", name="app_reclamation_statistics")
     */
    public function statistics(EntityManagerInterface $entityManager): Response
    {
        // Récupérez le repository de l'entité Reclamation
        $reclamationRepository = $entityManager->getRepository(Reclamation::class);

        // Comptez le nombre de réclamations en cours
        $reclamationsEnCours = $reclamationRepository->count(['etat' => true]);

        // Comptez le nombre de réclamations traitées
        $reclamationsTraitees = $reclamationRepository->count(['etat' => false]);

        // Passez les valeurs des statistiques à la vue
        return $this->render('reclamation/statistics.html.twig', [
            'reclamations_en_cours' => $reclamationsEnCours,
            'reclamations_traitees' => $reclamationsTraitees,
        ]);
    }
   


    /**
 * @Route("/reclamation/pdf", name="app_reclamation_pdf", methods={"GET"})
 */
public function pdf(EntityManagerInterface $entityManager, Pdf $pdf): Response
{
    $reclamations = $entityManager
        ->getRepository(Reclamation::class)
        ->findAll();

    $html = $this->renderView('reclamation/exportPDF.html.twig', [
        'reclamations' => $reclamations,
    ]);

    return new PdfResponse(
        $pdf->getOutputFromHtml($html),
        'reclamations.pdf'
    );
}


}
