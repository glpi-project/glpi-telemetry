<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CaptchaValidator
{
    private $client;
    private $secretKey;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;

    }

    public function validateToken(string $token, string $secretKey): bool
    {
        try {
            $response = $this->client->request(
                'POST',
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'body' => [
                        'secret' => $secretKey,
                        'response' => $token
                    ]
                ]
            );

            return $response->toArray()['success'] ?? false;

        } catch (\Throwable $e) {
            return false;
        }
    }
}
