<?php

namespace App\Entity;

use App\Repository\TelemetryGlpiPluginRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TelemetryGlpiPluginRepository::class)]
#[ORM\Index(name:"created_at_idx", columns:["created_at"])]
class TelemetryGlpiPlugin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\ManyToOne(inversedBy: 'TelemetryGlpiPlugin')]
    private ?Telemetry $telemetry_entry = null;

    #[ORM\ManyToOne(targetEntity: GlpiPlugin::class, cascade: ['persist'])]
    private ?GlpiPlugin $glpi_plugin;

    #[ORM\Column(length: 50)]
    private ?string $version = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTelemetryEntry(): ?Telemetry
    {
        return $this->telemetry_entry;
    }

    public function setTelemetryEntry(?Telemetry $telemetry_entry): self
    {
        $this->telemetry_entry = $telemetry_entry;

        return $this;
    }

    public function getGlpiPlugin(): ?GlpiPlugin
    {
        return $this->glpi_plugin;
    }

    public function setGlpiPlugin(?GlpiPlugin $glpi_plugin): self
    {
        $this->glpi_plugin = $glpi_plugin;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
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

}
