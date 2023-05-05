<?php

namespace App\Form;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\User;
use App\Entity\Fidelite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class FideliteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('valeur', null, [
            'constraints' => [
                new NotBlank([
                    'message' => 'La valeur est obligatoire.',
                ]),
                new Regex([
                    'pattern' => '/^\d{2}$/',
                    'message' => 'La valeur doit être composée de deux entiers sans espace.',
                ]),
            ],
        ])
        
            ->add('idUser', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Fidelite::class,
        ]);
    }
}
