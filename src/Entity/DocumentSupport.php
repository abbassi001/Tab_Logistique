<?php

namespace App\Entity;

use App\Repository\DocumentSupportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentSupportRepository::class)]
class DocumentSupport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]

    private ?Colis $colis = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_fichier = null;

    #[ORM\Column(length: 100)]
    private ?string $type_document = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_upload = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $chemin_stockage = null;

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

    public function getNomFichier(): ?string
    {
        return $this->nom_fichier;
    }

    public function setNomFichier(string $nom_fichier): static
    {
        $this->nom_fichier = $nom_fichier;

        return $this;
    }

    public function getTypeDocument(): ?string
    {
        return $this->type_document;
    }

    public function setTypeDocument(string $type_document): static
    {
        $this->type_document = $type_document;

        return $this;
    }

    public function getDateUpload(): ?\DateTimeInterface
    {
        return $this->date_upload;
    }

    public function setDateUpload(\DateTimeInterface $date_upload): static
    {
        $this->date_upload = $date_upload;

        return $this;
    }

    public function getCheminStockage(): ?string
    {
        return $this->chemin_stockage;
    }

    public function setCheminStockage(?string $chemin_stockage): static
    {
        $this->chemin_stockage = $chemin_stockage;

        return $this;
    }
}
