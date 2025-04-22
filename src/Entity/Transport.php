<?php

namespace App\Entity;

use App\Repository\TransportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransportRepository::class)]
class Transport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $type_transport = null;

    #[ORM\Column(length: 255)]
    private ?string $compagnie_transport = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $numero_vol_navire = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_depart = null;

    #[ORM\Column(length: 255)]
    private ?string $lieu_depart = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_arrivee = null;

    #[ORM\Column(length: 255)]
    private ?string $lieu_arrivee = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeTransport(): ?string
    {
        return $this->type_transport;
    }

    public function setTypeTransport(string $type_transport): static
    {
        $this->type_transport = $type_transport;

        return $this;
    }

    public function getCompagnieTransport(): ?string
    {
        return $this->compagnie_transport;
    }

    public function setCompagnieTransport(string $compagnie_transport): static
    {
        $this->compagnie_transport = $compagnie_transport;

        return $this;
    }

    public function getNumeroVolNavire(): ?string
    {
        return $this->numero_vol_navire;
    }

    public function setNumeroVolNavire(?string $numero_vol_navire): static
    {
        $this->numero_vol_navire = $numero_vol_navire;

        return $this;
    }

    public function getDateDepart(): ?\DateTimeInterface
    {
        return $this->date_depart;
    }

    public function setDateDepart(?\DateTimeInterface $date_depart): static
    {
        $this->date_depart = $date_depart;

        return $this;
    }

    public function getLieuDepart(): ?string
    {
        return $this->lieu_depart;
    }

    public function setLieuDepart(string $lieu_depart): static
    {
        $this->lieu_depart = $lieu_depart;

        return $this;
    }

    public function getDateArrivee(): ?\DateTimeInterface
    {
        return $this->date_arrivee;
    }

    public function setDateArrivee(\DateTimeInterface $date_arrivee): static
    {
        $this->date_arrivee = $date_arrivee;

        return $this;
    }

    public function getLieuArrivee(): ?string
    {
        return $this->lieu_arrivee;
    }

    public function setLieuArrivee(string $lieu_arrivee): static
    {
        $this->lieu_arrivee = $lieu_arrivee;

        return $this;
    }
}
