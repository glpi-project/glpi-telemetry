<?php

namespace App\Entity;

use App\Repository\TelemetryGlpiPluginRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TelemetryGlpiPluginRepository::class)]
class TelemetryGlpiPlugin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Telemetry::class)]
    private ?Telemetry $telemetry_entry = null;

    #[ORM\ManyToOne(targetEntity: GlpiPlugin::class)]
    private ?int $glpi_plugin;

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

    // public function getGlpiPlugin(): ?int
    // {
    //     return $this->glpi_plugin;
    // }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }

}