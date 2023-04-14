<?php

namespace App\Form;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('description')
            ->add('dateD')
            ->add('dateF')
        ;
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
