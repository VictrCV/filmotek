<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @codeCoverageIgnore
 */
class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Add a comment...',
                    'class' => 'comment-input',
                ]
            ])
            ->add('comment', SubmitType::class, [
                'label' => 'Comment',
                'attr' => [
                    'class' => 'btn-form'
                ],
            ]);
    }
}