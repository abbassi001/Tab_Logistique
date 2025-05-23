<?php

namespace App\Entity;

use App\Repository\ColisRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ColisRepository::class)]
class Colis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codeTracking = null;

    #[ORM\Column(length: 255)]
    private ?string $dimensions = null;
    
    #[ORM\Column(type: Types::FLOAT, nullable: true,)]
    #[Assert\Positive(message: "Doit être un nombre positif.")]

    private ?float $longueur = null;
    
    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Assert\Positive(message: "Doit être un nombre positif.")]
    private ?float $largeur = null;
    
    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Assert\Positive(message: "Doit être un nombre positif.")]
    private ?float $hauteur = null;

    #[ORM\Column]
    #[Assert\Positive(message: "Doit être un nombre positif.")]
    private ?float $poids = null;
    
    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $volumeCbm = null;

    #[ORM\Column]
    #[Assert\Positive(message: "Doit être un nombre positif.")]
    private ?float $valeur_declaree = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]

    private ?\DateTimeInterface $date_creation = null;

    #[ORM\Column(length: 255)]
    private ?string $nature_marchandise = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description_marchandise = null;

    #[ORM\Column(length: 255)]
    private ?string $classification_douaniere = null;

    // Relations avec d'autres entités
    #[ORM\ManyToOne]
    private ?Expediteur $expediteur = null;

    #[ORM\ManyToOne]
    private ?Destinataire $destinataire = null;

    #[ORM\ManyToOne]
    private ?Warehouse $warehouse = null;
    
    #[ORM\OneToMany(mappedBy: 'colis', targetEntity: Statut::class, orphanRemoval: true)]
    private Collection $statuts;
    
    #[ORM\OneToMany(mappedBy: 'colis', targetEntity: Photo::class, orphanRemoval: true)]
    private Collection $photos;
    
    #[ORM\OneToMany(mappedBy: 'colis', targetEntity: DocumentSupport::class, orphanRemoval: true)]
    private Collection $documents;
    
    #[ORM\OneToMany(mappedBy: 'colis', targetEntity: ColisTransport::class, orphanRemoval: true)]
    private Collection $colisTransports;

    

    public function __construct()
    {
        $this->statuts = new ArrayCollection();
        $this->photos = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->colisTransports = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeTracking(): ?string
    {
        return $this->codeTracking;
    }

    public function setCodeTracking(?string $codeTracking): static
    {
        $this->codeTracking = $codeTracking;

        return $this;
    }

    public function getDimensions(): ?string
    {
        // Si les dimensions individuelles sont définies, on peut les formatter
        if ($this->longueur !== null && $this->largeur !== null && $this->hauteur !== null) {
            return sprintf('%.2f cm x %.2f cm x %.2f cm', $this->longueur, $this->largeur, $this->hauteur);
        }
        
        return $this->dimensions;
    }

    public function setDimensions(string $dimensions): static
    {
        $this->dimensions = $dimensions;

        return $this;
    }
    
    public function getLongueur(): ?float
    {
        return $this->longueur;
    }
    
    public function setLongueur(?float $longueur): static
    {
        $this->longueur = $longueur;
        return $this;
    }
    
    public function getLargeur(): ?float
    {
        return $this->largeur;
    }
    
    public function setLargeur(?float $largeur): static
    {
        $this->largeur = $largeur;
        return $this;
    }
    
    public function getHauteur(): ?float
    {
        return $this->hauteur;
    }
    
    public function setHauteur(?float $hauteur): static
    {
        $this->hauteur = $hauteur;
        return $this;
    }
    
    public function getVolumeCbm(): ?float
    {
        return $this->volumeCbm;
    }
    
    public function setVolumeCbm(?float $volumeCbm): static
    {
        $this->volumeCbm = $volumeCbm;
        return $this;
    }

    public function getPoids(): ?float
    {
        return $this->poids;
    }

    public function setPoids(?float $poids): static
    {
        $this->poids = $poids;

        return $this;
    }

    // Autres getters et setters
    public function getValeurDeclaree(): ?float
    {
        return $this->valeur_declaree;
    }

    public function setValeurDeclaree(float $valeur_declaree): static
    {
        $this->valeur_declaree = $valeur_declaree;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getNatureMarchandise(): ?string
    {
        return $this->nature_marchandise;
    }

    public function setNatureMarchandise(string $nature_marchandise): static
    {
        $this->nature_marchandise = $nature_marchandise;

        return $this;
    }

    public function getDescriptionMarchandise(): ?string
    {
        return $this->description_marchandise;
    }

    public function setDescriptionMarchandise(?string $description_marchandise): static
    {
        $this->description_marchandise = $description_marchandise;

        return $this;
    }

    public function getClassificationDouaniere(): ?string
    {
        return $this->classification_douaniere;
    }

    public function setClassificationDouaniere(string $classification_douaniere): static
    {
        $this->classification_douaniere = $classification_douaniere;

        return $this;
    }

    // Getters et setters pour les relations
    public function getExpediteur(): ?Expediteur
    {
        return $this->expediteur;
    }

    public function setExpediteur(?Expediteur $expediteur): static
    {
        $this->expediteur = $expediteur;

        return $this;
    }

    public function getDestinataire(): ?Destinataire
    {
        return $this->destinataire;
    }

    public function setDestinataire(?Destinataire $destinataire): static
    {
        $this->destinataire = $destinataire;

        return $this;
    }

    public function getWarehouse(): ?Warehouse
    {
        return $this->warehouse;
    }

    public function setWarehouse(?Warehouse $warehouse): static
    {
        $this->warehouse = $warehouse;

        return $this;
    }
    
    /**
     * @return Collection<int, Statut>
     */
    public function getStatuts(): Collection
    {
        return $this->statuts;
    }

    public function addStatut(Statut $statut): static
    {
        if (!$this->statuts->contains($statut)) {
            $this->statuts->add($statut);
            $statut->setColis($this);
        }

        return $this;
    }

    public function removeStatut(Statut $statut): static
    {
        if ($this->statuts->removeElement($statut)) {
            // set the owning side to null (unless already changed)
            if ($statut->getColis() === $this) {
                $statut->setColis(null);
            }
        }

        return $this;
    }
    
    /**
     * @return Collection<int, Photo>
     */
    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    public function addPhoto(Photo $photo): static
    {
        if (!$this->photos->contains($photo)) {
            $this->photos->add($photo);
            $photo->setColis($this);
        }

        return $this;
    }

    public function removePhoto(Photo $photo): static
    {
        if ($this->photos->removeElement($photo)) {
            // set the owning side to null (unless already changed)
            if ($photo->getColis() === $this) {
                $photo->setColis(null);
            }
        }

        return $this;
    }
    
    /**
     * @return Collection<int, DocumentSupport>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(DocumentSupport $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setColis($this);
        }

        return $this;
    }

    public function removeDocument(DocumentSupport $document): static
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getColis() === $this) {
                $document->setColis(null);
            }
        }

        return $this;
    }
    
    /**
     * @return Collection<int, ColisTransport>
     */
    public function getColisTransports(): Collection
    {
        return $this->colisTransports;
    }

    public function addColisTransport(ColisTransport $colisTransport): static
    {
        if (!$this->colisTransports->contains($colisTransport)) {
            $this->colisTransports->add($colisTransport);
            $colisTransport->setColis($this);
        }

        return $this;
    }

    public function removeColisTransport(ColisTransport $colisTransport): static
    {
        if ($this->colisTransports->removeElement($colisTransport)) {
            // set the owning side to null (unless already changed)
            if ($colisTransport->getColis() === $this) {
                $colisTransport->setColis(null);
            }
        }

        return $this;
    }
}