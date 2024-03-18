<?php

declare(strict_types=1);

namespace App\E2ETests;

use App\Entity\Reference;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class ReferenceRegistrationTest extends PantherTestCase
{
    public function testSuccessfulReferenceRegistrationWillAllFields(): void
    {
        $client = static::createPantherClient();

        // Load the reference registration page
        $client->request('GET', '/reference/register');
        $this->assertSelectorTextSame('.card-title', 'Register your GLPI instance');

        // Wait for captcha validation
        $this->waitForCaptcha($client);
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
        $this->assertStringEndsWith('/reference', $client->getCurrentURL());
        $this->assertSelectorTextSame('.alert-success', 'Your reference has been added successfully');
        $client->waitFor('table.gridjs-table'); // Wait for GridJS to render table
        $firstRowSelector = 'table.gridjs-table > tbody > tr:nth-child(1)';
        $this->assertSelectorTextSame($firstRowSelector . ' > td:nth-child(1)', $name);
        $this->assertSelectorAttributeContains($firstRowSelector . ' > td:nth-child(1) > a', 'href', $url);
        $this->assertSelectorAttributeContains($firstRowSelector . ' > td:nth-child(2) > span > span', 'class', 'fi fi-fr');
        $this->assertSelectorAttributeContains($firstRowSelector . ' > td:nth-child(2) > span > span', 'title', 'France');
        $this->assertSelectorTextSame($firstRowSelector . ' > td:nth-child(3)', (string) $nbAssets);
        $this->assertSelectorTextSame($firstRowSelector . ' > td:nth-child(4)', (string) $nbHelpdesk);
        $this->assertSelectorTextSame($firstRowSelector . ' > td:nth-child(5)', date('Y-m-d'));
        $this->assertSelectorTextSame($firstRowSelector . ' > td:nth-child(6)', $comment);
    }

    public function testSuccessfulReferenceRegistrationWithOnlyName(): void
    {
        $client = static::createPantherClient();

        // Load the reference registration page
        $client->request('GET', '/reference/register');
        $this->assertSelectorTextSame('.card-title', 'Register your GLPI instance');

        // Wait for captcha validation
        $this->waitForCaptcha($client);
        $crawler = $client->refreshCrawler();

        // Submit the form
        $name = bin2hex(random_bytes(10));
        $form = $crawler->filter('[name="reference_form"]')->form();
        $form->setValues([
            'reference_form[name]' => $name,
        ]);
        $client->submit($form);

        // Validates that user is redirected to reference list page and that reference has been added
        $this->assertStringEndsWith('/reference', $client->getCurrentURL());
        $this->assertSelectorTextContains('.alert-success', 'Your reference has been added successfully');
        $client->waitFor('table.gridjs-table'); // Wait for GridJS to render table
        $firstRowSelector = 'table.gridjs-table > tbody > tr:nth-child(1)';
        $this->assertSelectorTextSame($firstRowSelector . ' > td:nth-child(1)', $name);
        $this->assertSelectorTextSame($firstRowSelector . ' > td:nth-child(2)', '');
        $this->assertSelectorTextSame($firstRowSelector . ' > td:nth-child(3)', '');
        $this->assertSelectorTextSame($firstRowSelector . ' > td:nth-child(4)', '');
        $this->assertSelectorTextSame($firstRowSelector . ' > td:nth-child(5)', date('Y-m-d'));
        $this->assertSelectorTextSame($firstRowSelector . ' > td:nth-child(6)', '');
    }

    public function testFailedReferenceRegistration(): void
    {
        $client = static::createPantherClient();

        // Load the reference registration page
        $uuid = bin2hex(random_bytes(20));
        $crawler = $client->request('GET', sprintf('/reference/register?uuid=%s', $uuid));
        $this->assertSelectorTextSame('.card-title', 'Register your GLPI instance');

        // Submit the form without waiting for captcha token to be generated
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
        $this->assertSelectorTextContains('.alert-danger', 'An error occurred while adding your reference');
        $this->assertInputValueSame('reference_form[name]', $name);
        $this->assertInputValueSame('reference_form[url]', $url);
        $this->assertSelectorExists('select[name="reference_form[country]"] > option[value="FR"][selected]');
        $this->assertInputValueSame('reference_form[phone]', $phone);
        $this->assertInputValueSame('reference_form[email]', $email);
        $this->assertInputValueSame('reference_form[referent]', $referent);
        $this->assertInputValueSame('reference_form[nb_assets]', (string) $nbAssets);
        $this->assertInputValueSame('reference_form[nb_helpdesk]', (string) $nbHelpdesk);
        $this->assertSelectorTextContains('textarea[name="reference_form[comment]"]', $comment);
        $this->assertInputValueSame('reference_form[uuid]', $uuid);
    }

    public function testRedirectFromLegacyUrl(): void
    {
        $client = static::createPantherClient();

        // Load the legacy URL
        $uuid = bin2hex(random_bytes(20));
        $crawler = $client->request('GET', sprintf('/reference?showmodal&uuid=%s', $uuid));

        // Validates that user is redirected to the form and that UUID is passed to input
        $this->assertStringEndsWith(sprintf('/reference/register?uuid=%s', $uuid), $client->getCurrentURL());
        $this->assertSelectorTextContains('.card-title', 'Register your GLPI instance');
        $this->assertInputValueSame('reference_form[uuid]', $uuid);

        // Wait for captcha validation
        $this->waitForCaptcha($client);
        $crawler = $client->refreshCrawler();

        // Submit the form
        $name = bin2hex(random_bytes(10));
        $form = $crawler->filter('[name="reference_form"]')->form();
        $form->setValues([
            'reference_form[name]' => $name,
        ]);
        $client->submit($form);

        // Validates that user is redirected to reference list page and that reference has been added
        $this->assertStringEndsWith('/reference', $client->getCurrentURL());
        $this->assertSelectorTextContains('.alert-success', 'Your reference has been added successfully');
        $client->waitFor('table.gridjs-table'); // Wait for GridJS to render table
        $firstRowSelector = 'table.gridjs-table > tbody > tr:nth-child(1)';
        $this->assertSelectorTextSame($firstRowSelector . ' > td:nth-child(1)', $name);

        // Validates that UUID is saved in DB
        /** @var \Doctrine\Persistence\ManagerRegistry $doctrine */
        $doctrine = static::getContainer()->get('doctrine');
        $repository = $doctrine->getManager()->getRepository(Reference::class);
        $reference = $repository->findOneBy(['uuid' => $uuid]);
        $this->assertInstanceOf(Reference::class, $reference);
        $this->assertEquals($reference->getUuid(), $uuid);
    }

    private function waitForCaptcha(Client $pantherClient): void
    {
        $pantherClient->waitFor('[name="reference_form"] [name="captcha_token"][value*="DUMMY"]');
    }
}
