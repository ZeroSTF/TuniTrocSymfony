<?php

namespace App\Form;



use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email', EmailType::class, [
            'constraints' => [
                new NotBlank(),
                new Email(),
            ],
        ])
        ->add('pwd', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => 'The password fields must match.',
            'options' => ['attr' => ['class' => 'password-field']],
            'required' => true,
            'first_options'  => ['label' => 'Password'],
            'second_options' => ['label' => 'Repeat Password'],
            'constraints' => [
                new NotBlank(),
                new Length([
                    'min' => 6,
                    'max' => 255,
                ]),
            ],
        ])
        ->add('nom', TextType::class, [
            'constraints' => [
                new NotBlank(),
                new Length([
                    'min' => 2,
                    'max' => 255,
                ]),
            ],
        ])
        ->add('prenom', TextType::class, [
            'constraints' => [
                new NotBlank(),
                new Length([
                    'min' => 2,
                    'max' => 255,
                ]),
            ],
        ])
        ->add('photo', FileType::class, [
            'required' => false,
            'mapped' => false,
            'constraints' => [
                new File([
                    'maxSize' => '1024k',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png',
                        'image/gif',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid image file (jpg, png, gif)',
                ])
            ],
            'help' => 'Please upload a valid image file (jpg, png, gif)'
        ])
        ->add('numTel', TextType::class, [
            'constraints' => [
                new NotBlank(),
                new Length([
                    'min' => 7,
                    'max' => 9,
                ]),
            ],
        ])
        ->add('ville', ChoiceType::class, [
            'choices' => [
                'Ariana' => 'Ariana',
                'Béja' => 'Béja',
                'Ben Arous' => 'Ben Arous',
                'Bizerte' => 'Bizerte',
                'Gabès' => 'Gabès',
                'Gafsa' => 'Gafsa',
                'Jendouba' => 'Jendouba',
                'Kairouan' => 'Kairouan',
                'Kasserine' => 'Kasserine',
                'Kébili' => 'Kébili',
                'La Manouba' => 'La Manouba',
                'Le Kef' => 'Le Kef',
                'Mahdia' => 'Mahdia',
                'Médenine' => 'Médenine',
                'Monastir' => 'Monastir',
                'Nabeul' => 'Nabeul',
                'Sfax' => 'Sfax',
                'Sidi Bouzid' => 'Sidi Bouzid',
                'Siliana' => 'Siliana',
                'Sousse' => 'Sousse',
                'Tataouine' => 'Tataouine',
                'Tozeur' => 'Tozeur',
                'Tunis' => 'Tunis',
                'Zaghouan' => 'Zaghouan',
            ],
            'attr' => [
                'class' => 'form-control',
            ],
        ])
        ->add('valeurFidelite', TextType::class, [
            'constraints' => [
                new NotBlank(),
                new Length([
                    'min' => 0,
                    'max' => 10000,
                ]),
            ],
        ])
        ->add('role', ChoiceType::class, [
            'choices' => [
                'Admin' => true,
                'Client' => false
            ],
            'expanded' => true
        ])
        ->add('etat', ChoiceType::class, [
            'choices' => [
                'ACTIF' => 'ACTIF',
                'INACTIF' => 'INACTIF',
                'BLOQUE' => 'BLOQUE',
                'NONBLOQUE' => 'NONBLOQUE',
                'ENATTENTECONFIRMATION' => 'ENATTENTECONFIRMATION'
            ]
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
