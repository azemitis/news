<?php declare(strict_types=1);

namespace App\Services\Comments;

use App\Cache;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CommentService
{
    private $httpClient;

    public function __construct($httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getComments(int $articleId, array $articles, array $users): array
    {
        $cacheKey = 'comments_' . $articleId;

        if (Cache::has($cacheKey)) {
            $comments = Cache::get($cacheKey);
        } else {
            $url = "https://jsonplaceholder.typicode.com/comments?postId={$articleId}";

            try {
                $response = $this->httpClient->get($url);
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                $articleUsers = array_slice($users, 0, 10);

                $comments = [];
                foreach ($data as $comment) {
                    $id = $comment['id'];
                    $postId = $comment['postId'];
                    $name = $comment['name'];
                    $body = $comment['body'];

                    $user = $articleUsers[array_rand($articleUsers)];

                    $article = null;
                    foreach ($articles as $item) {
                        if ($item->getId() == $postId) {
                            $article = $item;
                            break;
                        }
                    }

                    $commentObject = new Comment($id, $postId, $name, $body, $article, $user);
                    $comments[] = $commentObject;
                }

                Cache::remember($cacheKey, $comments, 20);

            } catch (GuzzleException $exception) {
                $comments = [];
            }
        }

        return $comments;
    }

    public function getCommentsByUser(int $userId, array $articles, array $users): array
    {
        $url = "https://jsonplaceholder.typicode.com/comments?userId={$userId}";

        try {
            $response = $this->httpClient->get($url);
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            $comments = [];
            foreach ($data as $comment) {
                $id = $comment['id'];
                $postId = $comment['postId'];
                $name = $comment['name'];
                $body = $comment['body'];

                $article = null;
                foreach ($articles as $item) {
                    if ($item->getId() == $postId) {
                        $article = $item;
                        break;
                    }
                }

                $user = $users[$userId] ?? null;

                if ($article !== null && $user !== null) {
                    $commentObject = new Comment($id, $postId, $name, $body, $article, $user);
                    $comments[] = $commentObject;
                }
            }

            return $comments;
        } catch (GuzzleException $exception) {
            return [];
        }
    }
}