<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Telemetry;
use App\Repository\TelemetryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

class TelemetryController extends AbstractController
{
    #[Route('/telemetry', name: 'app_telemetry_post', methods: ['POST'])]
    public function post(
        #[MapRequestPayload(serializationContext:['json_decode_associative' => false])]
        ?Telemetry $telemetry,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
    ): Response {
        if ($telemetry === null) {
            return $this->json(['error' => 'Bad request'], Response::HTTP_BAD_REQUEST);
        }

        $logger->debug('Telemetry received : ' . print_r($telemetry, true));
        try {
            $entityManager->persist($telemetry);
            $entityManager->flush();
            return $this->json(['message' => 'OK']);
        } catch (\Exception $e) {
            $logger->debug('Error saving data to database : ' . $e->getMessage());
            return $this->json(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/telemetry', name: 'app_telemetry')]
    public function index(TelemetryRepository $telemetryRepository): Response
    {
        return $this->render(
            'telemetry/index.html.twig',
            [
            'controller_name' => 'controller-name',
            ]
        );
    }
}
