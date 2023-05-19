<?php declare(strict_types=1);

namespace App\Services\User;

use App\Cache;
use App\Controllers\HomeController;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use App\Services\Comments\CommentService;
use GuzzleHttp\Client;
use App\Views\View;
use GuzzleHttp\Exception\GuzzleException;

class UserService
{
    private Client $httpClient;
    private HomeController $homeController;

    public function __construct(Client $httpClient, HomeController $homeController)
    {
        $this->httpClient = $httpClient;
        $this->homeController = $homeController;
    }

    public function user(int $userId): View
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

                return new View('Error', ['message' => $errorMessage]);
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
        $commentService = new CommentService($this->httpClient, $this->homeController);
        $comments = $commentService->getCommentsByUser($userId, $articles, $users);

        // Render Twig template
        return new View('User', [
            'author' => $userObject,
            'articles' => $articles,
            'comments' => $comments,
            'users' => $users
        ]);
    }
}