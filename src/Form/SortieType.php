<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'label' => 'Nom de la sortie',
                'required' => true,
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'widget' => 'single_text',
                //init à datetime +10jours
                // 'data' => new DateTime('+10 days'),
                'required' => true,
            ])

            ->add('duree', IntegerType::class, [
                'label' => 'Duree (en minutes)',
                'required' => true,
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('nbInscriptionMax', IntegerType::class, [
                'required' => true,
            ])
            ->add(
                'infosSortie',
                TextareaType::class,
                [
                    'required' => false,
                ]

            )
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
