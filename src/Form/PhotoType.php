<?php

namespace App\Form;

use App\Entity\Colis;
use App\Entity\Photo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class PhotoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Image (JPG, PNG,JPEG, GIF,max 5Mo)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                            'image/gif'

                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, PNG, JPEG, GIF)',
                    ])
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Description de la photo (optionnel)',
                    'rows' => 3
                ]
            ]);
            
        // Si le colis est déjà associé (venant de la page détail du colis)
        if ($options['data'] && $options['data']->getColis()) {
            $builder->add('colis', HiddenType::class, [
                'data' => $options['data']->getColis()->getId(),
                'mapped' => false
            ]);
        } else {
            $builder->add('colis', EntityType::class, [
                'class' => Colis::class,
                'choice_label' => function(Colis $colis) {
                    return $colis->getCodeTracking() . ' - ' . $colis->getNatureMarchandise();
                },
                'required' => false
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Photo::class,
        ]);
    }
}