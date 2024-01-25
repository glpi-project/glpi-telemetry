<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240125084731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1052Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1052Platform'."
        );

        $this->addSql('CREATE TABLE telemetry_glpi_plugin (
          id INT AUTO_INCREMENT NOT NULL,
          telemetry_entry_id INT DEFAULT NULL,
          glpi_plugin_id INT DEFAULT NULL,
          version VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX IDX_212467C9E1B3F466 (glpi_plugin_id),
          INDEX created_at_idx (created_at),
          INDEX IDX_212467C91C10E3FE (telemetry_entry_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1052Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1052Platform'."
        );

        $this->addSql('CREATE TABLE telemetry (
          id INT AUTO_INCREMENT NOT NULL,
          created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          glpi_uuid VARCHAR(41) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          glpi_version VARCHAR(25) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          glpi_default_language VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          glpi_avg_entities VARCHAR(25) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          glpi_avg_computers VARCHAR(25) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          glpi_avg_networkequipments VARCHAR(25) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          glpi_avg_tickets VARCHAR(25) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          glpi_avg_problems VARCHAR(25) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          glpi_avg_changes VARCHAR(25) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          glpi_avg_projects VARCHAR(25) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          glpi_avg_users VARCHAR(25) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          glpi_avg_groups VARCHAR(25) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          glpi_ldap_enabled TINYINT(1) DEFAULT NULL,
          glpi_mailcollector_enabled TINYINT(1) DEFAULT NULL,
          glpi_notifications VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          db_engine VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          db_version VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          db_size BIGINT DEFAULT NULL,
          db_log_size BIGINT DEFAULT NULL,
          db_sql_mode VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          web_engine VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          web_version VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          php_version VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          php_modules TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          php_config_max_execution_time INT DEFAULT NULL,
          php_config_memory_limit VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          php_config_post_max_size VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          php_config_safe_mode TINYINT(1) DEFAULT NULL,
          php_config_session VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          php_config_upload_max_filesize VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          os_family VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          os_distribution VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          os_version VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          install_mode VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX os_idx (os_family),
          INDEX version_idx (glpi_version),
          INDEX php_idx (php_version),
          INDEX webengine_idx (web_engine),
          INDEX glpi_default_language_idx (glpi_default_language),
          INDEX created_at_idx (created_at),
          INDEX glpi_uuid_idx (glpi_uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1052Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1052Platform'."
        );

        $this->addSql('CREATE TABLE glpi_plugin (
          id INT AUTO_INCREMENT NOT NULL,
          pkey VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1052Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1052Platform'."
        );

        $this->addSql('CREATE TABLE reference (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(505) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          country VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          comment TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          email VARCHAR(505) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          phone VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          url VARCHAR(505) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          referent VARCHAR(505) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          is_displayed TINYINT(1) DEFAULT NULL,
          uuid VARCHAR(41) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1052Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1052Platform'."
        );

        $this->addSql('CREATE TABLE glpi_reference (
          id INT AUTO_INCREMENT NOT NULL,
          reference_id INT NOT NULL,
          num_assets INT DEFAULT NULL,
          num_helpdesk INT DEFAULT NULL,
          created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          UNIQUE INDEX UNIQ_A38496BA1645DEA9 (reference_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1052Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1052Platform'."
        );

        $this->addSql('DROP TABLE telemetry_glpi_plugin');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1052Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1052Platform'."
        );

        $this->addSql('DROP TABLE telemetry');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1052Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1052Platform'."
        );

        $this->addSql('DROP TABLE glpi_plugin');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1052Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1052Platform'."
        );

        $this->addSql('DROP TABLE reference');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1052Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1052Platform'."
        );

        $this->addSql('DROP TABLE glpi_reference');
    }
}
