<?php

namespace App\Form;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nom ne doit pas être vide.',
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'La description ne doit pas être vide.',
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'La description doit contenir au moins {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('dateD', DateType::class, [
                'label' => 'Date de début',
                'required' => false,
            ])
            ->add('dateF', DateType::class, [
                'label' => 'Date de fin',
                'required' => false,
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
            'constraints' => [
                new Callback([$this, 'validate']),
            ],
        ]);
    }

    public function validate(Evenement $evenement, ExecutionContextInterface $context): void
    {
        $dateD = $evenement->getDateD();
        $dateF = $evenement->getDateF();

        if ($dateD >= $dateF) {
            $context->buildViolation('La date de fin doit être postérieure à la date de début.')
                ->atPath('dateF')
                ->addViolation();
        }
    }
}
