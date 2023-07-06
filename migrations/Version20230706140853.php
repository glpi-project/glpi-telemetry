<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230706140853 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX pkey ON glpi_plugin');
        $this->addSql('CREATE INDEX webengine_idx ON telemetry (web_engine)');
        $this->addSql('CREATE INDEX os_idx ON telemetry (os_family)');
        $this->addSql('DROP INDEX glpi_version ON telemetry');
        $this->addSql('CREATE INDEX version_idx ON telemetry (glpi_version)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX webengine_idx ON telemetry');
        $this->addSql('DROP INDEX os_idx ON telemetry');
        $this->addSql('DROP INDEX version_idx ON telemetry');
        $this->addSql('CREATE INDEX glpi_version ON telemetry (glpi_version)');
        $this->addSql('CREATE INDEX pkey ON glpi_plugin (pkey)');
    }
}
