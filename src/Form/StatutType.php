<?php

namespace App\Form;

use App\Entity\Colis;
use App\Entity\Employe;
use App\Entity\Statut;
use App\Entity\Warehouse;
use App\Enum\StatusType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StatutType extends AbstractType
{
    // Dans StatutType.php
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeStatut', EnumType::class, [
                'class' => StatusType::class,
                'label' => 'Type de statut',
                'choice_label' => function($choice, $key, $value) {
                    return match($choice) {
                        StatusType::EN_ATTENTE => 'En attente',
                        StatusType::RECU => 'Reçu',
                        StatusType::EN_TRANSIT => 'En transit',
                        StatusType::EN_LIVRAISON => 'En livraison',
                        StatusType::LIVRE => 'Livré',
                        StatusType::RETOURNE => 'Retourné',
                        StatusType::BLOQUE_DOUANE => 'Bloqué en douane',
                        default => $choice->name
                    };
                },
                'placeholder' => 'Sélectionnez un statut'
            ])
            ->add('dateStatut', DateTimeType::class, [
                'widget' => 'single_text', // Cette option est valide pour DateTimeType
                'label' => 'Date du statut'
            ]);
        
        // Si on utilise le sélecteur d'entrepôt
        if ($options['use_warehouse_selector']) {
            $builder->add('warehouse', EntityType::class, [
                'class' => Warehouse::class,
                'choice_label' => function(Warehouse $warehouse) {
                    $label = $warehouse->getCodeUt() . ' - ' . $warehouse->getAdresseWarehouse();
                    if ($warehouse->getNomEntreprise()) {
                        $label .= ' (' . $warehouse->getNomEntreprise() . ')';
                    }
                    return $label;
                },
                'placeholder' => 'Sélectionnez un entrepôt...',
                'required' => false,
                'mapped' => false, // Ce champ n'est pas directement mappé à l'entité
                'label' => 'Entrepôt',
                'attr' => [
                    'class' => 'form-select warehouse-selector'
                ]
            ]);
            
            $builder->add('localisation', TextType::class, [
                'label' => 'Localisation',
                'attr' => [
                    'readonly' => true,
                    'placeholder' => 'La localisation sera générée automatiquement'
                ]
            ]);
        } else {
            $builder->add('localisation', TextType::class, [
                'label' => 'Localisation',
                'attr' => [
                    'placeholder' => 'Exemple: Paris, France - Entrepôt central'
                ]
            ]);
        }
        
        // Ajouter le champ employe sauf si spécifiquement désactivé
        if (!isset($options['hide_employe']) || $options['hide_employe'] !== true) {
            $builder->add('employe', EntityType::class, [
                'class' => Employe::class,
                'choice_label' => function(Employe $employe) {
                    return $employe->getNom() . ' ' . $employe->getPrenom();
                },
                'required' => false,
                'disabled' => isset($options['employe_disabled']) && $options['employe_disabled'],
                'placeholder' => 'Sélectionnez un employé'
            ]);
        }
        
        // On n'ajoute le champ colis que si on n'est pas dans le contexte du wizard
        if (!isset($options['wizard_mode']) || $options['wizard_mode'] !== true) {
            $builder->add('colis', EntityType::class, [
                'class' => Colis::class,
                'choice_label' => function(Colis $colis) {
                    return $colis->getCodeTracking() . ' - ' . $colis->getNatureMarchandise();
                },
                'placeholder' => 'Sélectionnez un colis'
            ]);
        }
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Statut::class,
            'wizard_mode' => false,
            'hide_employe' => false,
            'employe_disabled' => false,
            'use_warehouse_selector' => false,
        ]);
    }
}