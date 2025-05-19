<?php

namespace App\Form;

use App\Entity\Departement;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer une adresse email']),
                    new Email(['message' => 'Adresse email invalide']),
                ],
                'attr' => ['placeholder' => 'Email professionnel']
            ])
            ->add('nom', TextType::class, [
                'constraints' => [new NotBlank(['message' => 'Le nom est obligatoire'])],
                'attr' => ['placeholder' => 'Nom de famille']
            ])
            ->add('prenom', TextType::class, [
                'constraints' => [new NotBlank(['message' => 'Le prénom est obligatoire'])],
                'attr' => ['placeholder' => 'Prénom']
            ])
            ->add('telephone', TelType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'Ex: +33 6 12 34 56 78']
            ])
            ->add('departement', EntityType::class, [
                'class' => Departement::class,
                'choice_label' => function (Departement $departement) {
                    return $departement->getNomDepartemnt();
                },
                'required' => false,
                'placeholder' => 'Sélectionnez un département'
            ])
  
            ->add('poste', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'Ex: Responsable logistique']
            ])
            ->add('dateContrat', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Date du contrat'
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Description du poste ou notes supplémentaires'
                ]
            ])
            ->add('isActive', ChoiceType::class, [
                'choices' => [
                    'Actif' => true,
                    'Inactif' => false
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'Statut du compte'
            ]);
            
        // Ajouter le champ mot de passe seulement si c'est un nouvel utilisateur
        if ($options['is_new']) {
            $builder->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => ['autocomplete' => 'new-password'],
                    'constraints' => [
                        new NotBlank(['message' => 'Veuillez entrer un mot de passe']),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères',
                            'max' => 4096,
                        ]),
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'invalid_message' => 'Les mots de passe doivent correspondre',
            ]);
            
            $builder->add('roles', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN'
                ],
                'expanded' => true,
                'multiple' => true,
                'label' => 'Rôles'
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_new' => false
        ]);
    }
}