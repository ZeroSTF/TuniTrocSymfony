<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\DataTransformer\DataUriTransformer;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

use Symfony\Component\Form\Extension\Core\Type\NumberType;

use Symfony\Component\Form\Extension\Core\Type\TimeType;

class Post1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'titre',
                
                'attr' => [
                    'class' => 'form-control',
                 
    
                ] ,'constraints' => [
                    
                ]
            ])  
            ->add('contenu', TextType::class, [
                'label' => 'contenu',
                
                'attr' => [
                    'class' => 'form-control',
                 
    
                ] ,'constraints' => [
                    
                ]
            ])  
            ->add('date')
            
            ->add('id_user', TextType::class, [
                'label' => 'id-user',
                
                'attr' => [
                    'class' => 'form-control',
                 
    
                ] ,'constraints' => [
                    
                ]
            ])  
            ->add('likes', TextType::class, [
                'label' => 'Likes',
                
                'attr' => [
                    'class' => 'form-control',
                 
    
                ] ,'constraints' => [
                    
                ]
            ])  
            ->add('dislikes', NumberType::class, [
                'label' => 'dislike',
                
                'attr' => [
                    'class' => 'form-control',
                 
    
                ] ,'constraints' => [
                    
                ]
            ])  
            ->add('img' , FileType::class, [
                'label' => 'Product Image',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control-file'
                ],
                'label_attr' => [
                    'class' => 'form-control-label'
                ],'constraints' => [
                   
                ]
                    ]);}
       
    

    

       
    

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}

