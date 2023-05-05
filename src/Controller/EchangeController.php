<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Transporteur;
use App\Entity\Echange;
use App\Entity\Panier;
use App\Entity\Produit;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse; 
use TCPDF;

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
    ////FROOONT
    #[Route('/echangeF')]
    #[Route('/echangeF', name: 'app_echangeF', methods: ['GET'])]
    public function indexF(EntityManagerInterface $entityManager): Response
    {
        $transporteurs = $entityManager->getRepository(Transporteur::class)->findAll();

        $echanges = $entityManager
            ->getRepository(Echange::class)
            ->findAll();

        return $this->render('echange/indexfront.html.twig', [
            'echanges' => $echanges,
            'transporteurs' => $transporteurs,
        ]);
    }

    /**
     * @Route("/echange/generer-facture/{id}", name="generer_facture")
     */
    public function genererFacture(Echange $echange): Response
    {
        // Generate PDF invoice for the specified Echange entity
        $pdfContent = $this->generatePdfInvoice($echange);
        
        // Return the PDF file as a response
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="facture-echange-'.$echange->getId().'.pdf"',
        ]);
    }
    
    
    /**
     * Generate a PDF invoice for the specified Echange entity.
     */
  

  private function generatePdfInvoice(Echange $echange): string
{
    // Create a new TCPDF object
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set the document information
    $pdf->SetCreator('TUNITROC');
    $pdf->SetTitle('Facture n° '.$echange->getId().' - TUNITROC');
    $pdf->SetSubject('Facture n° '.$echange->getId().' - TUNITROC');
    
    // Set the page margins and add a new page
    $pdf->SetMargins(20, 20, 20);
    $pdf->AddPage();
    
    // Set the fill color and draw a rectangle to fill the page with light blue
    $pdf->SetFillColor(32, 43, 61);
    $pdf->Rect(0, 0, $pdf->getPageWidth(), $pdf->getPageHeight(), 'F');
    
    // Set the header and footer
    $pdf->setHeaderData('/logo.png', 70, 'FACTURE', 'TUNITROC', array(255,255,255), array(255,255,255));
    $pdf->setFooterData(array(0,0,0), array(255,255,255));
    
    // Write the company name and address
    $pdf->setFont('helvetica', 'B', 20);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetXY(20, 50);
    $pdf->Write(5, "TUNITROC");
    $pdf->setFont('helvetica', '', 14);
    $pdf->SetXY(20, 70);
    $pdf->Write(5, "123 Rue des Exchanges");
    $pdf->SetXY(20, 80);
    $pdf->Write(5, "75001 Paris, France");
    $pdf->Ln(20);
    
    // Add the logo
    $pdf->Image('/logo.png', 150, 20, 40, 40, 'PNG');
    $pdf->Ln(20);
    
    // Write the exchange details
    $pdf->setFont('helvetica', '', 14);
    $pdf->SetXY(20, 120);
    $pdf->Write(5, "FACTURE N° ".$echange->getId()."\n");
    $pdf->SetXY(20, 125);
    $pdf->Write(5, "Date d'émission : ".date('d/m/Y H:i:s')."\n");
    $pdf->Ln(20);
    
    
    // Write the product details
    $pdf->SetTextColor(0, 128, 0);
    $pdf->Write(5, "Produits échangés:\n\n");
    $pdf->setFont('helvetica', '', 12);
    $pdf->Write(5, "- ".$echange->getIdPanier()->getProduitR()->getNom()."\n");
    $pdf->Write(5, "- ".$echange->getIdPanier()->getProduitS()->getNom()."\n");
    $pdf->SetTextColor(0, 0, 0);
    
    // Add a message of thanks
$pdf->Ln(10);
$pdf->setFont('helvetica', '', 14);
$pdf->SetTextColor(0, 0, 0);
$pdf->Write(5, "Nous vous remercions pour votre confiance et espérons que cet échange a été satisfaisant.\n");

    // Return the generated PDF as a string
    return $pdf->Output('Facture_echange_'.$echange->getId().'.pdf', 'S');
}


   /**
 * @Route("/echange/confirmer/{id}", name="confirmer_echange")
 */ 
public function confirmerEchange($id, ManagerRegistry $doctrine)
{
    // Get the Echange entity from the database
    $echange = $doctrine->getRepository(Echange::class)->find($id);

    // Check if the Echange entity exists
    if (!$echange) {
        throw $this->createNotFoundException('Echange not found with id '.$id);
    }

    // Update the status of the Echange to "confirmed"
    $echange->setEtat('confirmed');
    $em = $doctrine->getManager();
    $em->flush();

    // Add a flash message to indicate success
    $this->addFlash('success', 'Echange with id '.$id.' has been confirmed.');

    // Redirect back to the index page
    return $this->redirectToRoute('app_echangeF');
}

    /////// BACK 
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
    /**
 * @Route("/echange/filtrer", name="app_echange_filtrer")
 */
public function filtrer(Request $request, EntityManagerInterface $entityManager): Response
{
    $transporteurs = $entityManager->getRepository(Transporteur::class)->findAll();

    $etat = $request->query->get('etat');
    $echanges = null;

    if ($etat) {
        $qb = $entityManager->createQueryBuilder();
        $qb->select('e')
            ->from(Echange::class, 'e')
            ->where('e.etat = :etat')
            ->setParameter('etat', $etat)
            ->orderBy('e.etat', 'DESC')
            ->addOrderBy('e.createdAt', 'DESC');
        $echanges = $qb->getQuery()->getResult();
    } else {
        $echanges = $entityManager
            ->getRepository(Echange::class)
            ->findBy([], ['createdAt' => 'DESC']);
    }

    return $this->render('echange/index.html.twig', [
        'echanges' => $echanges,
        'etat' => $etat,
        'transporteurs' => $transporteurs,

    ]);
}


}
