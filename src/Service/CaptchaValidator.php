<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CaptchaValidator
{
    /**
     * Turnstile service secret key.
     */
    private string $captchaSecretKey;

    /**
     * HTTP client.
     */
    private HttpClientInterface $client;

    public function __construct(
        HttpClientInterface $client,
        string $captchaSecretKey
    ) {
        $this->client = $client;
        $this->captchaSecretKey = $captchaSecretKey;
    }

    /**
     * Validate token against the captcha service.
     *
     * @param string $token
     * @return bool
     */
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
                        'secret' => $this->captchaSecretKey,
                        'response' => $token,
                    ]
                ]
            );

            return (bool) ($response->toArray()['success'] ?? false);

        } catch (\Throwable $e) {
            return false;
        }
    }
}
