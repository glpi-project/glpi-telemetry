<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230630031034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE glpi_plugin (id INT AUTO_INCREMENT NOT NULL, pkey VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE telemetry_glpi_plugin (id INT AUTO_INCREMENT NOT NULL, glpi_plugin_id INT NOT NULL, telemetry_entry_id INT DEFAULT NULL, version VARCHAR(50) NOT NULL, INDEX IDX_212467C91C10E3FE (telemetry_entry_id), UNIQUE INDEX UNIQ_212467C9E1B3F466 (glpi_plugin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE telemetry_glpi_plugin ADD CONSTRAINT FK_212467C91C10E3FE FOREIGN KEY (telemetry_entry_id) REFERENCES telemetry (id)');
        $this->addSql('ALTER TABLE telemetry_glpi_plugin ADD CONSTRAINT FK_212467C9E1B3F466 FOREIGN KEY (glpi_plugin_id) REFERENCES telemetry_glpi_plugin (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telemetry_glpi_plugin DROP FOREIGN KEY FK_212467C91C10E3FE');
        $this->addSql('ALTER TABLE telemetry_glpi_plugin DROP FOREIGN KEY FK_212467C9E1B3F466');
        $this->addSql('DROP TABLE glpi_plugin');
        $this->addSql('DROP TABLE telemetry_glpi_plugin');
    }
}
