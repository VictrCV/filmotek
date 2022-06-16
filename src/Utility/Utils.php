<?php

namespace App\Utility;

use App\Entity\Series;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait Utils
 *
 * @codeCoverageIgnore
 * @package App\Controller
 */
trait Utils
{
    /**
     * @param int $code
     * @param string|null $message
     * @return Response
     */
    public static function errorMessage(int $code, ?string $message): Response
    {
        $errorMessage = [
            'code' => $code,
            'message' => $message
        ];
        return new JsonResponse($errorMessage, $code);
    }

    /**
     * Generates a response object with the message and corresponding code
     *
     * @param int $code HTTP status
     * @param object|array|null $messageBody HTTP body message
     * @param null|array $headers
     * @return Response Response object
     */
    public static function apiResponse(
        int               $code,
        object|array|null $messageBody = null,
        ?array            $headers = null
    ): Response
    {
        if (null === $messageBody) {
            $data = null;
        } else {
            $data = json_encode($messageBody);
        }

        $response = new Response($data, $code);
        $response->headers->add([
            'Access-Control-Allow-Origin' => '*',   // enable CORS
            'Access-Control-Allow-Credentials' => 'true', // Ajax CORS requests with Authorization header
        ]);
        if (!empty($headers)) {
            $response->headers->add($headers);
        }

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public static function rapidapiJsonToSeriesArray(string $jsonContent): ?array
    {
        $rapidapiSeries = json_decode($jsonContent, true)['results'];
        if (isset($rapidapiSeries)) {
            $series = [
                Series::API_ID_ATTR => $rapidapiSeries['id'],
                Series::TITLE_ATTR => $rapidapiSeries['titleText']['text'],
                Series::IS_FILM_ATTR => !$rapidapiSeries['titleType']['isSeries'],
                Series::SYNOPSIS_ATTR => $rapidapiSeries['plot']['plotText']['plainText'],
                Series::IMAGE_URL_ATTR => $rapidapiSeries['primaryImage']['url']
            ];

            foreach ($rapidapiSeries['genres']['genres'] as $genre){
                $genres[] = $genre['id'];
            }
            $series[Series::GENRES_ATTR] = $genres ?? null;

            return $series;
        }
        return null;
    }
}