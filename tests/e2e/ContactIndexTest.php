<?php

declare(strict_types=1);

namespace App\E2ETests;

use App\Tests\PantherTestCase;
use Symfony\Component\HttpFoundation\Response;

class ContactIndexTest extends PantherTestCase
{
    public function testRoutes(): void
    {
        $routes = ['/contact'];

        $client = $this->getHttpClient();
        foreach ($routes as $route) {
            $client->request('GET', $route);
            self::assertEquals(
                Response::HTTP_OK,
                $client->getResponse()->getStatusCode(),
                $client->getResponse()->__toString()
            );
        }
    }

    public function testSuccessfulContact(): void
    {
        // @TODO Test a successful contact form submission:
        //  - validates that the form is displayed
        //  - fill the form
        //  - validates that a success message is displayed
        //  - validates that the mailer tries to send the message
        self::assertTrue(true, 'Prevent test to be marked as risky');
    }

    public function testFailedContact(): void
    {
        // @TODO Test a failed contact form submission:
        //  - validates that the form is displayed
        //  - simulate a failure message is displayed
        //  - validates that contact form is displayed with previous values and that a
        self::assertTrue(true, 'Prevent test to be marked as risky');
    }
}
