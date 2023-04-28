<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\File;


class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cause', null, [
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est obligatoire.',
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9\s]+$/',
                        'message' => 'Ce champ ne doit contenir que des lettres ou des chiffres',
                    ]),
                    new Callback(function ($value, ExecutionContextInterface $context) {
                        $forbiddenWords = ['connard', 'stupide'];
                        if (in_array(strtolower($value), $forbiddenWords)) {
                            $context->buildViolation('Ce champ contient un mot interdit : {{ word }}')
                                ->setParameter('{{ word }}', $value)
                                ->addViolation();
                        }
                    }),
                ],
            ])
            ->add('etat', ChoiceType::class, [
                'choices'  => [
                    'Non traitée' => false,
                    'Traitée' => true,
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('idUserr', EntityType::class, [
                'label' => 'Receiver',
                'class' => User::class,
                'choice_label' => 'email',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('idUsers', EntityType::class, [
                'label' => 'Sender',
                'class' => User::class,
                'choice_label' => 'email',
                'attr' => ['class' => 'form-control'],
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
            ])
            ->add('date', null, [
                'label' => 'Date',
                'required' => true,
                'mapped' => true,
                'data' => new \DateTime(),
                'attr' => ['class' => 'form-control'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}
 

