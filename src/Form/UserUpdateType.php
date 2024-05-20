<?php

namespace App\Form;

use App\Entity\StudentGroup;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('firstname', TextType::class,[
            'attr' => [
                'class' => 'form-control mb-2'
            ],
            'label_attr' => [
                'class' => 'mb-1'
            ],
            'label' => 'Prénom *',
            'constraints' => [
                new NotBlank([
                    'message' => 'Champ obligatoire'
                ]),
            ],
        ])
        ->add('lastname', TextType::class,[
            'attr' => [
                'class' => 'form-control mb-2'
            ],
            'label_attr' => [
                'class' => 'mb-1'
            ],
            'label' => 'Nom *',
            'constraints' => [
                new NotBlank([
                    'message' => 'Champ obligatoire'
                ]),
            ],
        ])
        ->add('pseudo', TextType::class,[
            'attr' => [
                'class' => 'form-control mb-2'
            ],
            'label_attr' => [
                'class' => 'mb-1'
            ],
            'label' => 'Pseudo',
            'required' => false,
        ])        
        ->add('avatar', FileType::class,[
            'attr' => [
                'class' => 'form-control mt-4'
            ],                
            'label' => false,
            'required' => false,
            'mapped' => false,
            'constraints' => [
                new Image([
                    'mimeTypes' => ['image/png', 'image/jpeg', 'image/webp'],
                    'mimeTypesMessage' => 'Format d\'image non pris en compte'
                ])
            ]
        ])
        ->add('deleteAvatar', CheckboxType::class, [
            'label' => 'Supprimer l\'avatar actuel',
            'required' => false,
            'attr' => [
                'class' => 'check-custom mt-2',
            ],
            'label_attr' => [
                'class' => 'check-label-custom mx-2 mt-2'
            ],
            'mapped' => false,

        ])
        ->add('modify', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-lg btn-custom my-4'
            ],
            'label' => 'Modifier les informations',
         ])            
    ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
