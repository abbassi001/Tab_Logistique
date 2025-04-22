<?php

namespace App\Form;

use App\Entity\Colis;
use App\Entity\ColisTransport;
use App\Entity\Transport;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColisTransportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_association', null, [
                'widget' => 'single_text'
            ])
            ->add('colis', EntityType::class, [
                'class' => Colis::class,
                'choice_label' => function(Colis $colis) {
                    return $colis->getCodeTracking() . ' - ' . $colis->getNatureMarchandise();
                }
            ])
            ->add('transport', EntityType::class, [
                'class' => Transport::class,
                'choice_label' => function(Transport $transport) {
                    return $transport->getTypeTransport() . ' ' . $transport->getCompagnieTransport() . ' (' . $transport->getLieuDepart() . ' → ' . $transport->getLieuArrivee() . ')';
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ColisTransport::class,
        ]);
    }
}