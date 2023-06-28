<?php

namespace App\Controller;

use App\Service\PostgresConnection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestServiceController extends AbstractController
{
    #[Route('/test/service', name: 'app_test_service')]
    public function index(PostgresConnection $postgresConnection): Response
    {
        $newco = $postgresConnection->getPostGresConnection();
        $newdata = $postgresConnection->getPostgresData();

        return $this->render('test_service/index.html.twig', [
            'controller_name' => 'TestServiceController',
            'connection_state'=> $newco,
            'data' => $newdata
        ]);
    }
}
