<?php

namespace App\Form;

use App\Entity\Expediteur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpediteurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_entreprise_individu', TextType::class, [
                'label' => "Nom de l'entreprise ou de l'individu",
                'attr' => ['placeholder' => "Ex : Société XYZ ou Jean Dupont"]
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Numéro de téléphone',
                'attr' => ['placeholder' => '+33 6 12 34 56 78']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr' => ['placeholder' => 'expediteur@example.com']
            ])
            ->add('pays', CountryType::class, [
                'label' => 'Pays',
                'placeholder' => 'Choisissez un pays',
                'preferred_choices' => ['FR', 'TD', 'CM', 'SN', 'CI']
            ])
            ->add('adresse_complete', TextareaType::class, [
                'label' => 'Adresse complète',
                'attr' => [
                    'rows' => 3,
                    'placeholder' => '123 rue du Marché, Quartier Central, N’Djamena'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Expediteur::class,
        ]);
    }
}
