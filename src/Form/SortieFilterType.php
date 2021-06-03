<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\SortieFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'required' => true
            ])
            ->add('query', TextType::class, [
                'required' => false,
                'label' => 'Le nom de la sortie contient :',
                'attr' => [
                    'placeholder' => 'Rechercher'
                ]
            ])
            ->add('startDate', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('endDate', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('isOrganisateur', CheckboxType::class,[
                'label' => "Sorties dont je suis l'organisateur",
                'required' => false,
            ])
            ->add('isInscrit', CheckboxType::class,[
                'label' => "Sorties auxquelles je suis inscrit/e",
                'required' => false,
            ])
            ->add('isNotInscrit', CheckboxType::class,[
                'label' => "Sorties auxquelles je ne suis pas inscrit/e",
                'required' => false,
            ])
            ->add('isPassed', CheckboxType::class,[
                'label' => "Sorties passées",
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SortieFilter::class,
        ]);
    }

}
