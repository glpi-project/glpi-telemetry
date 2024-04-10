<?php

declare(strict_types=1);

namespace App\Tests;

abstract class KernelTestCase extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
{
    protected function setUp(): void
    {
        /** @var \Doctrine\DBAL\Connection $database */
        $database = static::getContainer()->get('database_connection');
        $database->beginTransaction();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        /** @var \Doctrine\DBAL\Connection $database */
        $database = static::getContainer()->get('database_connection');
        $database->rollBack();

        parent::tearDown();
    }
}
