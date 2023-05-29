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
use Doctrine\DBAL\DriverManager;

class UserService
{
    private Client $httpClient;
    private CommentService $commentService;
    private \Doctrine\DBAL\Connection $connection;

    public function __construct(Client $httpClient, CommentService $commentService)
    {
        $this->httpClient = $httpClient;
        $this->commentService = $commentService;

        $connectionParams = [
            'dbname' => 'news',
            'user' => 'root',
            'password' => 'root',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        ];

        $this->connection = DriverManager::getConnection($connectionParams);
    }

    public function user(int $userId): array
    {
        var_dump('Inside user() method'); // Add this line

        // Check if the user object is cached
        $cacheKey = 'user_' . $userId;
        if (Cache::has($cacheKey)) {
            $userObject = Cache::get($cacheKey);
            $users = [$userId => $userObject];
        } else {
            try {
                // Fetch user from local database
                $userData = $this->getByUserId($userId);
                var_dump($userData); // Dump user data to check

                // Create user object
                $userObject = new User($userId, $userData['username'], $userData['email'], $userData['password']);
                var_dump($userObject); // Dump user object to check

                // Cache the user object
                Cache::remember($cacheKey, $userObject, 20);

                $users = [$userId => $userObject];

            } catch (\Exception $exception) {
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
        $comments = $this->commentService->getCommentsByUser($userId, $articles, $users);

        return [
            'author' => $userObject,
            'articles' => $articles,
            'comments' => $comments,
            'users' => $users
        ];
    }

    private function getByUserId(int $userId): ?array
    {
        $query = "SELECT * FROM users WHERE id = ?";
        $statement = $this->connection->executeQuery($query, [$userId]);

        return $statement->fetchAssociative() ?: null;
    }



}