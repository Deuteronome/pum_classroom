<?php

namespace App\Form;

use App\Entity\StudentGroup;
use App\Entity\User;
use App\Repository\StudentGroupRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AccountGroupType extends AbstractType
{
    private StudentGroupRepository $studentGroupRepository;
    
    public function __construct(StudentGroupRepository $studentGroupRepository)
    {
        $this->studentGroupRepository = $studentGroupRepository;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $groups = $this->studentGroupRepository->findAll();

                
        $builder
            ->add('studentGroup', ChoiceType::class, [
                'choices' => $groups,
                'choice_label' => 'groupName',
                'attr' => [
                    'class' => 'form-control'
                ],
                'label_attr' => [
                    'class' => 'mb-1'
                ],
                'label' => 'Groupe',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Champ obligatoire'
                    ]),
                ],
            ])
            ->add('deleteGroup', CheckboxType::class, [
                'label' => 'Supprimer le groupe actuel',
                'required' => false,
                'attr' => [
                    'class' => 'check-custom',
                ],
                'label_attr' => [
                    'class' => 'check-label-custom mx-2'
                ],
                'mapped' => false,
    
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
