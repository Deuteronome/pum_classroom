<?php

namespace App\Form;

use App\Entity\StudentGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class StudentGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('groupName', TextType::class, [
                'label' => 'Nom de la classe *',
                'attr' => [
                    'class' => 'form-control form-custom mb-2',
                ],
                'label_attr' => [
                    'class' => 'form-custom mb-1',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Donnez un nom à cette classe',
                    ]),
                    new Length([
                        'min' => 5,
                        'max' => 255,
                        'minMessage' => 'le nom doit comporter au moins {{ limit }} caractères',
                        'maxMessage' => 'nom trop long (max {{ limit }} caractères',
                    ])
                ],
            ])
            ->add('startAt', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de démarrage *',
                'attr' => [
                    'class' => 'form-control form-custom mb-2'
                ],
                'label_attr' => [
                    'class' => 'form-custom mb-1',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Définnissez une date de démarrage pour cette classe',
                    ])
                ],
            ])
            ->add('endAt', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de clôture',
                'attr' => [
                    'class' => 'form-control form-custom mb-2'
                ],
                'label_attr' => [
                    'class' => 'form-custom mb-1',
                ],
                'required' => false,
            ])
            ->add('create', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-lg btn-custom my-4'
                ],
                'label' => 'Créer la classe'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StudentGroup::class,
        ]);
    }
}
