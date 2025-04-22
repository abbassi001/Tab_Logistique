<?php

namespace App\Form;

use App\Entity\Transport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TransportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('type_transport', ChoiceType::class, [
            'choices' => [
                'Camion' => 'CAMION',
                'Avion' => 'AVION',
                'Bateau' => 'BATEAU',
                'Train' => 'TRAIN',
                'Autre' => 'AUTRE'
            ],
            'label' => 'Type de transport'
        ])
            ->add(child: 'compagnie_transport')
            ->add('numero_vol_navire')
            ->add('date_depart', null, [
                'widget' => 'single_text'
            ])
            ->add('lieu_depart')
            ->add('date_arrivee', null, [
                'widget' => 'single_text'
            ])
            ->add('lieu_arrivee')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transport::class,
        ]);
    }
}
