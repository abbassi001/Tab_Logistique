<?php

namespace App\Form;

use App\Entity\Colis;
use App\Entity\Destinataire;
use App\Entity\Expediteur;
use App\Entity\Warehouse;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('codeTracking')
            ->add('dimensions')
            ->add('poids')
            ->add('valeur_declaree')
            ->add('date_creation', null, [
                'widget' => 'single_text'
            ])
            ->add('nature_marchandise')
            ->add('description_marchandise')
            ->add('classification_douaniere')
            ->add('expediteur', EntityType::class, [
                'class' => Expediteur::class,
                'choice_label' => function(Expediteur $expediteur) {
                    return $expediteur->getNomEntrepriseIndividu() . ' (' . $expediteur->getPays() . ')';
                },
                'required' => false
            ])
            ->add('destinataire', EntityType::class, [
                'class' => Destinataire::class,
                'choice_label' => function(Destinataire $destinataire) {
                    return $destinataire->getNomEntrepriseIndividu() . ' (' . $destinataire->getPays() . ')';
                }
            ])
            ->add('warehouse', EntityType::class, [
                'class' => Warehouse::class,
                'choice_label' => function(Warehouse $warehouse) {
                    return $warehouse->getCodeUt() . ' - ' . $warehouse->getLocalisationWarehouse();
                },
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Colis::class,
        ]);
    }
}