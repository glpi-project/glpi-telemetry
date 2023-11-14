<?php

namespace App\Entity;

use App\Repository\TelemetryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TelemetryRepository::class)]
#[ORM\Index(name:"version_idx", columns:["glpi_version"])]
#[ORM\Index(name:"webengine_idx", columns:["web_engine"])]
#[ORM\Index(name:"created_at_idx", columns:["created_at"])]
#[ORM\Index(name:"os_idx", columns:["os_family"])]
#[ORM\Index(name:"php_idx", columns:["php_version"])]
#[ORM\Index(name:"glpi_default_language_idx", columns: ["glpi_default_language"])]
#[ORM\Index(name:"glpi_uuid_idx", columns: ["glpi_uuid"])]
class Telemetry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(length: 41, nullable: true)]
    private ?string $glpi_uuid = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $glpi_version = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $glpi_default_language = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $glpi_avg_entities = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $glpi_avg_computers = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $glpi_avg_networkequipments = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $glpi_avg_tickets = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $glpi_avg_problems = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $glpi_avg_changes = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $glpi_avg_projects = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $glpi_avg_users = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $glpi_avg_groups = null;

    #[ORM\Column(nullable: true)]
    private ?bool $glpi_ldap_enabled = null;

    #[ORM\Column(nullable: true)]
    private ?bool $glpi_mailcollector_enabled = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $glpi_notifications = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $db_engine = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $db_version = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $db_size = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $db_log_size = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $db_sql_mode = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $web_engine = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $web_version = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $php_version = null;

    #[ORM\Column(type: 'text', length: 655, nullable: true)]
    private ?string $php_modules = null;

    #[ORM\Column(nullable: true)]
    private ?int $php_config_max_execution_time = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $php_config_memory_limit = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $php_config_post_max_size = null;

    #[ORM\Column(nullable: true)]
    private ?bool $php_config_safe_mode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $php_config_session = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $php_config_upload_max_filesize = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $os_family = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $os_distribution = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $os_version = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $install_mode = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getGlpiUuid(): ?string
    {
        return $this->glpi_uuid;
    }

    public function setGlpiUuid(?string $glpi_uuid): self
    {
        $this->glpi_uuid = $glpi_uuid;

        return $this;
    }

    public function getGlpiVersion(): ?string
    {
        return $this->glpi_version;
    }

    public function setGlpiVersion(?string $glpi_version): self
    {
        $this->glpi_version = $glpi_version;

        return $this;
    }

    public function getGlpiDefaultLanguage(): ?string
    {
        return $this->glpi_default_language;
    }

    public function setGlpiDefaultLanguage(?string $glpi_default_language): self
    {
        $this->glpi_default_language = $glpi_default_language;

        return $this;
    }

    public function getGlpiAvgEntities(): ?string
    {
        return $this->glpi_avg_entities;
    }

    public function setGlpiAvgEntities(?string $glpi_avg_entities): self
    {
        $this->glpi_avg_entities = $glpi_avg_entities;

        return $this;
    }

    public function getGlpiAvgComputers(): ?string
    {
        return $this->glpi_avg_computers;
    }

    public function setGlpiAvgComputers(?string $glpi_avg_computers): self
    {
        $this->glpi_avg_computers = $glpi_avg_computers;

        return $this;
    }

    public function getGlpiAvgNetworkequipments(): ?string
    {
        return $this->glpi_avg_networkequipments;
    }

    public function setGlpiAvgNetworkequipments(?string $glpi_avg_networkequipments): self
    {
        $this->glpi_avg_networkequipments = $glpi_avg_networkequipments;

        return $this;
    }

    public function getGlpiAvgTickets(): ?string
    {
        return $this->glpi_avg_tickets;
    }

    public function setGlpiAvgTickets(?string $glpi_avg_tickets): self
    {
        $this->glpi_avg_tickets = $glpi_avg_tickets;

        return $this;
    }

    public function getGlpiAvgProblems(): ?string
    {
        return $this->glpi_avg_problems;
    }

    public function setGlpiAvgProblems(?string $glpi_avg_problems): self
    {
        $this->glpi_avg_problems = $glpi_avg_problems;

        return $this;
    }

    public function getGlpiAvgChanges(): ?string
    {
        return $this->glpi_avg_changes;
    }

    public function setGlpiAvgChanges(?string $glpi_avg_changes): self
    {
        $this->glpi_avg_changes = $glpi_avg_changes;

        return $this;
    }

    public function getGlpiAvgProjects(): ?string
    {
        return $this->glpi_avg_projects;
    }

    public function setGlpiAvgProjects(?string $glpi_avg_projects): self
    {
        $this->glpi_avg_projects = $glpi_avg_projects;

        return $this;
    }

    public function getGlpiAvgUsers(): ?string
    {
        return $this->glpi_avg_users;
    }

    public function setGlpiAvgUsers(?string $glpi_avg_users): self
    {
        $this->glpi_avg_users = $glpi_avg_users;

        return $this;
    }

    public function getGlpiAvgGroups(): ?string
    {
        return $this->glpi_avg_groups;
    }

    public function setGlpiAvgGroups(?string $glpi_avg_groups): self
    {
        $this->glpi_avg_groups = $glpi_avg_groups;

        return $this;
    }

    public function isGlpiLdapEnabled(): ?bool
    {
        return $this->glpi_ldap_enabled;
    }

    public function setGlpiLdapEnabled(?bool $glpi_ldap_enabled): self
    {
        $this->glpi_ldap_enabled = $glpi_ldap_enabled;

        return $this;
    }

    public function isGlpiMailcollectorEnabled(): ?bool
    {
        return $this->glpi_mailcollector_enabled;
    }

    public function setGlpiMailcollectorEnabled(?bool $glpi_mailcollector_enabled): self
    {
        $this->glpi_mailcollector_enabled = $glpi_mailcollector_enabled;

        return $this;
    }

    public function getGlpiNotifications(): ?string
    {
        return $this->glpi_notifications;
    }

    public function setGlpiNotifications(?string $glpi_notifications): self
    {
        $this->glpi_notifications = $glpi_notifications;

        return $this;
    }

    public function getDbEngine(): ?string
    {
        return $this->db_engine;
    }

    public function setDbEngine(?string $db_engine): self
    {
        $this->db_engine = $db_engine;

        return $this;
    }

    public function getDbVersion(): ?string
    {
        return $this->db_version;
    }

    public function setDbVersion(?string $db_version): self
    {
        $this->db_version = $db_version;

        return $this;
    }

    public function getDbSize(): ?int
    {
        return $this->db_size;
    }

    public function setDbSize(?int $db_size): self
    {
        $this->db_size = $db_size;

        return $this;
    }

    public function getDbLogSize(): ?int
    {
        return $this->db_log_size;
    }

    public function setDbLogSize(?int $db_log_size): self
    {
        $this->db_log_size = $db_log_size;

        return $this;
    }

    public function getDbSqlMode(): ?string
    {
        return $this->db_sql_mode;
    }

    public function setDbSqlMode(?string $db_sql_mode): self
    {
        $this->db_sql_mode = $db_sql_mode;

        return $this;
    }

    public function getWebEngine(): ?string
    {
        return $this->web_engine;
    }

    public function setWebEngine(?string $web_engine): self
    {
        $this->web_engine = $web_engine;

        return $this;
    }

    public function getWebVersion(): ?string
    {
        return $this->web_version;
    }

    public function setWebVersion(?string $web_version): self
    {
        $this->web_version = $web_version;

        return $this;
    }

    public function getPhpVersion(): ?string
    {
        return $this->php_version;
    }

    public function setPhpVersion(?string $php_version): self
    {
        $this->php_version = $php_version;

        return $this;
    }

    public function getPhpModules(): ?string
    {
        return $this->php_modules;
    }

    public function setPhpModules(?string $php_modules): self
    {
        $this->php_modules = $php_modules;

        return $this;
    }

    public function getPhpConfigMaxExecutionTime(): ?int
    {
        return $this->php_config_max_execution_time;
    }

    public function setPhpConfigMaxExecutionTime(?int $php_config_max_execution_time): self
    {
        $this->php_config_max_execution_time = $php_config_max_execution_time;

        return $this;
    }

    public function getPhpConfigMemoryLimit(): ?string
    {
        return $this->php_config_memory_limit;
    }

    public function setPhpConfigMemoryLimit(?string $php_config_memory_limit): self
    {
        $this->php_config_memory_limit = $php_config_memory_limit;

        return $this;
    }

    public function getPhpConfigPostMaxSize(): ?string
    {
        return $this->php_config_post_max_size;
    }

    public function setPhpConfigPostMaxSize(?string $php_config_post_max_size): self
    {
        $this->php_config_post_max_size = $php_config_post_max_size;

        return $this;
    }

    public function isPhpConfigSafeMode(): ?bool
    {
        return $this->php_config_safe_mode;
    }

    public function setPhpConfigSafeMode(?bool $php_config_safe_mode): self
    {
        $this->php_config_safe_mode = $php_config_safe_mode;

        return $this;
    }

    public function getPhpConfigSession(): ?string
    {
        return $this->php_config_session;
    }

    public function setPhpConfigSession(?string $php_config_session): self
    {
        $this->php_config_session = $php_config_session;

        return $this;
    }

    public function getPhpConfigUploadMaxFilesize(): ?string
    {
        return $this->php_config_upload_max_filesize;
    }

    public function setPhpConfigUploadMaxFilesize(?string $php_config_upload_max_filesize): self
    {
        $this->php_config_upload_max_filesize = $php_config_upload_max_filesize;

        return $this;
    }

    public function getOsFamily(): ?string
    {
        return $this->os_family;
    }

    public function getOsDistribution(): ?string
    {
        return $this->os_distribution;
    }

    public function setOsFamily(?string $os_family): self
    {
        $this->os_family = $os_family;

        return $this;
    }

    public function getOsVersion(): ?string
    {
        return $this->os_version;
    }

    public function setOsVersion(?string $os_version): self
    {
        $this->os_version = $os_version;

        return $this;
    }

    public function getInstallMode(): ?string
    {
        return $this->install_mode;
    }

    public function setInstallMode(?string $install_mode): self
    {
        $this->install_mode = $install_mode;

        return $this;
    }
}
