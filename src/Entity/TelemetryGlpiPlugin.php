<?php

namespace App\Entity;

use App\Repository\TelemetryGlpiPluginRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TelemetryGlpiPluginRepository::class)]
class TelemetryGlpiPlugin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // #[ORM\Column(type: Types::BIGINT)]
    // private ?string $telemetry_entry_id = null;
    #[ORM\ManyToOne(inversedBy: 'TelemetryGlpiPlugin')]
    private ?Telemetry $telemetry_entry = null;

    #[ORM\Column]
    private ?int $glpi_plugin_id = null;

    #[ORM\Column(length: 50)]
    private ?string $version = null;

    #[ORM\OneToOne(inversedBy: 'telemetryGlpiPlugin', targetEntity: self::class, cascade: ['persist', 'remove'])]
    private ?self $GlpiPlugin = null;

    #[ORM\OneToOne(mappedBy: 'GlpiPlugin', targetEntity: self::class, cascade: ['persist', 'remove'])]
    private ?self $telemetryGlpiPlugin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTelemetryEntry(): ?Telemetry
    {
        return $this->telemetry_entry;
    }

    public function setTelemetryEntry(?Telemetry $telemetry): static
    {
        $this->telemetry_entry = $telemetry;

        return $this;
    }
    // public function getTelemetryEntryId(): ?string
    // {
    //     return $this->telemetry_entry_id;
    // }

    // public function setTelemetryEntryId(string $telemetry_entry_id): static
    // {
    //     $this->telemetry_entry_id = $telemetry_entry_id;

    //     return $this;
    // }

    public function getGlpiPluginId(): ?int
    {
        return $this->glpi_plugin_id;
    }

    public function setGlpiPluginId(int $glpi_plugin_id): static
    {
        $this->glpi_plugin_id = $glpi_plugin_id;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getGlpiPlugin(): ?self
    {
        return $this->GlpiPlugin;
    }

    public function setGlpiPlugin(?self $GlpiPlugin): static
    {
        $this->GlpiPlugin = $GlpiPlugin;

        return $this;
    }

    public function getTelemetryGlpiPlugin(): ?self
    {
        return $this->telemetryGlpiPlugin;
    }

    public function setTelemetryGlpiPlugin(?self $telemetryGlpiPlugin): static
    {
        // unset the owning side of the relation if necessary
        if ($telemetryGlpiPlugin === null && $this->telemetryGlpiPlugin !== null) {
            $this->telemetryGlpiPlugin->setGlpiPlugin(null);
        }

        // set the owning side of the relation if necessary
        if ($telemetryGlpiPlugin !== null && $telemetryGlpiPlugin->getGlpiPlugin() !== $this) {
            $telemetryGlpiPlugin->setGlpiPlugin($this);
        }

        $this->telemetryGlpiPlugin = $telemetryGlpiPlugin;

        return $this;
    }
}
