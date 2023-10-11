<?php

namespace App\Controller;

use App\Middleware\JsonCheck;
use App\Repository\TelemetryRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
class TelemetryController extends AbstractController
{
    #[Route('/telemetry', name: 'app_telemetry_post', methods: ['POST'])]
    public function post(Request $request, TelemetryRepository $telemetryRepository, LoggerInterface $logger): Response
    {
        $logger->debug('POST request received');
        $logger->debug('POST request content: ' . $request->getContent());
        $validation = false;

        //check if the content type is json
        if ($request->headers->get('Content-Type') != 'application/json') {
            $logger->debug('POST request content type is not json');
            return new JsonResponse(['status' => 'Content-Type must be application/json'], Response::HTTP_BAD_REQUEST);
        } else {
            $logger->debug('POST request content type is json');
        }

        //Decode request content
        $data = json_decode($request->getContent());
        $logger->debug('POST request content decoded', ['data' => $data]);
        $logger->debug('POST request decoded');

        //Validate JSON
        $middleware = new JsonCheck($logger);
        $logger->debug('POST request middleware created');

        if ($middleware->validateJson($data)) {
            $logger->debug('POST request middleware validated');
            $validation = true;
        } else {
            $logger->debug('POST request middleware not validated');
            return new JsonResponse(['status' => 'JSON is not valid'], Response::HTTP_BAD_REQUEST);
        }

        if ($validation) {
            $logger->debug('POST request middleware validated');
            //Save data to database
        } else {
            $logger->debug('POST request middleware not validated');
            return new JsonResponse(['status' => 'JSON is not valid'], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['status' => 'OK']);
    }

    #[Route('/telemetry', name: 'app_telemetry')]
    public function index(TelemetryRepository $telemetryRepository): Response
    {
            return $this->render('telemetry/index.html.twig', [
                'controller_name' => 'controller-name',

            ]);

        }
}
