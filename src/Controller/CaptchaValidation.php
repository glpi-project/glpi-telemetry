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

    public function validateCaptcha(Request $request, HttpClientInterface $client) : JsonResponse {

        $token = $request->request->get('captcha_token');
        $secretKey = $this->getParameter('captcha_secret_key');
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
        $content  = json_decode($response->getContent());

        // if($content['success'] = true) {
        //     $msg = 'token has been validated !';
        //     $result =  $this->json(['message' => $msg, 'data' => $response]);
        // }
        // if($content['success'] = false) {
        //     $error    = $content['error-codes'];
        //     $msg = 'An error occured';
        //     $result = $this->json(['error_msg' => $msg, 'error_codes' => $error]);
        // }

        return $this->json($response);

    }
}