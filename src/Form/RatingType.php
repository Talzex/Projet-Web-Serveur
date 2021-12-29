<?php

namespace App\Form;

use App\Entity\Rating;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RatingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('value', NumberType::class, [
                'label' => 'Note sur 10 :',
                'attr' => [
                    'max' => '10',
                    'min' => '0',
                    'type' => 'number'
                ]
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Votre commentaire :',
                'attr' => [
                    'placeholder' => 'Ce film est vraiment chouette... Je vous conseille de le regarder!',
                    ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rating::class,
        ]);
    }
}
