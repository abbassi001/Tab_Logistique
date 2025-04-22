<?php

namespace App\Entity;

use App\Repository\ExpediteurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExpediteurRepository::class)]
class Expediteur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_entreprise_individu = null;

    #[ORM\Column(length: 20)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 100)]
    private ?string $pays = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse_complete = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomEntrepriseIndividu(): ?string
    {
        return $this->nom_entreprise_individu;
    }

    public function setNomEntrepriseIndividu(string $nom_entreprise_individu): static
    {
        $this->nom_entreprise_individu = $nom_entreprise_individu;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(string $pays): static
    {
        $this->pays = $pays;

        return $this;
    }

    public function getAdresseComplete(): ?string
    {
        return $this->adresse_complete;
    }

    public function setAdresseComplete(?string $adresse_complete): static
    {
        $this->adresse_complete = $adresse_complete;

        return $this;
    }


}
