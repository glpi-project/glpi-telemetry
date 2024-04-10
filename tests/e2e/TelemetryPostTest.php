<?php

declare(strict_types=1);

namespace App\E2ETests;

use App\Tests\PantherTestCase;
use Symfony\Component\HttpFoundation\Response;

class TelemetryPostTest extends PantherTestCase
{
    public function testInvalidHeaderPost(): void
    {
        $client = $this->getHttpClient();
        $client->request('POST', '/telemetry', [], [], ['CONTENT_TYPE' => 'text/html']);
        self::assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();
        self::assertIsString($content);
        self::assertJsonStringEqualsJsonString($content, '{"error":"Bad request"}');
    }

    public function testInvalidJsonPost(): void
    {
        $client = $this->getHttpClient();
        $client->request('POST', '/telemetry', [], [], ['CONTENT_TYPE' => 'application/json'], '{"test": "test"}');
        self::assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();
        self::assertIsString($content);
        self::assertJsonStringEqualsJsonString($content, '{"error":"Bad request"}');
    }

    public function testSuccessfullPost(): void
    {
        // @TODO Test a valid telemetry POST:
        // - validates the response code and content
        // - validates that the corresponding entries are found in DB
        self::assertTrue(true, 'Prevent test to be marked as risky');
    }
}
