<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices'  => [
                    'Autre' => 'Autre',
                    'vetements' => 'vetements',
                    'telephones' => 'telephones',
                    'son' => 'son',
                    'Camera' => 'Camera',
                    
                ],
            ])
            ->add('categorie', ChoiceType::class, [
                'choices'  => [
                    'Habillement' => 'Habillement',
                    'Multimédias' => 'Multimédias',
                    'Autre' => 'Autre',
                ],
            ])
            ->add('nom')
            ->add('libelle')
            //->add('photo')
            ->add('photo', FileType::class, [
                'label' => 'Photo (jpg, png, gif)',
                'required' => false,
            ])
            ->add('ville', ChoiceType::class, [
                'choices'  => [
                    'Ariana' => 'Habillement',
                    'Béja' => 'Multimédias',
                    'Ben Arous' => 'Ben Arous',
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
                    'Sousse' => 'Sousse',
                    'Tunis' => 'Tunis',
                ],
            ])
            ->add('idUser')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
