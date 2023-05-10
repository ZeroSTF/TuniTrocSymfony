<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class ProfileFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('nom', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'label' => 'Nom',
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
            ])
            ->add('prenom', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'label' => 'Prenom',
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
            ])
            ->add('numTel', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control'],
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
            ]);



    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => false,
        ]);
    }
}
