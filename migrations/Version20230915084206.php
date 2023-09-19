<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230915084206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX version_idx ON telemetry (glpi_version)');
        $this->addSql('CREATE INDEX webengine_idx ON telemetry (web_engine)');
        $this->addSql('CREATE INDEX created_at_idx ON telemetry (created_at)');
        $this->addSql('CREATE INDEX os_idx ON telemetry (os_family)');
        $this->addSql('CREATE INDEX php_idx ON telemetry (php_version)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX version_idx ON telemetry');
        $this->addSql('DROP INDEX webengine_idx ON telemetry');
        $this->addSql('DROP INDEX created_at_idx ON telemetry');
        $this->addSql('DROP INDEX os_idx ON telemetry');
        $this->addSql('DROP INDEX php_idx ON telemetry');
    }
}
