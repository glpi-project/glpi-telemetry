<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Panther\Client as PantherClient;

abstract class PantherTestCase extends \Symfony\Component\Panther\PantherTestCase
{
    private static KernelBrowser $httpClient;

    protected function setUp(): void
    {
        self::$httpClient    = static::createClient();
        self::$pantherClient = static::createPantherClient();

        parent::setUp();
    }

    /**
     * Get the HTTP client.
     */
    protected function getHttpClient(): KernelBrowser
    {
        return self::$httpClient;
    }

    /**
     * Get the Panther client.
     */
    protected function getPantherClient(): PantherClient
    {
        if (self::$pantherClient === null) {
            throw new \RuntimeException();
        }
        return self::$pantherClient;
    }

    /**
     * Wait for the captcha from the given form to be validated.
     */
    protected function waitForCaptcha(PantherClient $pantherClient, string $formName): void
    {
        $pantherClient->waitFor(sprintf('[name="%s"] [name="captcha_token"][value*="DUMMY"]', $formName));
    }
}
