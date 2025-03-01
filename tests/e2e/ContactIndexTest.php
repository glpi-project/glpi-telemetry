<?php

declare(strict_types=1);

namespace App\E2ETests;

use App\Tests\PantherTestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;

class ContactIndexTest extends PantherTestCase
{
    protected function setUp(): void
    {
        // Delete all messages from the mail catcher
        HttpClient::create()->request('DELETE', $this->getMailerApiUrl('/api/v1/messages'));

        parent::setUp();
    }

    public function testRoutes(): void
    {
        $routes = ['/contact'];

        $client = $this->getHttpClient();
        foreach ($routes as $route) {
            $client->request('GET', $route);
            self::assertEquals(
                Response::HTTP_OK,
                $client->getInternalResponse()->getStatusCode(),
                $client->getInternalResponse()->__toString(),
            );
        }
    }

    public function testSuccessfulContact(): void
    {
        $client = $this->getPantherClient();

        // Load the contact page
        $client->request('GET', '/contact');
        self::assertSelectorTextSame('.card-title', 'Any question about GLPI ?');

        // Wait for captcha validation
        $this->waitForCaptcha($client, 'contact_form');
        $crawler = $client->refreshCrawler();

        // Submit the form
        $subject = sprintf('Hello from %s', bin2hex(random_bytes(10)));
        $email   = sprintf('%s@example.com', bin2hex(random_bytes(10)));
        $message = sprintf("This is really a great software. Thanks! Cheers from %s.", bin2hex(random_bytes(10)));
        $form = $crawler->filter('[name="contact_form"]')->form();
        $form->setValues([
            'contact_form[subject]' => $subject,
            'contact_form[email]'   => $email,
            'contact_form[message]' => $message,
        ]);
        $client->submit($form);

        // Validates that user is redirected to form with a success message
        self::assertStringEndsWith('/contact', $client->getCurrentURL());
        self::assertSelectorTextSame('.alert-success', 'Your message has been sent.');

        // Check that email has been sent
        /**
         * @var array{
         *          From: array{Address: string},
         *          To: array<int, array{Address: string}>,
         *          Subject: string,
         *          Text: string
         *      } $lastMessage
         */
        $lastMessage = json_decode(
            HttpClient::create()->request('GET', $this->getMailerApiUrl('/api/v1/message/latest'))->getContent(),
            true,
        );
        self::assertIsArray($lastMessage);
        self::assertEquals($email, $lastMessage['From']['Address']);
        self::assertEquals('contact@example.com', $lastMessage['To'][0]['Address'] ?? null); // configured in env
        self::assertEquals(sprintf('New message from Telemetry: %s', $subject), $lastMessage['Subject']);
        self::assertEquals($message, trim($lastMessage['Text']));
    }

    public function testFailedContact(): void
    {
        $client = $this->getPantherClient();

        // Load the contact page
        $crawler = $client->request('GET', '/contact');
        self::assertSelectorTextSame('.card-title', 'Any question about GLPI ?');

        // Remove the captcha from the form, to make it invalid
        $this->removeCaptcha($client, 'contact_form');
        $crawler = $client->refreshCrawler();

        // Submit the form
        $subject = sprintf('Hello from %s', bin2hex(random_bytes(10)));
        $email   = sprintf('%s@example.com', bin2hex(random_bytes(10)));
        $message = sprintf("This is really a great software. Thanks! Cheers from %s.", bin2hex(random_bytes(10)));
        $form = $crawler->filter('[name="contact_form"]')->form();
        $form->setValues([
            'contact_form[subject]' => $subject,
            'contact_form[email]'   => $email,
            'contact_form[message]' => $message,
        ]);
        $client->submit($form);

        // Validates that user is redirected to form with an error message
        self::assertStringEndsWith('/contact', $client->getCurrentURL());
        self::assertSelectorTextSame('.alert-danger', 'An error occurred while sending your message.');

        // Ensure that no email has been sent
        /**
         * @var array{
         *          total: int
         *      } $messages
         */
        $messages = json_decode(
            HttpClient::create()->request('GET', $this->getMailerApiUrl('/api/v1/messages'))->getContent(),
            true,
        );
        self::assertEquals(0, $messages['total']);
    }

    /**
     * Get the mailer API URL for the given endpoint.
     */
    private function getMailerApiUrl(string $endpoint): string
    {
        if (!array_key_exists('MAILER_API_URL', $_ENV) || !is_string($_ENV['MAILER_API_URL'])) {
            throw new \RuntimeException('Unable to get mailer API URL.');
        }
        return $_ENV['MAILER_API_URL'] . $endpoint;
    }
}
