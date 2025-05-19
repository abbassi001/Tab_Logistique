<?php

namespace App\Entity;

use App\Enum\StatusType;
use App\Repository\StatutRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatutRepository::class)]
class Statut
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'statuts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Colis $colis = null;

    #[ORM\Column(type: 'string', enumType: StatusType::class)]
    private ?StatusType $type_statut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_statut = null;

    #[ORM\Column(length: 255)]
    private ?string $localisation = null;

    #[ORM\ManyToOne]
    private ?User $user = null;


    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getColis(): ?Colis
    {
        return $this->colis;
    }

    public function setColis(?Colis $colis): static
    {
        $this->colis = $colis;

        return $this;
    }

    public function getTypeStatut(): ?StatusType
    {
        return $this->type_statut;
    }

    public function setTypeStatut(StatusType $type_statut): static
    {
        $this->type_statut = $type_statut;

        return $this;
    }

    public function getDateStatut(): ?\DateTimeInterface
    {
        return $this->date_statut;
    }

    public function setDateStatut(\DateTimeInterface $date_statut): static
    {
        $this->date_statut = $date_statut;

        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): static
    {
        $this->localisation = $localisation;

        return $this;
    }
}