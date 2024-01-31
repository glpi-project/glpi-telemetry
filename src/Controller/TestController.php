<?php

namespace App\Controller;

use App\Entity\Telemetry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test', methods: ['POST'])]
    public function test(
        #[MapRequestPayload]
        Telemetry $telemetry,
    ): Response
    {

        return new Response(
            print_r($telemetry, true),
            Response::HTTP_OK
        );
    }
}