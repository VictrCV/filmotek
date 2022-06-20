<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @codeCoverageIgnore
 */
class TemporaryMarksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('season', IntegerType::class, [
                'label' => 'Season',
                'attr' => [
                    'min' => 1,
                    'class' => 'form-control'
                ]
            ])
            ->add('episode', IntegerType::class, [
                'label' => 'Episode',
                'attr' => [
                    'min' => 1,
                    'class' => 'form-control'
                ]
            ])
            ->add('time', TimeType::class, [
                'label' => 'Time',
                'widget' => 'single_text',
                'with_seconds' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'save',
                'attr' => [
                    'class' => 'btn btn-save material-icons mb-2'
                ],
            ]);
    }
}