<?php

namespace App\Controller;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\User;
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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


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

    #[Route('/mesproduits/{p1}', name: 'app_mesproduit_index', methods: ['GET'])]
    public function index3(Request $request,EntityManagerInterface $entityManager, Security $security, int $p1): Response
    {
        $user = $security->getUser();
        $produits = $entityManager
            ->getRepository(Produit::class)
            ->findBy(['idUser' => $user]);
        return $this->render('produit/index3.html.twig', [
            'produits' => $produits,
            'p1' => $p1
        ]);
    }

    #[Route('/confirmer-echange/{p1}/{p2}', name: 'app_confirmer_echange', methods: ['GET', 'POST'])]
    public function confirmerEchange(SessionInterface $session, int $p1, int $p2, EntityManagerInterface $entityManager): Response
    {
        $prod1= $entityManager->getRepository(Produit::class)->find($p1);
        $prod2 = $entityManager->getRepository(Produit::class)->find($p2);
        $panier=new Panier();
        $panier->setProduitR($prod1);
        $panier->setProduitS($prod2);
        $panier->setDate(new \DateTime());
        $panier->setTransporteurb(false);
        $entityManager->persist($panier);
        $entityManager->flush();

        // Afficher la notification
        $session->getFlashBag()->add('success', 'La demande d\'échange a été envoyée avec succès !');

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

    #[Route('produit/{p1}/{p2}', name: 'app_produit_show3', methods: ['GET'])]
    public function show3(Request $request, EntityManagerInterface $entityManager, int $p1,int $p2): Response
    {


        return $this->redirectToRoute('app_confirmer_echange', [
            'p1' => $p1,
            'p2' => $p2,
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
    #[Route('/json/getAll', name: 'app_produit_JSON_rec', methods: ['GET'])]
    public function index_JSON_rec(SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $produits =  $entityManager->getRepository(Produit::class)->findAll();
        $json = $serializer->serialize($produits, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['user'],
        ]);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/json/new', name: 'create_produit_js', methods: ['GET'])]
    public function createProduitAction(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, SerializerInterface $serializer): JsonResponse
    {
        $type = $request->get('type');
        $categorie = $request->get('categorie');
        $nom = $request->get('nom');
        $libelle = $request->get('libelle');
        $prix = $request->get('prix');
        $stock = $request->get('stock');

        $produit = new Produit();
        $produit->setType($type);
        $produit->setCategorie($categorie);
        $produit->setNom($nom);
        $produit->setLibelle($libelle);
        $produit->setVille($prix);
        $produit->setPhoto(" ");
        $produit->setIdUser(78);
        $produit->setUser($entityManager->getRepository(User::class)->find(78));
        // $produit->setStock($stock);

        $errors = $validator->validate($produit);

        if (count($errors) > 0) {
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse($errorsArray, Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($produit);
        $entityManager->flush();

        $jsonContent = $serializer->serialize($produit, 'json');
        return new JsonResponse($jsonContent, Response::HTTP_CREATED, [], true);
    }

    #[Route('/json/editj', name: 'edit_produit_js', methods: ['GET'])]
    public function editProduitAction(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, SerializerInterface $serializer): JsonResponse
    {
        $produit= new Produit();
        $produit = $entityManager->getRepository(Produit::class)->find($request->get('id'));

        if (!$produit) {
            return new JsonResponse(['error' => 'Produit not found'], Response::HTTP_NOT_FOUND);
        }

        $type = $request->get('type');
        $categorie = $request->get('categorie');
        $nom = $request->get('nom');
        $libelle = $request->get('libelle');
        $prix = $request->get('prix');
        $stock = $request->get('stock');

        $produit->setType($type);
        $produit->setCategorie($categorie);
        $produit->setNom($nom);
        $produit->setLibelle($libelle);
        $produit->setVille($prix);

        $errors = $validator->validate($produit);

        if (count($errors) > 0) {
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse($errorsArray, Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        $jsonContent = $serializer->serialize($produit, 'json');
        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }

    #[Route('/json/delete', name: 'delete_produit_js', methods: ['GET'])]
    public function deleteProduitAction(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $produit = $entityManager->getRepository(Produit::class)->find($request->get('id'));
        if($produit != null) {

            $entityManager->remove($produit);
            $entityManager->flush();

            $serializer = new Serializer([new ObjectNormalizer()]);
            $formatted = $serializer->normalize("produit has been deleted successfully.");
            return new JsonResponse($formatted);
        }

        $formatted = ["error" => "Invalid produit ID."];
        return new JsonResponse($formatted);
    }


}