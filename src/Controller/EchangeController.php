<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Transporteur;
use App\Entity\Echange;
use App\Repository\EchangeRepository;
use App\Entity\Panier;
use App\Entity\Produit;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse; 
use TCPDF;
use Symfony\Component\Serializer\SerializerInterface;


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
         $pdf->SetMargins(10, 10, 10);
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
         $pdf->SetXY(20, 5);
         $pdf->Write(5, "TUNITROC");
         $pdf->setFont('helvetica', '', 14);
         $pdf->SetXY(20, 20);
         $pdf->Write(5, "123 Rue des Exchanges");
         $pdf->SetXY(20, 30);
         $pdf->Write(5, "75001 Paris, France");
         $pdf->Ln(0);
         
         // Add the logo and a slogan
         $pdf->setFont('helvetica', '', 14);
         $pdf->SetXY(20, 50);
         $pdf->Write(5, "FACTURE N° ".$echange->getId()."\n");
         $pdf->SetXY(20, 60);
         $pdf->Write(5, "Date d'émission : ".date('d/m/Y H:i:s')."\n");
         $pdf->Ln(0);
     
         
     
         // Add an image and a caption
         $pdf->setFont('helvetica', '', 12);
         $pdf->SetXY(20, 80);
         $pdf->Write(5, "Produit échangé : ".$echange->getIdPanier()->getProduitR()->getNom());
         $pdf->Write(5, " (échangés avec)  ".$echange->getIdPanier()->getProduitS()->getNom());

     
         // Add a block of text
         $pdf->Ln(10);
         $pdf->setFont('helvetica', '', 14);
         $pdf->SetTextColor(255, 255, 255);
         $pdf->Write(5, "Pour TUNITROC, l'échange est plus qu'un simple échange. C'est un moyen de connecter les personnes à travers le monde, de partager des idées, des cultures et des expériences. C'est pourquoi nous sommes fiers de vous offrir un service d'échange simple, sécurisé et convivial qui vous permet de trouver les produits que vous cherchez et de rencontrer des gens incroyables en cours de route.\n\n");


// Add a call to action
$pdf->Ln(20);
$pdf->setFont('helvetica', 'B', 16);
$pdf->SetTextColor(0, 0, 0);
$pdf->Write(5, "Rejoignez la communauté TUNITROC dès maintenant !");
$pdf->Ln(10);
$pdf->setFont('helvetica', '', 14);
$pdf->SetTextColor(0, 0, 0);
$pdf->Write(5, "Visitez notre site web pour vous inscrire et commencez à échanger dès aujourd'hui :");
$pdf->Ln(10);
$pdf->setFont('helvetica', 'U', 14);
$pdf->SetTextColor(0, 128, 0);
$pdf->Write(5, "www.tunitroc.com");
$pdf->Ln(20);

// Add a closing message
$pdf->setFont('helvetica', '', 14);
$pdf->SetTextColor(255, 255, 255);
$pdf->Write(5, "Encore une fois, merci de nous avoir fait confiance pour cet échange. Nous espérons vous revoir bientôt sur TUNITROC !");
$pdf->Ln(20);
// Add a slogan
$pdf->setFont('helvetica', 'B', 16);
$pdf->SetTextColor(255, 255, 255);
$pdf->Write(5, "TUNITROC : L'échange gagnant-gagnant !");
$pdf->Ln(0);

// Return the generated PDF as a string
return $pdf->Output('Facture_echange_'.$echange->getId().'.pdf', 'S');}
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
/////////////////////////JSON
#[Route("/AllEchanges", name:"list")]
public function getEchanges(EntityManagerInterface $entityManager, SerializerInterface $serializer)
{
    $echanges = $entityManager
    ->getRepository(Echange::class)
    ->findAll();

        $json = $serializer->serialize($echanges, 'json', ['groups' => "echanges"]);

    return new Response($json);
}
#[Route("/Echange/{id}", name:"echange")]
public function EchangeId(EntityManagerInterface $entityManager,$id, SerializerInterface $serializer)
{
    $echange = $entityManager
    ->getRepository(Echange::class)
    ->find($id);

        $json = $serializer->serialize($echange, 'json', ['groups' => "echanges"]);

    return new Response($json);
}
#[Route("/addEchangeJSON/new", name:"addEchangeJSON")]
public function addEchangeJSON(Request $req,ManagerRegistry $doctrine,EntityManagerInterface $entityManager, SerializerInterface $serializer)
{
    $em = $doctrine->getManager();
    $echange = new Echange();
    $echange->setEtat($req->get('etat'));
    $echange->setLocation($req->get('location'));
    $echange->setIdTransporteur($req->get('IdTransporteur'));
    $echange->setIdPanier($req->get('IdPanier'));

    $em->persist($echange);
    $em->flush();

    $json = $serializer->serialize($echange, 'json', ['groups' => "echanges"]);
return new Response(json_encode($json));
}

#[Route("/updateEchangeJSON/{id}", name:"updateEchangeJSON")]
public function updateEchangeJSON(Request $req,ManagerRegistry $doctrine,EntityManagerInterface $em, SerializerInterface $serializer,$id)
{
    $echange = $em
    ->getRepository(Echange::class)
    ->find($id);
    $echange->setEtat($req->get('etat'));
    $echange->setIdTransporteur($req->get('IdTransporteur'));

    $em->flush();

    $json = $serializer->serialize($echange, 'json', ['groups' => "echanges"]);
return new Response("Echange mis à jour avec succés " . json_encode($json));
}
#[Route("/deleteEchangeJSON/{id}", name:"deleteEchangeJSON")]
public function deleteEchangeJSON(Request $req,ManagerRegistry $doctrine,EntityManagerInterface $em, SerializerInterface $serializer,$id)
{
    $echange = $em
    ->getRepository(Echange::class)
    ->find($id);
   $em->remove($echange);

    $em->flush();

    $json = $serializer->serialize($echange, 'json', ['groups' => "echanges"]);
return new Response("Echange supprimé avec succés " . json_encode($json));
}

}
