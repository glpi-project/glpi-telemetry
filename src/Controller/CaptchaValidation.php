<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CaptchaValidation extends AbstractController
{
    #[Route('/captcha/validation', name: 'app_captcha_validation')]
    public function validateCaptcha(Request $request, HttpClientInterface $client): JsonResponse
    {
        $token = $request->request->get('captcha_token');
        $secretKey = $this->getParameter('captcha.secret_key');
        $data = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => [
                'secret'   => $secretKey,
                'response' => $token
            ]
        ];

        $response = $client->request('POST', 'https://challenges.cloudflare.com/turnstile/v0/siteverify', $data);
        $content  = $response->getContent();

        return $this->json($response);
    }
}
