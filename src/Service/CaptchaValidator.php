<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;
class CaptchaValidator
{
    private $secretKey;
    private $client;
    public function __construct(
        HttpClientInterface $client,
        #[Autowire(param: 'captcha.secret_key')]
        string $secretKey
    ) {
        $this->client = $client;
        $this->secretKey = $secretKey;
    }

    public function validateToken(string $token): bool
    {
        try {
            $response = $this->client->request(
                'POST',
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'body' => [
                        'secret' => $this->secretKey,
                        'response' => $token,
                    ]
                ]
            );

            return $response->toArray()['success'] ?? false;

        } catch (\Throwable $e) {
            return false;
        }
    }
}
