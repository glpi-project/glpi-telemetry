<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230616093543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE glpi_reference (id INT AUTO_INCREMENT NOT NULL, reference_id INT NOT NULL, num_assets INT DEFAULT NULL, num_helpdesk INT DEFAULT NULL, UNIQUE INDEX UNIQ_A38496BA1645DEA9 (reference_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reference (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(505) DEFAULT NULL, country VARCHAR(10) DEFAULT NULL, comment VARCHAR(255) DEFAULT NULL, email VARCHAR(505) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, url VARCHAR(505) DEFAULT NULL, referent VARCHAR(505) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_displayed TINYINT(1) DEFAULT NULL, uuid VARCHAR(41) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE telemetry (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', glpi_uuid VARCHAR(41) DEFAULT NULL, glpi_version VARCHAR(25) DEFAULT NULL, glpi_default_language VARCHAR(10) DEFAULT NULL, glpi_avg_entities VARCHAR(25) DEFAULT NULL, glpi_avg_computers VARCHAR(25) DEFAULT NULL, glpi_avg_networkequipments VARCHAR(25) DEFAULT NULL, glpi_avg_tickets VARCHAR(25) DEFAULT NULL, glpi_avg_problems VARCHAR(25) DEFAULT NULL, glpi_avg_changes VARCHAR(25) DEFAULT NULL, glpi_avg_projects VARCHAR(25) DEFAULT NULL, glpi_avg_users VARCHAR(25) DEFAULT NULL, glpi_avg_groups VARCHAR(25) DEFAULT NULL, glpi_ldap_enabled TINYINT(1) DEFAULT NULL, glpi_mailcollector_enabled TINYINT(1) DEFAULT NULL, glpi_notifications VARCHAR(255) DEFAULT NULL, db_engine VARCHAR(50) DEFAULT NULL, db_version VARCHAR(50) DEFAULT NULL, db_size BIGINT DEFAULT NULL, db_log_size BIGINT DEFAULT NULL, db_sql_mode VARCHAR(255) DEFAULT NULL, web_engine VARCHAR(50) DEFAULT NULL, web_version VARCHAR(50) DEFAULT NULL, php_version VARCHAR(50) DEFAULT NULL, php_modules VARCHAR(255) DEFAULT NULL, php_config_max_execution_time INT DEFAULT NULL, php_config_memory_limit VARCHAR(10) DEFAULT NULL, php_config_post_max_size VARCHAR(10) DEFAULT NULL, php_config_safe_mode TINYINT(1) DEFAULT NULL, php_config_session VARCHAR(255) DEFAULT NULL, php_config_upload_max_filesize VARCHAR(10) DEFAULT NULL, os_family VARCHAR(50) DEFAULT NULL, os_version VARCHAR(50) DEFAULT NULL, install_mode VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE glpi_reference ADD CONSTRAINT FK_A38496BA1645DEA9 FOREIGN KEY (reference_id) REFERENCES reference (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE glpi_reference DROP FOREIGN KEY FK_A38496BA1645DEA9');
        $this->addSql('DROP TABLE glpi_reference');
        $this->addSql('DROP TABLE reference');
        $this->addSql('DROP TABLE telemetry');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
