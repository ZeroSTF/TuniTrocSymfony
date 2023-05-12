<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Form\ReclamationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use App\Entity\User;
use Knp\Snappy\Pdf;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
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
    $userId = $security->getUser();
    $reclamations = $entityManager
            ->getRepository(Reclamation::class)
            ->findBy(['idUsers' => $userId]);
            return $this->render('reclamation/mes_reclamations.html.twig', [
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
                        $this->getParameter('photos_directory_reclamation'),
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

    #[Route('/{id}', name: 'app_reclamation_show1', methods: ['GET'])]
    public function show1(Reclamation $reclamation): Response
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
                        $this->getParameter('photos_directory_reclamation'),
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

        return $this->redirectToRoute('app_produit_index2');
    }
    //json
    #[Route('/json/getAll', name: 'app_reclamation_JSON_rec', methods: ['GET'])]
    public function index_JSON_rec(SerializerInterface $serializer): Response
    {
        $reclamations = $this->getDoctrine()->getRepository(Reclamation::class)->findAll();
        $reclamationsArray = [];
        foreach ($reclamations as $reclamation) {
            $reclamationArray = [
                'id' => $reclamation->getId(),
                'cause' => $reclamation->getCause(),
                'etat' => $reclamation->isEtat(),
                'id_userS' => $reclamation->getIdUsers()->getId(),
                'id_userR' => $reclamation->getIdUserr()->getId(),
                'photo' => $reclamation->getPhoto(),
                'date' => $reclamation->getDate()->format('Y-m-d H:i:s'),
            ];
            $reclamationsArray[] = $reclamationArray;
        }

        $json = $serializer->serialize($reclamationsArray, 'json');

        return new JsonResponse($json, 200, [], true);
    }


    #[Route('/json/new', name: 'create_reclamation_js', methods: ['GET'])]
    public function createReclamationAction(Request $request,EntityManagerInterface $entityManager, ValidatorInterface $validator, SerializerInterface $serializer): JsonResponse
    {
        // Decode the JSON data into a PHP array
        $id_userS = $request->get('idUserr');
        $id_userR = $request->get('idUsers');
        $cause = $request->get('cause');
        $etat = $request->get('etat');
        $photo = $request->get('photo');

        $userR=$this->getDoctrine()->getRepository(User::class)->find($id_userR);
        $userS=$this->getDoctrine()->getRepository(User::class)->find($id_userS);


        $reclamation = new Reclamation();
        $reclamation->setIdUsers($userS);
        $reclamation->setIdUserr($userR);
        $reclamation->setCause($cause);
        $reclamation->setEtat(0);
        $reclamation->setPhoto($photo);
        $reclamation->setDate(new \DateTime());



        // Save the entity to the database
        $entityManager->persist($reclamation);
        $entityManager->flush();

        // Return a JSON response with the serialized entity data
        $jsonContent = $serializer->serialize($reclamation, 'json',[
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['idUserr','idUsers'],
        ]);
        return new JsonResponse($jsonContent, Response::HTTP_CREATED, [], true);
    }


    #[Route('/json/edit', name: 'edit_reclamation_js', methods: ['GET'])]
    public function editReclamationAction(Request $request,EntityManagerInterface $entityManager, ValidatorInterface $validator, SerializerInterface $serializer, $id): JsonResponse
    {
// Retrieve the Reclamation entity to be updated
        $reclamation = $this->getDoctrine()->getRepository(Reclamation::class)->find($request->get('id'));
        // If the entity is not found, return a 404 response
        if (!$reclamation) {
            return new JsonResponse(['error' => 'Reclamation not found'], Response::HTTP_NOT_FOUND);
        }

// Decode the JSON data into a PHP array
        $id_userS = $request->get('idUsers');
        $id_userR = $request->get('idUserr');
        $cause = $request->get('cause');
        $photo = $request->get('photo');

        $userR = $this->getDoctrine()->getRepository(User::class)->find($id_userR);
        $userS = $this->getDoctrine()->getRepository(User::class)->find($id_userS);

// Update the Reclamation entity with the new data
        $reclamation->setIdUsers($userS);
        $reclamation->setIdUserr($userR);
        $reclamation->setCause($cause);
        $reclamation->setEtat(0);
        $reclamation->setPhoto($photo);

// Validate the updated entity using the Validator component
        $errors = $validator->validate($reclamation);

// If there are validation errors, return a JSON response with the errors
        if (count($errors) > 0) {
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse($errorsArray, Response::HTTP_BAD_REQUEST);
        }

// Save the updated entity to the database
        $entityManager->persist($reclamation);
        $entityManager->flush();

// Return a JSON response with the serialized entity data
        $jsonContent = $serializer->serialize($reclamation, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['idUserr', 'idUsers'],
        ]);
        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);

    }

    #[Route('/json/delete', name: 'app_reclamation_delete_JSON_a', methods: ['GET'])]
    public function delete_JSON_jso(Request $request,EntityManagerInterface $entityManager): Response
    {
        $id = $request->get("id");

        $reclamation = $this->getDoctrine()->getRepository(Reclamation::class)->find($id);

        if($reclamation != null) {

            $entityManager->remove($reclamation);
            $entityManager->flush();

            $serializer = new Serializer([new ObjectNormalizer()]);
            $formatted = $serializer->normalize("reclamation has been deleted successfully.");
            return new JsonResponse($formatted);
        }

        $formatted = ["error" => "Invalid reclamation ID."];
        return new JsonResponse($formatted);
    }

}


