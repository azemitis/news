<?php declare(strict_types=1);

namespace App\Services\User;

use App\Cache;
use App\Models\Article;
use App\Models\User;
use App\Services\Comments\CommentService;
use App\Views\View;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Twig\Environment;

class UserService
{
    private Client $httpClient;

    private CommentService $commentService;

    public function __construct(Client $httpClient, CommentService $commentService)
    {
        $this->httpClient = $httpClient;
        $this->commentService = $commentService;
    }

    public function user(int $userId): array

    {
        // Check if the user object is cached
        $cacheKey = 'user_' . $userId;
        if (Cache::has($cacheKey)) {
            $userObject = Cache::get($cacheKey);
            $users = [$userId => $userObject];
        } else {
            try {
                // Fetch user
                $userUrl = "https://jsonplaceholder.typicode.com/users/{$userId}";
                $userResponse = $this->httpClient->get($userUrl);
                $userBody = $userResponse->getBody()->getContents();
                $userData = json_decode($userBody, true);

                // Create user object
                $userName = $userData['name'];
                $userUsername = $userData['username'];
                $userEmail = $userData['email'];

                $userObject = new User($userId, $userName, $userUsername, $userEmail);

                // Cache the user object
                Cache::remember($cacheKey, $userObject, 20);

                $users = [$userId => $userObject];

            } catch (GuzzleException $exception) {
                $errorMessage = 'Error fetching user data: ' . $exception->getMessage();

                return ['error' => $errorMessage];
            }
        }

        // Fetch articles
        $url = 'https://jsonplaceholder.typicode.com/posts';
        $response = $this->httpClient->get($url);
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        // Create article objects for the user
        $articles = [];
        foreach ($data as $article) {
            if ($article['userId'] === $userId) {
                $id = $article['id'];
                $title = $article['title'];
                $body = $article['body'];

                $articleObject = new Article($userId, $id, $title, $body, $userObject);
                $articles[] = $articleObject;
            }
        }

        // Fetch comments for the user
        $commentService = new CommentService($this->httpClient);
        $comments = $this->commentService->getCommentsByUser($userId, $articles, $users);

        return [
            'author' => $userObject,
            'articles' => $articles,
            'comments' => $comments,
            'users' => $users
        ];
    }
}