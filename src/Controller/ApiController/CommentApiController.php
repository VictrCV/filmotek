<?php

namespace App\Controller\ApiController;

use App\Entity\Comment;
use App\Entity\Series;
use App\Entity\User;
use App\Utility\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @package App\Controller\ApiController
 * @Route(path=CommentApiController::COMMENT_API_ROUTE, name="comment_api_")
 */
class CommentApiController extends AbstractController
{
    public const COMMENT_API_ROUTE = '/api/v1/comment';

    public const TEXT_REGEX = '/(.*\S)/';

    private const HEADER_CACHE_CONTROL = 'Cache-Control';
    private const HEADER_ALLOW = 'Allow';

    private EntityManagerInterface $entityManager;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }

    /**
     * @param Request $request
     * @return Response
     * @Route(path="", name="post", methods={"POST"})
     */
    public function postAction(Request $request): Response
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        if (!isset($data[Comment::TEXT_ATTR], $data[Comment::SERIES_ATTR], $data[Comment::USER_ATTR])) {
            return Utils::errorMessage(Response::HTTP_UNPROCESSABLE_ENTITY, "Missing data.");
        }

        if (!preg_match(self::TEXT_REGEX, $data[Comment::TEXT_ATTR])) {
            $badRequest = Utils::errorMessage(Response::HTTP_BAD_REQUEST, "Text must contain at least one non-whitespace character.");
        }

        $series = $this->entityManager
            ->getRepository(Series::class)
            ->find($data[Comment::SERIES_ATTR]);

        if (!isset($series)) {
            $badRequest = Utils::errorMessage(Response::HTTP_BAD_REQUEST, "Series does not exist.");
        }

        $user = $this->entityManager
            ->getRepository(User::class)
            ->find($data[Comment::USER_ATTR]);

        if (!isset($user)) {
            $badRequest = Utils::errorMessage(Response::HTTP_BAD_REQUEST, "User does not exist.");
        }

        if (isset($badRequest)) {
            return $badRequest;
        }

        $comment = new Comment();
        $comment->setText($data[Comment::TEXT_ATTR]);
        $comment->setSeries($series);
        $comment->setUser($user);

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return Utils::apiResponse(
            Response::HTTP_CREATED,
            [Comment::COMMENT_ATTR => $comment]
        );
    }

    /**
     * @return Response
     * @Route(path="", name="options", methods={"OPTIONS"})
     */
    public function optionsAction(): Response
    {
        $methods = ['OPTIONS', 'POST'];

        return new Response(
            null,
            Response::HTTP_NO_CONTENT,
            [
                self::HEADER_ALLOW => implode(', ', $methods),
                self::HEADER_CACHE_CONTROL => 'public, inmutable'
            ]
        );
    }
}