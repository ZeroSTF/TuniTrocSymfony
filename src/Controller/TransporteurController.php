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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Repository\TransporteurRepository;
use App\Entity\Echange;
use App\Form\EchangeType;
use App\Repository\EchangeRepository;
use Endroid\QrCode\QrCode;




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
    public function delete(ManagerRegistry $doctrine,$id): Response
    {
        $transporteur = $doctrine->getRepository(Transporteur::class)->find($id);
        $em = $doctrine->getManager();
        $em->remove($transporteur);
        $em->flush();

            return $this->redirectToRoute('app_transporteur');
    }

    #[Route('/transporteur/{id}', name: 'transporteur_show')]
    public function show($id, ManagerRegistry $doctrine, EchangeRepository $echangeRepository) : Response
    {
        $transporteur = $doctrine->getRepository(Transporteur::class)->find($id);

        $livredEchangesCount = $echangeRepository->countEchangesByState('livré');
        $totalAmount = $livredEchangesCount * 7;

        $echanges = $doctrine->getRepository(Echange::class)->findBy(['idTransporteur' => $id]);

        return $this->render('transporteur/show.html.twig', [
            'transporteur' => $transporteur,
            'echanges' => $echanges,
            'totalAmount' => $totalAmount,
            'livredEchangesCount' => $livredEchangesCount,
        ]);
    }
    #[Route('/update/{id}', name: 'update_trechange')]
    public function updateF(Request $request, ManagerRegistry $doctrine, $id): Response
    {
        $echange = $doctrine->getRepository(Echange::class)->find($id);
        $form = $this->createForm(EchangeType::class, $echange);
        $form->add('update', SubmitType::class, [
            'attr' => ['class' => 'btn btn-primary'],
            'label_html' => true,
            'label' => 'Update <i class="fas fa-save"></i>'
        ]);
                $form->handleRequest($request);


        $transporteurId = $echange->getIdTransporteur()->getId();
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $em->flush();
    
            return $this->redirectToRoute('transporteur_show', ['id' => $transporteurId]);
        }
    
       
        $transporteur = $doctrine->getRepository(Transporteur::class)->find($transporteurId);

        return $this->renderForm("transporteur/updatefront.html.twig", [
            'form' => $form,
            'transporteur' => $transporteur,
        ]);
    }
    
   

    #[Route('/maps/{id}', name: 'maps_echange')]
    public function mapAction(ManagerRegistry $doctrine,$id) {
        $echange = $doctrine->getRepository(Echange::class)->find($id);

        return $this->render("transporteur/map.html.twig", ['echange' => $echange]);
    }

///////

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
                $transporteur->setPhoto("");
            }
            $entityManager->persist($transporteur);
            $entityManager->flush();


            $twilioService = new TwilioService('ACcdb0b85a7602947372626f234b4869a2', '058b0e3b6041666ad41d18bf5be87723', '+16204558085');

            // Send SMS to the new transporteur's phone number
            $this->twilioService->sendSms($transporteur->getNumTel(), "Bienvenue chez Tunitroc Transport! Nous sommes ravis de vous compter parmi nos membres.
            N'oubliez pas de consulter notre plateforme pour trouver des opportunités de transport et faire croître votre entreprise. Votre ID: ".$transporteur->getId());
            
    
            return $this->redirectToRoute('app_transporteur', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->renderForm('transporteur/new.html.twig', [
            'transporteur' => $transporteur,
            'form' => $form,
        ]);
    }
    #[Route('/', name: 'app_transporteur', methods: ['GET'])]
    #[Route('/search', name: 'app_transporteur_search', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $entityManager): Response
    {
        $searchTerm = $request->query->get('searchTerm');
        $transporteurs = null;
        
        if ($searchTerm) {
            $transporteurs = $entityManager
                ->getRepository(Transporteur::class)
                ->createQueryBuilder('r')
                ->where('r.nom LIKE :term OR r.id = :id OR r.prenom LIKE :term')
                ->setParameter('term', '%'.$searchTerm.'%')
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
    
}
