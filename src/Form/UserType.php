<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $inputClass = "px-3";
        $builder
            ->add('firstName', TextType::class, [
                'attr' => [
                    'class' => $inputClass,
                    'placeholder' => 'First name'
                ],
                'label' => 'Prénom',
                'required' => true
            ])
            ->add('lastName', TextType::class, [
                'attr' => [
                    'class' => $inputClass,
                    'placeholder' => 'Last name'
                ],
                'label' => 'Nom',
                'required' => true
            ])
            ->add('organisation', TextType::class, [
                'attr' => [
                    'class' => $inputClass,
                    'placeholder' => 'Organization'
                ],
                'label' => 'Organisation',
                'required' => true
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => $inputClass,
                    'placeholder' => 'Email'
                ],
                'label' => 'Email',
                'required' => true
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Your passwords are different.',
                'options' => ['attr' => ['class' => $inputClass]],
                'required' => true,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Confirm password'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
