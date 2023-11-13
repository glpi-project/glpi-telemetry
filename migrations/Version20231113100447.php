<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231113100447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX glpi_default_language_idx ON telemetry (glpi_default_language)');
        $this->addSql('CREATE INDEX glpi_uuid_idx ON telemetry (glpi_uuid)');
        $this->addSql('CREATE INDEX created_at_idx ON telemetry_glpi_plugin (created_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX created_at_idx ON telemetry_glpi_plugin');
        $this->addSql('DROP INDEX glpi_default_language_idx ON telemetry');
        $this->addSql('DROP INDEX glpi_uuid_idx ON telemetry');
    }
}
