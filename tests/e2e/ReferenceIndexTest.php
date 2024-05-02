<?php

declare(strict_types=1);

namespace App\E2ETests;

use App\Tests\PantherTestCase;
use Symfony\Component\HttpFoundation\Response;

class ReferenceIndexTest extends PantherTestCase
{
    public function testRoutes(): Void
    {
        $client = $this->getHttpClient();
        foreach (['/reference', '/reference/map/data', '/reference/map/countries'] as $route) {
            $client->request('GET', $route);
            self::assertEquals(
                Response::HTTP_OK,
                $client->getResponse()->getStatusCode(),
                $client->getResponse()->__toString(),
            );
        }
    }

    public function testIndexPage(): void
    {
        // @TODO Test the index page behaviour:
        //  - validates that the map is displayed
        //  - validates that the list is displayed
        // Maybe a process that cleans the DB and load some fixtures could help.
        self::assertTrue(true, 'Prevent test to be marked as risky');
    }
}
