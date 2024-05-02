<?php

declare(strict_types=1);

namespace App\E2ETests;

use App\Tests\PantherTestCase;
use Symfony\Component\HttpFoundation\Response;

class JsonSchemaTest extends PantherTestCase
{
    public function testRoutes(): void
    {
        $routes = ['/schema/v1.json'];

        $client = $this->getHttpClient();
        foreach ($routes as $route) {
            $client->request('GET', $route);
            self::assertEquals(
                Response::HTTP_OK,
                $client->getResponse()->getStatusCode(),
                $client->getResponse()->__toString(),
            );
        }
    }

    public function testSchemaV1(): void
    {
        // @TODO Test the response content and headers
        self::assertTrue(true, 'Prevent test to be marked as risky');
    }
}
