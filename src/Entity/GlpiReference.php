<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\GlpiReferenceRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GlpiReferenceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class GlpiReference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $num_assets = null;

    #[ORM\Column(nullable: true)]
    private ?int $num_helpdesk = null;

    #[ORM\OneToOne(targetEntity: Reference::class, inversedBy: 'glpireference', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'reference_id', referencedColumnName: 'id', unique: true, nullable: false)]
    private ?Reference $reference = null;

    #[ORM\Column]
    private ?DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updated_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?Reference
    {
        return $this->reference;
    }

    public function setReference(Reference $reference): self
    {
        $this->reference = $reference;
        return $this;
    }

    public function getNumAssets(): ?int
    {
        return $this->num_assets;
    }

    public function setNumAssets(?int $num_assets): self
    {
        $this->num_assets = $num_assets;

        return $this;
    }

    public function getNumHelpdesk(): ?int
    {
        return $this->num_helpdesk;
    }

    public function setNumHelpdesk(?int $num_helpdesk): self
    {
        $this->num_helpdesk = $num_helpdesk;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    #[ORM\PrePersist]
    public function setTimestampsOnPersist(): void
    {
        $this->created_at = new DateTimeImmutable();
        $this->updated_at = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setTimestampsOnUpdate(): void
    {
        $this->updated_at = new DateTimeImmutable();
    }
}
