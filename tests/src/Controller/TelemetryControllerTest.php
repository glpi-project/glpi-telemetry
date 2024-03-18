<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TelemetryControllerTest extends WebTestCase
{
    public function testTelemetryRoute(): void
    {
        $client     = static::createClient();
        $client->request('GET', '/telemetry');
        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testInvalidHeaderPost(): void
    {
        $client = static::createClient();
        $client->request('POST', '/telemetry', [], [], ['CONTENT_TYPE' => 'text/html']);
        self::assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();
        self::assertIsString($content);
        self::assertJsonStringEqualsJsonString($content, '{"error":"Bad request"}');
    }

    public function testInvalidJsonPost(): void
    {
        $client = static::createClient();
        $client->request('POST', '/telemetry', [], [], ['CONTENT_TYPE' => 'application/json'], '{"test": "test"}');
        self::assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();
        self::assertIsString($content);
        self::assertJsonStringEqualsJsonString($content, '{"error":"Bad request"}');
    }
}
