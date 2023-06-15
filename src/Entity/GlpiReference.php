<?php

namespace App\Entity;

use App\Repository\GlpiReferenceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GlpiReferenceRepository::class)]
class GlpiReference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // #[ORM\Column]
    // private ?int $reference_id = null;

    #[ORM\Column(nullable: true)]
    private ?int $num_assets = null;

    #[ORM\Column(nullable: true)]
    private ?int $num_helpdesk = null;

    #[ORM\OneToOne(targetEntity: Reference::class, inversedBy: 'glpireference', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'reference_id', referencedColumnName: 'id', unique: true, nullable: false)]
    private ?Reference $reference = null;

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

    // public function getReferenceId(): ?int
    // {
    //     return $this->reference_id;
    // }

    // public function setReferenceId(int $reference_id): self
    // {
    //     $this->reference_id = $reference_id;

    //     return $this;
    // }

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
}
