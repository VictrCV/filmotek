<?php

namespace App\Utility;

use Hateoas\HateoasBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait Utils
 *
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
            $hateoas = HateoasBuilder::create()->build();
            $data = $hateoas->serialize($messageBody, 'json');
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
}