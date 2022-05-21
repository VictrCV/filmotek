<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @codeCoverageIgnore
 */
class SearchFormType extends AbstractType
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
                'attr' => [
                    'placeholder' => 'Title',
                ]
            ])
            ->add('genres', ChoiceType::class, [
                'choices' => [
                    'Genre' => $this->getGenres(),
                ],
                'label' => false,
                'multiple' => true,
            ])
            ->add('search', SubmitType::class, [
                'label' => 'Search',
                'attr' => [
                    'class' => 'btn-sign-up-form'
                ],
            ]);
    }

    private function getGenres(): array
    {
        $response = $this->client->request(
            'GET',
            'https://data-imdb1.p.rapidapi.com/titles/utils/genres',
            [
                'headers' => [
                    'X-RapidAPI-Host' => 'data-imdb1.p.rapidapi.com',
                    'X-RapidAPI-Key' => '6eba30a07dmsh958077cd42aab13p17bf28jsnf44b61beabc9'
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