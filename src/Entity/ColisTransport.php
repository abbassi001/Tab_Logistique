<?php

namespace App\Entity;

use App\Repository\ColisTransportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ColisTransportRepository::class)]
#[ORM\UniqueConstraint(name: "colis_transport_unique", columns: ["colis_id", "transport_id"])]
class ColisTransport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(inversedBy: 'colisTransports')]
    private ?Colis $colis = null;
    
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Transport $transport = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_association = null;

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
    
    public function getTransport(): ?Transport
    {
        return $this->transport;
    }

    public function setTransport(?Transport $transport): static
    {
        $this->transport = $transport;

        return $this;
    }

    public function getDateAssociation(): ?\DateTimeInterface
    {
        return $this->date_association;
    }

    public function setDateAssociation(\DateTimeInterface $date_association): static
    {
        $this->date_association = $date_association;

        return $this;
    }
}