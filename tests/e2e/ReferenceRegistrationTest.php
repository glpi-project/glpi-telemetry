<?php

declare(strict_types=1);

namespace App\E2ETests;

use App\Entity\Reference;
use App\Tests\PantherTestCase;
use Symfony\Component\Panther\Client;

class ReferenceRegistrationTest extends PantherTestCase
{
    public function testSuccessfulReferenceRegistrationWillAllFields(): void
    {
        $client = $this->getPantherClient();

        // Load the reference registration page
        $client->request('GET', '/reference/register');
        self::assertSelectorTextSame('.card-title', 'Register your GLPI instance');

        // Wait for captcha validation
        $this->waitForCaptcha($client, 'reference_form');
        $crawler = $client->refreshCrawler();

        // Submit the form
        $name       = bin2hex(random_bytes(10));
        $url        = sprintf('https://example.com/?name=%s', $name);
        $phone      = '+330123456789';
        $email      = sprintf('%s@example.com', $name);
        $referent   = sprintf('#%s', bin2hex(random_bytes(10)));
        $nbAssets   = rand(500, 250000);
        $nbHelpdesk = rand(10, 200);
        $comment    = sprintf("This is really a great software. Thanks! Cheers from %s.", bin2hex(random_bytes(10)));
        $form = $crawler->filter('[name="reference_form"]')->form();
        $form->setValues([
            'reference_form[name]'          => $name,
            'reference_form[url]'           => $url,
            'reference_form[country]'       => 'FR',
            'reference_form[phone]'         => $phone,
            'reference_form[email]'         => $email,
            'reference_form[referent]'      => $referent,
            'reference_form[nb_assets]'     => $nbAssets,
            'reference_form[nb_helpdesk]'   => $nbHelpdesk,
            'reference_form[comment]'       => $comment,
        ]);
        $client->submit($form);

        // Validates that user is redirected to reference list page and that reference has been added
        self::assertStringEndsWith('/reference', $client->getCurrentURL());
        self::assertSelectorTextSame('.alert-success', 'Your reference has been added successfully');
        $client->waitFor('table.gridjs-table'); // Wait for GridJS to render table
        $firstRowSelector = 'table.gridjs-table > tbody > tr:nth-child(1)';
        self::assertSelectorTextSame($firstRowSelector . ' > td:nth-child(1)', $name);
        self::assertSelectorAttributeContains($firstRowSelector . ' > td:nth-child(1) > a', 'href', $url);
        self::assertSelectorAttributeContains($firstRowSelector . ' > td:nth-child(2) > span > span', 'class', 'fi fi-fr');
        self::assertSelectorAttributeContains($firstRowSelector . ' > td:nth-child(2) > span > span', 'title', 'France');
        self::assertSelectorTextSame($firstRowSelector . ' > td:nth-child(3)', (string) $nbAssets);
        self::assertSelectorTextSame($firstRowSelector . ' > td:nth-child(4)', (string) $nbHelpdesk);
        self::assertSelectorTextSame($firstRowSelector . ' > td:nth-child(5)', date('Y-m-d'));
        self::assertSelectorTextSame($firstRowSelector . ' > td:nth-child(6)', $comment);
    }

    public function testSuccessfulReferenceRegistrationWithOnlyName(): void
    {
        $client = $this->getPantherClient();

        // Load the reference registration page
        $client->request('GET', '/reference/register');
        self::assertSelectorTextSame('.card-title', 'Register your GLPI instance');

        // Wait for captcha validation
        $this->waitForCaptcha($client, 'reference_form');
        $crawler = $client->refreshCrawler();

        // Submit the form
        $name = bin2hex(random_bytes(10));
        $form = $crawler->filter('[name="reference_form"]')->form();
        $form->setValues([
            'reference_form[name]' => $name,
        ]);
        $client->submit($form);

        // Validates that user is redirected to reference list page and that reference has been added
        self::assertStringEndsWith('/reference', $client->getCurrentURL());
        self::assertSelectorTextContains('.alert-success', 'Your reference has been added successfully');
        $client->waitFor('table.gridjs-table'); // Wait for GridJS to render table
        $firstRowSelector = 'table.gridjs-table > tbody > tr:nth-child(1)';
        self::assertSelectorTextSame($firstRowSelector . ' > td:nth-child(1)', $name);
        self::assertSelectorTextSame($firstRowSelector . ' > td:nth-child(2)', '');
        self::assertSelectorTextSame($firstRowSelector . ' > td:nth-child(3)', '');
        self::assertSelectorTextSame($firstRowSelector . ' > td:nth-child(4)', '');
        self::assertSelectorTextSame($firstRowSelector . ' > td:nth-child(5)', date('Y-m-d'));
        self::assertSelectorTextSame($firstRowSelector . ' > td:nth-child(6)', '');
    }

    public function testFailedReferenceRegistration(): void
    {
        $client = $this->getPantherClient();

        // Load the reference registration page
        $uuid = bin2hex(random_bytes(20));
        $crawler = $client->request('GET', sprintf('/reference/register?uuid=%s', $uuid));
        self::assertSelectorTextSame('.card-title', 'Register your GLPI instance');

        // Remove the captcha from the form, to make it invalid
        $this->removeCaptcha($client, 'reference_form');
        $crawler = $client->refreshCrawler();

        // Submit the form
        $name       = bin2hex(random_bytes(10));
        $url        = sprintf('https://example.com/?name=%s', $name);
        $phone      = '+330123456789';
        $email      = sprintf('%s@example.com', $name);
        $referent   = sprintf('#%s', bin2hex(random_bytes(10)));
        $nbAssets   = rand(500, 250000);
        $nbHelpdesk = rand(10, 200);
        $comment    = sprintf("This is really a great software. Thanks! Cheers from %s.", bin2hex(random_bytes(10)));
        $form = $crawler->filter('[name="reference_form"]')->form();
        $form->setValues([
            'reference_form[name]'          => $name,
            'reference_form[url]'           => $url,
            'reference_form[country]'       => 'FR',
            'reference_form[phone]'         => $phone,
            'reference_form[email]'         => $email,
            'reference_form[referent]'      => $referent,
            'reference_form[nb_assets]'     => $nbAssets,
            'reference_form[nb_helpdesk]'   => $nbHelpdesk,
            'reference_form[comment]'       => $comment,
        ]);
        $client->submit($form);

        // Validates that a propoer error message is displayed and form is still displayed with user values
        self::assertSelectorTextContains('.alert-danger', 'An error occurred while adding your reference');
        self::assertInputValueSame('reference_form[name]', $name);
        self::assertInputValueSame('reference_form[url]', $url);
        self::assertSelectorExists('select[name="reference_form[country]"] > option[value="FR"][selected]');
        self::assertInputValueSame('reference_form[phone]', $phone);
        self::assertInputValueSame('reference_form[email]', $email);
        self::assertInputValueSame('reference_form[referent]', $referent);
        self::assertInputValueSame('reference_form[nb_assets]', (string) $nbAssets);
        self::assertInputValueSame('reference_form[nb_helpdesk]', (string) $nbHelpdesk);
        self::assertSelectorTextContains('textarea[name="reference_form[comment]"]', $comment);
        self::assertInputValueSame('reference_form[uuid]', $uuid);
    }

    public function testRedirectFromLegacyUrl(): void
    {
        $client = $this->getPantherClient();

        // Load the legacy URL
        $uuid = bin2hex(random_bytes(20));
        $crawler = $client->request('GET', sprintf('/reference?showmodal&uuid=%s', $uuid));

        // Validates that user is redirected to the form and that UUID is passed to input
        self::assertStringEndsWith(sprintf('/reference/register?uuid=%s', $uuid), $client->getCurrentURL());
        self::assertSelectorTextContains('.card-title', 'Register your GLPI instance');
        self::assertInputValueSame('reference_form[uuid]', $uuid);

        // Wait for captcha validation
        $this->waitForCaptcha($client, 'reference_form');
        $crawler = $client->refreshCrawler();

        // Submit the form
        $name = bin2hex(random_bytes(10));
        $form = $crawler->filter('[name="reference_form"]')->form();
        $form->setValues([
            'reference_form[name]' => $name,
        ]);
        $client->submit($form);

        // Validates that user is redirected to reference list page and that reference has been added
        self::assertStringEndsWith('/reference', $client->getCurrentURL());
        self::assertSelectorTextContains('.alert-success', 'Your reference has been added successfully');
        $client->waitFor('table.gridjs-table'); // Wait for GridJS to render table
        $firstRowSelector = 'table.gridjs-table > tbody > tr:nth-child(1)';
        self::assertSelectorTextSame($firstRowSelector . ' > td:nth-child(1)', $name);

        // Validates that UUID is saved in DB
        /** @var \Doctrine\Persistence\ManagerRegistry $doctrine */
        $doctrine = static::getContainer()->get('doctrine');
        $repository = $doctrine->getManager()->getRepository(Reference::class);
        $reference = $repository->findOneBy(['uuid' => $uuid]);
        self::assertInstanceOf(Reference::class, $reference);
        self::assertEquals($reference->getUuid(), $uuid);
    }
}
