<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie'
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date et heure de la sortie',
                'widget' => 'single_text',
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'label' => "Date limite d'inscription",
                'widget' => 'single_text'
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre de places'
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (minutes)'
            ])
            ->add('infos', TextareaType::class, [
                'label' => 'Description et infos'
            ])
            ->add('campusOrganisateur', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez un campus',
                'required' => true,
                'disabled' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer'
            ])
            ->add('publish', SubmitType::class, [
                'label' => 'Publier'
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));

    }

    protected function addElements(FormInterface $form, Ville $ville = null) {
        $form->add('ville', EntityType::class, [
            'class' => Ville::class,
            'choice_label' => 'nom',
            'mapped' => false,
            'placeholder' => 'Choisissez une ville',
            'data' => $ville,
            'query_builder' => function (VilleRepository $villeRepository) {
                    return $villeRepository->findAll();
            },
        ]);

        $lieux = array();

        if ($ville) {

            $lieuxRepository = $this->entityManager->getRepository('App:Lieu');
            $lieux = $lieuxRepository->createQueryBuilder('l')
                ->where('l.ville = :ville')
                ->setParameter('ville', $ville)
                ->getQuery()
                ->getResult();

        }

        $form->add('lieu', EntityType::class, [
            'class' => Lieu::class,
            'choice_label' => 'nom',
            'placeholder' => 'Choisissez un lieu',
            'required' => true,
            'choices' => $lieux,
        ]);

    }

    function onPreSubmit(FormEvent $event) {

        $form = $event->getForm();
        $data = $event->getData();

        $ville = $this->entityManager->getRepository('App:Ville')->find($data['ville']);

        $this->addElements($form, $ville);

    }

    function onPreSetData(FormEvent $event) {

        $sortie = $event->getData();
        $form = $event->getForm();

        if ($sortie->getLieu()) {
            $ville = $sortie->getLieu()->getVille() ? $sortie->getLieu()->getVille() : null;
        } else {
            $ville = null;
        }

        $this->addElements($form, $ville);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
