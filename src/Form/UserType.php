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
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer une adresse email',
                    ]),
                    new Email([
                        'mode' => 'strict',
                        'message' => 'L\'adresse email "{{ value }}" n\'est pas valide',
                    ]),
                ],
                'label' => 'Email'
            ])
            ->add('nom', TextType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un nom',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom doit contenir au maximum {{ limit }} caractères',
                    ]),
                ],
                'label' => 'Nom'
            ])
            ->add('prenom', TextType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un prénom',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le prénom doit contenir au maximum {{ limit }} caractères',
                    ]),
                ],
                'label' => 'Prenom'
            ])
            ->add('numTel', TextType::class, [
                'required' => false,
                'label' => 'Numero de Telephone',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir le numéro de téléphone.',
                    ]),
                    new Length([
                        'min' => 8,
                        'max' => 8,
                        'exactMessage' => 'Le numéro de téléphone doit comporter exactement {{ limit }} chiffres.',
                    ]),
                    new Regex([
                        'pattern' => '/^\d+$/',
                        'message' => 'Le numéro de téléphone doit contenir uniquement des chiffres.',
                    ]),
                ],
            ])
            ->add('ville', ChoiceType::class, [
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

                'label' => 'Photo (fichier JPG ou PNG)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image JPG ou PNG valide.',
                    ])
                ],
                'data_class' => null, // add this line to handle the new photo field type

            ])
            ->add('pwd', PasswordType::class, [
                'required' => false,
                'label' => 'Mot de Passe',

                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un mot de passe.',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit comporter au moins {{ limit }} caractères.',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ])

                ],
            ])
            ->add('confirm_password', PasswordType::class, [
                'required' => false,
                'label' => 'Confirmer le mot de passe',
                'help' => 'Votre mot de passe doit comporter au moins 6 caractères.',
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez confirmer le mot de passe.',
                    ]),
                    new Callback([
                        'callback' => function ($value, ExecutionContextInterface $context) use ($builder) {
                            $password = $builder->getData()->getPwd();
                            dump($password);
                            dump($value);
                            if ($password !== null && $password !== $value) {
                                $context->buildViolation('Les mot de passes ne sont pas identiques.')->addViolation();
                            }
                        },
                        'payload' => ['builder' => $builder],
                    ]),

                ],
            ])
            ->add('valeurFidelite', TextType::class, [
                'required' => false,
                'label' => 'Valeur Fidelité',
                'constraints' => [
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

            ])
            ->add('etat', ChoiceType::class, [
                'choices' => [
                    'ACTIVE' => 'ACTIVE',
                    'INACTIVE' => 'INACTIVE',
                    'BLOCKED' => 'BLOCKED',
                    'PENDING' => 'PENDING',
                    'DELETED' => 'DELETED'
                ]
            ]);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
