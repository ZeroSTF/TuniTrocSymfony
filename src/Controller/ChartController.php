<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Echange;
use Doctrine\Persistence\ManagerRegistry;

class ChartController extends AbstractController
{
    public function __construct()
    {
        // Empty constructor
    }

 /**
     * @Route("/echange/chart")
     */
    #[Route('/echange/chart',  name: 'app_chart')]

public function chart(): Response
    {
        return $this->render('chart/chart.html.twig');
    }

/**
 * @Route("/chart-data")
 */
#[Route('/chart-data')]
public function chartData(ManagerRegistry $doctrine): JsonResponse
    {
        // Retrieve the data from the database
        $data = $doctrine
            ->getRepository(Echange::class)
            ->createQueryBuilder('e')
            ->select('e.location AS location, COUNT(e.etat) AS count')
            ->where('e.etat = :etat')
            ->setParameter('etat', 'confirmed')
            ->groupBy('e.location')
            ->getQuery()
            ->getArrayResult();

        // Extract the labels and data from the database result
        $labels = array_column($data, 'location');
        $counts = array_column($data, 'count');

        // Create the data array for the chart
        $chartData = [
            'labels' => $labels,
            'data' => $counts,
        ];

        return $this->json($chartData);
    }


}
