<?php

namespace App\Form;

use App\Entity\StudentGroup;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AccountEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class,[
                'attr' => [
                    'class' => 'form-control mb-2'
                ],
                'label_attr' => [
                    'class' => 'mb-1'
                ],
                'label' => 'Modifier adresse mail :',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Champ obligatoire'
                    ]),
                ],
            ])            
            ->add('plainPassword', PasswordType::class, [
                'attr' => [
                    'class' => 'form-control mb-2',                    
                ],
                'label_attr' => [
                    'class' => 'mb-1'
                ],
                'label' => 'Entrez votre mot de passe pour autoriser la modification',
                'mapped' => false,
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
