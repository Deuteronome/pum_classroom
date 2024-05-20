<?php

namespace App\Form;

use App\Entity\StudentGroup;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Test\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AccountRoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Apprenant' => 'ROLE_USER',
                    'Enseignant' => 'ROLE_TEACHER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'mapped' => false,
                
                'attr' => [
                    'class' => 'form-control mb-2',
                    
                ],
                'label_attr' => [
                    'class' => 'mb-1'
                ],
                'label' => 'Type de compte',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Champ obligatoire'
                    ]),
                ],
            ])
            ->add('modify', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-lg btn-custom'
                ],
                'label' => 'Modifier',
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
