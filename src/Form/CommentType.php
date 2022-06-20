<?php

namespace App\Form;

use App\Controller\ApiController\CommentApiController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @codeCoverageIgnore
 */
class CommentType extends AbstractType implements EventSubscriberInterface
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
            ])
            ->addEventSubscriber($this);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::SUBMIT => 'textValidation',
        ];
    }

    public function textValidation(FormEvent $event)
    {
        $submittedData = $event->getData();

        if (!preg_match(CommentApiController::TEXT_REGEX, $submittedData['text'])) {
            throw new TransformationFailedException(
                'The comment must contain at least one non-whitespace character',
                0,
                null,
                'The comment must contain at least one non-whitespace character'
            );
        }
    }
}