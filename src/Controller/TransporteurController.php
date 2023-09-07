<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Reclamation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Transporteur;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\TransporteurType;
use App\Service\TwilioService;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Repository\TransporteurRepository;
use App\Entity\Echange;
use App\Form\EchangeType;
use App\Repository\EchangeRepository;
use Endroid\QrCode\QrCode;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TransporteurController extends AbstractController
{
    private $twilioService;
    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    #[Route('/transporteur', name: 'app_transporteur', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $transporteurs = $entityManager
            ->getRepository(Transporteur::class)
            ->findAll();

        return $this->render('transporteur/index.html.twig', [
            'transporteurs' => $transporteurs,
        ]);
        //// FROOONT
    }

    #[Route('/transporteurF', name: 'app_transporteurF')]
    public function indexF(): Response
    {
        return $this->render('transporteur/indexfront.html.twig', [
            'controller_name' => 'TransportController',
        ]);
    }
    #[Route('/tr/delete/{id}', name: 'delete_transporteur')]
    public function delete(ManagerRegistry $doctrine, $id): Response
    {
        $transporteur = $doctrine
            ->getRepository(Transporteur::class)
            ->find($id);
        $em = $doctrine->getManager();
        $em->remove($transporteur);
        $em->flush();

        return $this->redirectToRoute('app_transporteur');
    }

    #[Route('/transporteur/{id}', name: 'transporteur_show')]
    public function show(
        $id,
        ManagerRegistry $doctrine,
        EchangeRepository $echangeRepository
    ): Response {
        $transporteur = $doctrine
            ->getRepository(Transporteur::class)
            ->find($id);

        $echanges = $doctrine
            ->getRepository(Echange::class)
            ->findBy(['idTransporteur' => $id]);

        return $this->render('transporteur/show.html.twig', [
            'transporteur' => $transporteur,
            'echanges' => $echanges,
        ]);
    }
    #[Route('/update/{id}', name: 'update_trechange')]
    public function updateF(
        Request $request,
        ManagerRegistry $doctrine,
        $id
    ): Response {
        $echange = $doctrine->getRepository(Echange::class)->find($id);
        $form = $this->createForm(EchangeType::class, $echange);
        $form->add('update', SubmitType::class, [
            'attr' => ['class' => 'btn btn-primary'],
            'label_html' => true,
            'label' => 'Update <i class="fas fa-save"></i>',
        ]);
        $form->handleRequest($request);

        $transporteurId = $echange->getIdTransporteur()->getId();

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $em->flush();

            return $this->redirectToRoute('transporteur_show', [
                'id' => $transporteurId,
            ]);
        }

        $transporteur = $doctrine
            ->getRepository(Transporteur::class)
            ->find($transporteurId);

        return $this->renderForm('transporteur/updatefront.html.twig', [
            'form' => $form,
            'transporteur' => $transporteur,
        ]);
    }

    #[Route('/maps/{id}', name: 'maps_echange')]
    public function mapAction(ManagerRegistry $doctrine, $id)
    {
        $echange = $doctrine->getRepository(Echange::class)->find($id);

        return $this->render('transporteur/map.html.twig', [
            'echange' => $echange,
        ]);
    }

    ///////

    #[Route('/tr/update/{id}', name: 'update_transporteur')]
    public function update(
        Request $request,
        ManagerRegistry $doctrine,
        $id
    ): Response {
        $transporteur = $doctrine
            ->getRepository(Transporteur::class)
            ->find($id);
        $form = $this->createForm(TransporteurType::class, $transporteur);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $em->flush();

            return $this->redirectToRoute('app_transporteur');
        }

        return $this->renderForm('transporteur/update.html.twig', [
            'form' => $form,
            'transporteur' => $transporteur,
        ]);
    }

    #[Route('/new', name: 'add_transporteur', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $transporteur = new Transporteur();
        $form = $this->createForm(TransporteurType::class, $transporteur);
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

                $transporteur->setPhoto($photoFilename);
            } else {
                $transporteur->setPhoto('');
            }
            $entityManager->persist($transporteur);
            $entityManager->flush();

            $twilioService = new TwilioService(
                'ACcdb0b85a7602947372626f234b4869a2',
                '058b0e3b6041666ad41d18bf5be87723',
                '+16204558085'
            );

            // Send SMS to the new transporteur's phone number
            $this->twilioService->sendSms(
                $transporteur->getNumTel(),
                "Bienvenue chez Tunitroc Transport! Nous sommes ravis de vous compter parmi nos membres.
            N'oubliez pas de consulter notre plateforme pour trouver des opportunités de transport et faire croître votre entreprise. Votre ID: " .
                $transporteur->getId()
            );

            return $this->redirectToRoute(
                'app_transporteur',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm('transporteur/new.html.twig', [
            'transporteur' => $transporteur,
            'form' => $form,
        ]);
    }

    #[Route('/search', name: 'app_transporteur_search', methods: ['GET'])]
    public function search(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $searchTerm = $request->query->get('searchTerm');
        $transporteurs = null;

        if ($searchTerm) {
            $transporteurs = $entityManager
                ->getRepository(Transporteur::class)
                ->createQueryBuilder('r')
                ->where('r.nom LIKE :term OR r.id = :id OR r.prenom LIKE :term')
                ->setParameter('term', '%' . $searchTerm . '%')
                ->setParameter('id', $searchTerm)
                ->orderBy('r.nom', 'DESC')
                ->getQuery()
                ->getResult();
        } else {
            $transporteurs = $entityManager
                ->getRepository(Transporteur::class)
                ->findAll();
        }

        return $this->render('transporteur/index.html.twig', [
            'transporteurs' => $transporteurs,
            'searchTerm' => $searchTerm,
        ]);
    }

    /////////////////////////JSON
    #[Route('/AllTransporteurs', name: 'list')]
    public function getTransporteur(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer
    ) {
        $transporteurs = $entityManager
            ->getRepository(Transporteur::class)
            ->findAll();

        $json = $serializer->serialize($transporteurs, 'json', [
            'groups' => 'transporteurs',
        ]);

        return new Response($json);
    }
    #[Route('/getTransporteur/{id}', name: 'transporteur')]
    public function EchangeId(
        EntityManagerInterface $entityManager,
                               $id,
        SerializerInterface $serializer
    ) {
        $echange = $entityManager
            ->getRepository(Transporteur::class)
            ->find($id);

        $json = $serializer->serialize($echange, 'json', [
            'groups' => 'transporteurs',
        ]);

        return new Response($json);
    }
    #[Route('/addTransporteurJSON/new', name: 'addTransporteurJSON')]
    public function addTransporteurJSON(
        Request $req,
        ManagerRegistry $doctrine,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer
    ) {
        $em = $doctrine->getManager();
        $transporteur = new Transporteur();
        $transporteur->setNom($req->get('nom'));
        $transporteur->setPrenom($req->get('prenom'));
        $transporteur->setNumTel($req->get('numTel'));
        $transporteur->setPhoto($req->get('photo'));

        $em->persist($transporteur);
        $em->flush();

        $json = $serializer->serialize($transporteur, 'json', [
            'groups' => 'transporteurs',
        ]);
        return new Response(
            'Transporteur ajouté avec succées' . json_encode($json)
        );
    }

    #[Route('/updateTransporteurJSON/{id}', name: 'updateTransporteurJSON')]
    public function updateTransporteurJSON(
        Request $req,
        ManagerRegistry $doctrine,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        $id
    ) {
        $transporteur = $em->getRepository(Transporteur::class)->find($id);
        $transporteur->setNom($req->get('nom'));
        $transporteur->setPrenom($req->get('prenom'));
        $transporteur->setNumTel($req->get('numTel'));
        $transporteur->setPhoto($req->get('photo'));

        $em->flush();

        $json = $serializer->serialize($transporteur, 'json', [
            'groups' => 'transporteurs',
        ]);
        return new Response(
            'Transporteur mis à jour avec succés ' . json_encode($json)
        );
    }
    #[Route('/deleteTransporteurJSON/{id}', name: 'deleteTransporteurJSON')]
    public function deleteTransporteurJSON(
        Request $req,
        ManagerRegistry $doctrine,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        $id
    ) {
        $transporteur = $em->getRepository(Transporteur::class)->find($id);
        $em->remove($transporteur);

        $em->flush();

        $json = $serializer->serialize($transporteur, 'json', [
            'groups' => 'transporteurs',
        ]);
        return new Response(
            'transporteur supprimé avec succés ' . json_encode($json)
        );
    }

    #[Route('/transporteur/json/getAll', name: 'app_transporteur_JSON_rec', methods: ['GET'])]
    public function index_JSON_rec(SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $transporteurs = $entityManager->getRepository(Transporteur::class)->findAll();
        $json = $serializer->serialize($transporteurs, 'json');

        return new JsonResponse(json_decode($json));
    }

    #[Route('/transporteur/json/new', name: 'create_transporteur_js', methods: ['GET'])]
    public function createTransporteurAction(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, SerializerInterface $serializer): JsonResponse
    {
        // Decode the JSON data into a PHP array
        $nom = $request->get('nom');
        $prenom = $request->get('prenom');
        $numTel = $request->get('numTel');

        $transporteur = new Transporteur();
        $transporteur->setNom($nom);
        $transporteur->setPrenom($prenom);
        $transporteur->setNumTel($numTel);
        $transporteur->setPhoto("");



        // Save the entity to the database
        $entityManager->persist($transporteur);
        $entityManager->flush();

        // Return a JSON response with the serialized entity data
        $jsonContent = $serializer->serialize($transporteur, 'json',[
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['id'],
        ]);
        return new JsonResponse($jsonContent, Response::HTTP_CREATED, [], true);
    }

    #[Route('/transporteur/json/edit', name: 'edit_transporteur_js', methods: ['GET'])]
    public function editTransporteurAction(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, SerializerInterface $serializer): JsonResponse
    {
        // Decode the JSON data into a PHP array
        $transporteur = $this->getDoctrine()->getRepository(Transporteur::class)->find($request->get('id'));
        // If the entity is not found, return a 404 response
        if (!$transporteur) {
            return new JsonResponse(['error' => 'TRa not found'], Response::HTTP_NOT_FOUND);
        }
        $nom = $request->get('nom');
        $prenom = $request->get('prenom');
        $numTel = $request->get('numTel');


        // Update the entity with the new data
        $transporteur->setNom($nom);
        $transporteur->setPrenom($prenom);
        $transporteur->setNumTel($numTel);

        // Validate the entity using the Validator component
        $errors = $validator->validate($transporteur);
        if (count($errors) > 0) {
            return new JsonResponse((string) $errors, Response::HTTP_BAD_REQUEST, ['Content-Type' => 'application/json']);
        }

        // Save the updated entity to the database
        $entityManager->persist($transporteur);
        $entityManager->flush();

        // Return a JSON response with the serialized entity data
        $jsonContent = $serializer->serialize($transporteur, 'json');
        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }

    /*
      $reclamation = $this->getDoctrine()->getRepository(Reclamation::class)->find($request->get('id'));
        // If the entity is not found, return a 404 response
        if (!$reclamation) {
            return new JsonResponse(['error' => 'Reclamation not found'], Response::HTTP_NOT_FOUND);
        }
     */

    #[Route('/transporteur/json/delete', name: 'delete_transporteur_js', methods: ['GET'])]
    public function deletetransporteurAction(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $produit = $entityManager->getRepository(Transporteur::class)->find($request->get('id'));
        if($produit != null) {

            $entityManager->remove($produit);
            $entityManager->flush();

            $serializer = new Serializer([new ObjectNormalizer()]);
            $formatted = $serializer->normalize("transporteur has been deleted successfully.");
            return new JsonResponse($formatted);
        }

        $formatted = ["error" => "Invalid transporteur   ID."];
        return new JsonResponse($formatted);
    }
}
