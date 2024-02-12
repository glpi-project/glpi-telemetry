<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TelemetryControllerTest extends WebTestCase
{
    public function testTelemetryRoute()
    {
        $client     = static::createClient();
        $client->request('GET', '/telemetry');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testInvalidHeaderPost()
    {
        $client = static::createClient();
        $client->request('POST', '/telemetry', [], [], ['CONTENT_TYPE' => 'text/html']);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
    }

    public function testInvalidJsonPost()
    {
        $client = static::createClient();
        $client->request('POST', '/telemetry', [], [], ['CONTENT_TYPE' => 'application/json'], '{"test": "test"}');
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString($client->getResponse()->getContent(), '{"error":"Bad request"}');
    }
}
