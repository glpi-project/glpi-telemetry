<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231129104412 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX db_engine_idx ON telemetry (db_engine)');
        $this->addSql('CREATE INDEX db_version_idx ON telemetry (db_version)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX db_engine_idx ON telemetry');
        $this->addSql('DROP INDEX db_version_idx ON telemetry');
    }
}
