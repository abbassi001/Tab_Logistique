<?php

namespace App\Entity;

use App\Repository\WarehouseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WarehouseRepository::class)]
class Warehouse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $codeUt = null;

    #[ORM\Column(length: 255)]
    private ?string $adresseWarehouse = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nomEntreprise = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $pays = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $codePostal = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeUt(): ?string
    {
        return $this->codeUt;
    }

    public function setCodeUt(string $codeUt): self
    {
        $this->codeUt = $codeUt;
        return $this;
    }

    public function getAdresseWarehouse(): ?string
    {
        return $this->adresseWarehouse;
    }

    public function setAdresseWarehouse(string $adresseWarehouse): self
    {
        $this->adresseWarehouse = $adresseWarehouse;
        return $this;
    }

    public function getNomEntreprise(): ?string
    {
        return $this->nomEntreprise;
    }

    public function setNomEntreprise(?string $nomEntreprise): self
    {
        $this->nomEntreprise = $nomEntreprise;
        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): self
    {
        $this->ville = $ville;
        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(?string $pays): self
    {
        $this->pays = $pays;
        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(?string $codePostal): self
    {
        $this->codePostal = $codePostal;
        return $this;
    }

    // Méthode pour une représentation en chaîne
    public function __toString(): string
    {
        $representation = $this->codeUt . ' - ' . $this->adresseWarehouse;
        if ($this->nomEntreprise) {
            $representation .= ' (' . $this->nomEntreprise . ')';
        }
        return $representation;
    }
}