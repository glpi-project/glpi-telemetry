<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240212140440 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX pkey_idx ON glpi_plugin (pkey)');

    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX pkey_idx ON glpi_plugin');
    }
}
