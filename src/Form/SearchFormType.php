<?php

namespace App\Form;

use App\Controller\AppController\SearchController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @codeCoverageIgnore
 */
class SearchFormType extends AbstractType implements EventSubscriberInterface
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Title',
                ]
            ])
            ->add('exact', CheckboxType::class, [
                'label' => 'Exact title',
                'required' => false
            ])
            ->add('genre', ChoiceType::class, [
                'choices' => $this->getGenres(),
                'label' => false,
                'required' => false,
                'placeholder' => 'Genre'
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'TV Series' => 'tvSeries',
                    'Movie' => 'movie',
                    'TV Mini-Series' => 'tvMiniSeries',
                    'TV Movie' => 'tvMovie'],
                'label' => false,
                'required' => true
            ])
            ->add('search', SubmitType::class, [
                'label' => 'Search',
                'attr' => [
                    'class' => 'btn-sign-up-form'
                ],
            ])
            ->addEventSubscriber($this);
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => 'ensureOneFieldIsSubmitted',
        ];
    }

    public function ensureOneFieldIsSubmitted(FormEvent $event)
    {
        $submittedData = $event->getData();

        if (!isset($submittedData['title']) && !isset($submittedData['genre'])) {
            throw new TransformationFailedException(
                'Set title and/or genre',
                0,
                null,
                'Set title and/or genre.'
            );
        }
    }

    private function getGenres(): array
    {
        $response = $this->client->request(
            'GET',
            SearchController::BASE_URL . '/titles/utils/genres',
            [
                'headers' => [
                    'X-RapidAPI-Host' => SearchController::RAPIDAPI_HOST,
                    'X-RapidAPI-Key' => SearchController::RAPIDAPI_KEY
                ]
            ]
        );

        $body = $response->toArray();
        $body = array_values($body)[0];
        array_shift($body);
        $genres = [];
        foreach ($body as $genre) {
            $genres[$genre] = $genre;
        }

        return $genres;
    }
}