<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('_username', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Email',
            ])
            ->add('_password', PasswordType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Password',
            ]);
    }
}