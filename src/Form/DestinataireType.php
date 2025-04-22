<?php

namespace App\Form;

use App\Entity\Destinataire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class DestinataireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_entreprise_individu', TextType::class, [
                'label' => 'Nom / Entreprise *',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un nom']),
                    new Length(['min' => 3, 'max' => 255]),
                ],
                'attr' => [
                    'placeholder' => 'Nom du destinataire',
                    'maxlength' => 255,
                ],
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone *',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un numéro de téléphone']),
                    new Length(['min' => 5, 'max' => 20]),
                ],
                'attr' => [
                    'placeholder' => 'Ex: +33 6 12 34 56 78',
                    'class' => 'phone-input',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Email du destinataire',
                ],
            ])
            
            ->add('pays', \Symfony\Component\Form\Extension\Core\Type\CountryType::class, [
                'label' => 'Pays de destination *',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner un pays']),
                ],
                'attr' => [
                    'placeholder' => 'Sélectionnez un pays',
                    'class' => 'country-select',
                ],
            ])
            ->add('adresse_complete', TextareaType::class, [
                'label' => 'Adresse complète *',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une adresse']),
                ],
                'attr' => [
                    'placeholder' => 'Numéro, rue, code postal, ville...',
                    'rows' => 3,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Destinataire::class,
            'attr' => ['novalidate' => 'novalidate'], // Désactiver validation HTML5 native pour utiliser validation Symfony
        ]);
    }
}