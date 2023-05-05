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
use App\Entity\User;
use Knp\Snappy\Pdf;
use Twilio\Rest\Client;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

use DateTime;




#[Route('/reclamation')]
class ReclamationController extends AbstractController
{
    private $pdf;

    public function __construct(Pdf $pdf)
    {
        $this->pdf = $pdf;
    }

    
    #[Route('/', name: 'app_reclamation_index', methods: ['GET'])]
    #[Route('/search', name: 'app_reclamation_search', methods: ['GET'])]
public function index(Request $request, EntityManagerInterface $entityManager): Response
{
    $dateString = $request->query->get('date');
    $searchDate = null;
    $reclamations = null;
    
    if ($dateString) {
        $searchDate = DateTime::createFromFormat('Y-m-d\TH:i', $dateString);
        if ($searchDate) {
            $reclamations = $entityManager
                ->getRepository(Reclamation::class)
                ->createQueryBuilder('r')
                ->where('r.date < :date')
                ->setParameter('date', $searchDate)
                ->orderBy('r.date', 'DESC')
                ->getQuery()
                ->getResult();
        }
    } else {
        $reclamations = $entityManager
            ->getRepository(Reclamation::class)
            ->findAll();
    }

    return $this->render('reclamation/index.html.twig', [
        'reclamations' => $reclamations,
        'date' => $searchDate ? $searchDate->format('Y-m-d\TH:i') : null,
    ]);
}
#[Route('/mes_reclamations', name: 'app_mes_reclamations', methods: ['GET'])]
public function mes_reclamations(EntityManagerInterface $entityManager, Security $security): Response
{
    $userId = $this->security->getUser();
    $reclamations = $entityManager
            ->getRepository(Reclamation::class)
            ->findBy(['id_userS' => $userId]);
            return $this->render('mes_reclamations.html.twig', [
                'reclamations' => $reclamations,
            ]);

}

#[Route('/statistics', name: 'app_reclamation_statistics', methods: ['GET'])]
public function statistics(EntityManagerInterface $entityManager): Response
{
    $recsByMonth = $entityManager
        ->createQueryBuilder()
        ->select('u.date, COUNT(u) as count')
        ->from(Reclamation::class, 'u')
        ->groupBy('u.date')
        ->getQuery()
        ->getResult();

    $data = [];
    foreach ($recsByMonth as $row) {
        $data[] = [
            'label' => $row['date']->format('Y-m'),
            'value' => $row['count'],
        ];
    }

    // Sort the data by date in ascending order
    usort($data, function ($a, $b) {
        return strcmp($a['label'], $b['label']);
    });

    return $this->render('reclamation/statistics.html.twig', [
        'data' => $data,
    ]);
}

    

    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
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

            
            $reclamation->setCause($translator->trans($form->get('cause')->getData()));

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
            $entityManager->persist($reclamation);
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

    // /**
    //  * @Route("/reclamation/statistics", name="app_reclamation_statistics")
    //  */
    // public function statistics(EntityManagerInterface $entityManager): Response
    // {
    //     // Récupérez le repository de l'entité Reclamation
    //     $reclamationRepository = $entityManager->getRepository(Reclamation::class);

    //     // Comptez le nombre de réclamations en cours
    //     $reclamationsEnCours = $reclamationRepository->count(['etat' => true]);

    //     // Comptez le nombre de réclamations traitées
    //     $reclamationsTraitees = $reclamationRepository->count(['etat' => false]);

    //     // Passez les valeurs des statistiques à la vue
    //     return $this->render('reclamation/statistics.html.twig', [
    //         'reclamations_en_cours' => $reclamationsEnCours,
    //         'reclamations_traitees' => $reclamationsTraitees,
    //     ]);
    // }

   
   


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

#[Route('/{id}/notifier', name: 'app_reclamation_notifier')]
    public function notifier(int $id, EntityManagerInterface $entityManager): Response
    {
        // Get the Twilio credentials from environment variables
        $accountSid = $_ENV['TWILIO_ACCOUNT_SID'];
        $authToken = $_ENV['TWILIO_AUTH_TOKEN'];
        $twilioNumber = $_ENV['TWILIO_PHONE_NUMBER'];

        // Create a Twilio client instance
        $client = new Client($accountSid, $authToken);

        $reclamation=$this->getDoctrine()->getRepository(Reclamation::class)
        ->find($id);

        $message = 'Une reclamation contre vous de la part de  ' . $reclamation->getIdUsers()->getPrenom().' '.$reclamation->getIdUsers()->getNom();

            $phoneNumber = $reclamation->getIdUserr()->getNumTel();
                $client->messages->create("+216" .
                    $phoneNumber,
                    array(
                        'from' => $twilioNumber,
                        'body' => $message
                    )
                );

        return new Response('SMS messages sent.');
    }


}


