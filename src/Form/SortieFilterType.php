<?php

namespace App\Form;

use App\Entity\Campus;
use App\Model\SortieSearchData;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentUser = $options['user'];

        $builder
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'label' => 'Campus',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                    ->orderBy('c.nom', 'ASC');
                },
                'preferred_choices' => function (Campus $campus) use ($currentUser) {
                    return $campus === $currentUser->getCampus();
                },
            ])
            ->add('searchTerm', TextType::class, [
                'label' => 'Le nom de la sortie contient',
                 'required' => false,
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Entre le',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'label' => 'et le',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('isOrganisateur', CheckboxType::class, [
                'label' => 'Sorties dont je suis l\'organisateur•rice',
                'required' => false,
            ])
            ->add('isInscrit', CheckboxType::class, [
                'label' => 'Sorties auxquelles je suis inscrit•e',
                'required' => false,
            ])
            ->add('isNotInscrit', CheckboxType::class, [
                'label' => 'Sorties auxquelles je ne suis pas inscrit•e',
                'required' => false,
            ])
            ->add('showTerminees', CheckboxType::class, [
                'label' => 'Afficher les sorties terminées',
                'required' => false,
            ])
            ->setMethod('GET')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SortieSearchData::class,
            'csrf_protection' => false,
            'user' => null,
        ]);
    }
}
