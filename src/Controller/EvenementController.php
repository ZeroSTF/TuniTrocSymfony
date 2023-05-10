<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\User;
use App\Form\EvenementType;
use DateInterval;
use DatePeriod;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twilio\Rest\Client;

#[Route('/evenement')]
class EvenementController extends AbstractController
{
    #[Route('/', name: 'app_evenement_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $evenements = $entityManager
            ->getRepository(Evenement::class)
            ->findAll();

        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenements,
        ]);
    }
     #[Route('/evenement/edit2', name: 'app_evenement_edit2', methods: ['POST'])]

    public function edit2(Request $request, EntityManagerInterface $entityManager)
    {
        $eventId = $request->request->get('event')['id'];
        $start = DateTime::createFromFormat('D M d Y H:i:s e+', $request->request->get('event')['start']);
        $end = DateTime::createFromFormat('D M d Y H:i:s e+', $request->request->get('event')['end']);

        $evenement = $entityManager->getRepository(Evenement::class)->find($eventId);

        if (!$evenement) {
            throw $this->createNotFoundException('The evenement does not exist');
        }

        $evenement->setDateD($start);
        $evenement->setDateF($end);

        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/statistics', name: 'app_evenement_statistics', methods: ['GET'])]
    public function statistics(EntityManagerInterface $entityManager): Response
    {

        $events = $entityManager
            ->createQueryBuilder()
            ->select('e.dateD, e.dateF')
            ->from(Evenement::class, 'e')
            ->getQuery()
            ->getResult();

        $eventsByMonth = [];
        foreach ($events as $event) {
            $date_d = $event['dateD'];
            $date_f = $event['dateF'];
            $interval = DateInterval::createFromDateString('1 month');
            $period = new DatePeriod($date_d, $interval, $date_f);

            foreach ($period as $dt) {
                $month = $dt->format('n');
                if (!isset($eventsByMonth[$month])) {
                    $eventsByMonth[$month] = 1;
                } else {
                    $eventsByMonth[$month]++;
                }
            }
        }

        $months = [
            'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
            'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
        ];

        $data3 = [];
        foreach ($eventsByMonth as $month => $count) {
            $monthName = $months[$month - 1]; // month numbers start from 1
            $data3[] = [
                'label' => $monthName,
                'value' => $count,
            ];
        }

        return $this->render('evenement/statistics.html.twig', [
            'data3' => $data3,
        ]);
    }


    #[Route('/new', name: 'app_evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);
        $evenements = $entityManager
            ->getRepository(Evenement::class)
            ->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($evenement);
            $entityManager->flush();

            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('evenement/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
            'evenements' => $evenements,
        ]);
    }

    #[Route('/{id}', name: 'app_evenement_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        $evenement = $entityManager
            ->getRepository(Evenement::class)
            ->find($id);

        $evenements = $entityManager
            ->getRepository(Evenement::class)
            ->findAll();

        return $this->render('evenement/show.html.twig', [
            'evenement' => $evenement,
            'evenements' => $evenements,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $evenement = $entityManager
            ->getRepository(Evenement::class)
            ->find($id);

        $evenements = $entityManager
            ->getRepository(Evenement::class)
            ->findAll();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
            'evenements' => $evenements
        ]);
    }

    #[Route('/{id}/delete', name: 'app_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $evenement = $entityManager
            ->getRepository(Evenement::class)
            ->find($id);
        if ($this->isCsrfTokenValid('delete' . $evenement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($evenement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/notifier', name: 'app_evenement_notifier')]
    public function notifier(int $id, EntityManagerInterface $entityManager): Response
    {
        // Get the Twilio credentials from environment variables
        $accountSid = $_ENV['TWILIO_ACCOUNT_SID'];
        $authToken = $_ENV['TWILIO_AUTH_TOKEN'];
        $twilioNumber = $_ENV['TWILIO_PHONE_NUMBER'];

        // Create a Twilio client instance
        $client = new Client($accountSid, $authToken);

        $evenement = $entityManager
            ->getRepository(Evenement::class)
            ->find($id);
        $users = $entityManager
            ->getRepository(User::class)
            ->findAll();

        // Calcul du nombre de jours restants jusqu'à l'événement
        $diff = $evenement->getDateD()->diff(new \DateTime());
        $jours_restants = $diff->format('%a');

// Construction du message
        $message = 'Il y a un événement le ' . $evenement->getDateD()->format('Y-m-d') . ': ' . $evenement->getNom() . ' (' . $jours_restants . ' jours restants)';


        foreach ($users as $user) {
            $phoneNumber = $user->getNumTel();
            if (!empty($phoneNumber && $user->getEtat() == "SUBSCRIBED")) {
                $client->messages->create("+216" .
                    $phoneNumber,
                    array(
                        'from' => $twilioNumber,
                        'body' => $message
                    )
                );
            }
        }

        $this->addFlash('success', 'Les abonnés ont été notifiés.');
        return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);

    }
}

