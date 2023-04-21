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
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Email'
            ])
            ->add('nom', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Nom'
            ])
            ->add('prenom', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Prenom'
            ])
            ->add('numTel', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Numero de Telephone',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your phone number',
                    ]),
                    new Length([
                        'min' => 8,
                        'max' => 8,
                        'exactMessage' => 'Your phone number should be exactly {{ limit }} numbers',
                    ]),
                    new Regex([
                        'pattern' => '/^\d+$/',
                        'message' => 'Your phone number should only contain numbers',
                    ]),
                ],
            ])
            ->add('ville', ChoiceType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Ville',
                'choices' => [
                    'Ariana' => 'Ariana',
                    'Beja' => 'Beja',
                    'Ben Arous' => 'Ben Arous',
                    'Bizerte' => 'Bizerte',
                    'Gabes' => 'Gabes',
                    'Gafsa' => 'Gafsa',
                    'Jendouba' => 'Jendouba',
                    'Kairouan' => 'Kairouan',
                    'Kasserine' => 'Kasserine',
                    'Kebili' => 'Kebili',
                    'Kef' => 'Kef',
                    'Mahdia' => 'Mahdia',
                    'Manouba' => 'Manouba',
                    'Medenine' => 'Medenine',
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

            ])
            ->add('photo', FileType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Photo (JPG or PNG file)',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid JPG or PNG image',
                    ])
                ],
                'data_class' => null, // add this line to handle the new photo field type
            ])


            ->add('pwd', PasswordType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Password',

                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ])

                ],
            ])
            ->add('confirm_password', PasswordType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Confirm Password',
                'help' => 'Your password must be at least 6 characters long.',
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please confirm your password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                    new Callback([
                        'callback' => function ($value, ExecutionContextInterface $context) use ($builder) {
                            $password = $builder->getData()->getPwd();
                            if ($password !== null && $password !== $value) {
                                $context->buildViolation('Your passwords do not match')->addViolation();
                            }
                        },
                        'payload' => ['builder' => $builder],
                    ]),

                ],
            ])
        ->add('valeurFidelite', TextType::class, [
            'label' => 'Valeur Fidelité',
            'attr' => ['class' => 'form-control'],
            'constraints' => [
                new NotBlank(),
                new Length([
                    'min' => 0,
                    'max' => 10000,
                ]),
            ],
        ])
        ->add('role', ChoiceType::class, [
            'attr' => ['class' => 'form-control'],
            'choices' => [
                'Admin' => true,
                'Client' => false
            ],
            'expanded' => true
        ])
        ->add('etat', ChoiceType::class, [
            'attr' => ['class' => 'form-control'],
            'choices' => [
                'ACTIVE' => 'ACTIVE',
                'INACTIVE' => 'INACTIVE',
                'BLOCKED' => 'BLOCKED',
                'PENDING' => 'PENDING',
                'DELETED' => 'DELETED'
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
