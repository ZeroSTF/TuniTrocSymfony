<?php

namespace App\Controller;
use App\Entity\Produit;
use App\Form\ProduitType;
use Dompdf\Dompdf;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\File;
use League\Csv\Writer;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;



#[Route('/produit')]
class ProduitController extends AbstractController
{
    #[Route('/', name: 'app_produit_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $produits = $entityManager
            ->getRepository(Produit::class)
            ->findAll();

        return $this->render('produit/index.html.twig', [
            'produits' => $produits,

        ]);
    }

    #[Route('/mesproduits', name: 'app_mesproduit_index', methods: ['GET'])]
    public function index3(Request $request,EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();
        $produits = $entityManager
            ->getRepository(Produit::class)
            ->findBy(['idUser' => $user]);
        return $this->render('produit/index3.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/confirmer-echange', name: 'app_confirmer_echange', methods: ['POST'])]
    public function confirmerEchange(SessionInterface $session): Response
    {
        // Effectuer les actions nécessaires pour confirmer l'échange

        // Afficher la notification
        $session->getFlashBag()->add('success', 'L\'échange a été confirmé avec succès !');

        // Rediriger vers la page index2.html.twig
        return $this->redirectToRoute('app_produit_index2');
    }


    /**
     * @Route("/search", name="search")
     */
    public function search(EntityManagerInterface $entityManager,Request $request,HttpClientInterface $httpClient)
    {
        $query = $request->query->get('query');

        $produits = $this->getDoctrine()->getRepository(Produit::class)->createQueryBuilder('p')
            ->where('p.nom LIKE :query')
            ->orWhere('p.categorie LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ->getQuery()
            ->getResult();

        $response1 = $httpClient->request('GET', 'http://api.openweathermap.org/data/2.5/weather?q=tunis&appid=5b491eb9b69dd529d5cb765278c52609&units=metric&lang=fr');
        $content1 = $response1->getContent();
        $weatherData1 = json_decode($content1, true);
        $weather1 = $weatherData1['weather'];
        return $this->render('produit/index2.html.twig', [
            'produits' => $produits,
            'weather_data' => $weatherData1,
        ]);
    }


    #[Route('/generate-csv', name:'generate_csv')]
    public function generateCsv(EntityManagerInterface $entityManager)
    {
        $produits = $entityManager->getRepository(Produit::class)->findAll();
        $csvData = [];
        $csvData[] = ['#', 'Nom du produit','Categorie'];
        /*$totalMontant = array_reduce($produit, function ($total, $sponsor) {
            return $total + $sponsor->getMontant();
        }, 0);*/

        foreach ($produits as $produit) {
            //$pourcentage = ($sponsor->getMontant() / $totalMontant) * 100;
            $csvData[] = [$produit->getId(), $produit->getNom(),$produit->getCategorie()];
        }
        //$csvData[] = ['Total', $totalMontant];
        $csv = Writer::createFromString('');
        $csv->insertAll($csvData);
        $csvContent = $csv->getContent();
        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename=" Liste des  produits.csv"');

        return $response;
    }
    #[Route('/generate-pdf', name:'generate_pdf')]
    public function generatePdf(EntityManagerInterface $entityManager)
    {

        $produits = $entityManager->getRepository(Produit::class)->findAll();
        $html = $this->renderView('produit/pdf.html.twig', [
            'produits'=>$produits
        ]);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="Liste des produits.pdf"');
        return $response;
    }

    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // handle photo file upload
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $photoFileName = uniqid().'.'.$photoFile->guessExtension();
                try {
                    $photoFile->move(
                        $this->getParameter('photos_directory_produit'),
                        $photoFileName
                    );
                    $produit->setPhoto($photoFileName);
                } catch (FileException $e) {
                    // handle exception
                }
            }

            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }




    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        $produit=$entityManager->getRepository(Produit::class)
            ->find($id);
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }
    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $produit=$entityManager->getRepository(Produit::class)
            ->find($id);
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/produit/{id}', name: 'app_produit_show3', methods: ['GET'])]
    public function show3(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $produit = $entityManager->getRepository(Produit::class)->find($id);

        return $this->render('produit/show3.html.twig', [
            'produit' => $produit,
        ]);
    }
    #[Route('/{id}/panier', name: 'app_produit_show1', methods: ['GET'])]
    public function show1(int $id, EntityManagerInterface $entityManager): Response
    {
        $produit = $entityManager->getRepository(Produit::class)->find($id);
        return $this->render('produit/show1.html.twig', [
            'produit' => $produit,
        ]);
    }



    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $produit = $entityManager->getRepository(Produit::class)->find($id);
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // handle photo file upload
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $photoFileName = uniqid().'.'.$photoFile->guessExtension();
                try {
                    $photoFile->move(
                        $this->getParameter('photos_directory_produit'),
                        $photoFileName
                    );
                    $produit->setPhoto($photoFileName);
                } catch (FileException $e) {
                    // handle exception
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }







    public function searchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $requestString = $request->get('q');
        $produits =  $em->getRepository('AppBundle:Post')->findEntitiesByString($requestString);
        if(!$produits) {
            $result['produits']['error'] = "produit Not found :( ";
        } else {
            $result['produits'] = $this->getRealEntities($produits);
        }
        return new Response(json_encode($result));
    }
    public function getRealEntities($produits){
        foreach ($produits as $produits){
            $realEntities[$produits->getId()] = [$produits->getPhoto(),$produits->getTitle()];

        }
        return $realEntities;
    }


}