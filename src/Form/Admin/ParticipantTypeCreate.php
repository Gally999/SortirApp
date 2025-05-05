<?php

namespace App\Form\Admin;

use Dom\Entity;
use App\Entity\Campus;
use App\Entity\Participant;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ParticipantTypeCreate extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Email',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez l\'email']
            ])
            ->add('pseudo', TextType::class, [
                'required' => true,
                'label' => 'Pseudo',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le pseudo']
            ])
            ->add('nom', TextType::class, [
                'required' => true,
                'label' => 'Nom',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le nom']
            ])
            ->add('prenom', TextType::class, [
                'required' => false,
                'label' => 'Prénom',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le prénom']
            ])
            ->add('password', TextType::class, [
                'required' => false,
                'label' => 'Mot de passe',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le mot de passe']
            ])
            ->add('telephone', TelType::class, [
                'required' => false,
                'label' => 'Numéro de téléphone',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le numéro de téléphone']
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                
            ])
            ->add('profilePicture', FileType::class, [
                'label' => 'Photo de profil',
                'required' => false,
                'mapped' => false,

            ])
            ->add('administrateur', CheckboxType::class, [
                'label' => 'Administrateur',
                'required' => false,

            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,

            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
