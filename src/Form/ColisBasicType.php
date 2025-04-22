<?php

namespace App\Form;

use App\Entity\Colis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColisBasicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Le champ dimensions sera rempli automatiquement grâce à JavaScript
            ->add('dimensions', HiddenType::class, [
                'required' => false,
            ])
            ->add('longueur', NumberType::class, [
                'label' => 'Longueur (cm) *',
                'required' => true,
                'scale' => 2,
                'attr' => [
                    'placeholder' => 'Ex: 30.5',
                    'class' => 'dimension-field',
                    'data-dimension' => 'longueur',
                    'min' => 0.01,
                    'step' => 0.01,
                ],
            ])
            ->add('largeur', NumberType::class, [
                'label' => 'Largeur (cm) *',
                'required' => true,
                'scale' => 2,
                'attr' => [
                    'placeholder' => 'Ex: 20.0',
                    'class' => 'dimension-field',
                    'data-dimension' => 'largeur',
                    'min' => 0.01,
                    'step' => 0.01,
                ],
            ])
            ->add('hauteur', NumberType::class, [
                'label' => 'Hauteur (cm) *',
                'required' => true,
                'scale' => 2,
                'attr' => [
                    'placeholder' => 'Ex: 15.0',
                    'class' => 'dimension-field',
                    'data-dimension' => 'hauteur',
                    'min' => 0.01,
                    'step' => 0.01,
                ],
            ])
            // Champ poids saisi manuellement
            ->add('poids', NumberType::class, [
                'label' => 'Poids (kg) *',
                'required' => true,
                'scale' => 2,
                'attr' => [
                    'placeholder' => 'Ex: 2.5',
                    'min' => 0.01,
                ],
            ])
            ->add('valeur_declaree', NumberType::class, [
                'label' => 'Valeur déclarée (€) *',
                'required' => true,
                'scale' => 2,
                'attr' => [
                    'placeholder' => 'Ex: 150.00',
                ],
            ])
            ->add('date_creation', DateTimeType::class, [
                'label' => 'Date de création *',
                'required' => true,
                'widget' => 'single_text',
                'data' => new \DateTime(),
            ])
            ->add('nature_marchandise', TextType::class, [
                'label' => 'Nature de la marchandise *',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: Électronique, Vêtements, Documents...',
                ],
            ])
            ->add('description_marchandise', TextareaType::class, [
                'label' => 'Description détaillée',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Description détaillée du contenu du colis...',
                ],
            ])
            ->add('classification_douaniere', TextType::class, [
                'label' => 'Classification douanière *',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: 8471.30.00',
                ],
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