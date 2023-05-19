<?php declare(strict_types=1);

namespace App\Controllers;

use App\Cache;
use App\Controllers\RandomImage;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use App\Services\Article\IndexArticleService;
use App\Views\View;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Twig\Environment;

class HomeController
{
    private Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function index(): View
    {
        try {
            $service = new IndexArticleService($this->httpClient);
            return $service->index();
        } catch (GuzzleException $exception) {
            $errorMessage = 'Error fetching article data: ' . $exception->getMessage();
            return new View('Error', ['message' => $errorMessage]);
        }
    }

    public function show(Environment $twig, array $vars): View
    {
        $articleId = (int)$vars['id'];

        try {
            // Fetch articles and users
            $articlesData = $this->index($twig, $vars)->getData();
            $articles = $articlesData['articles'];
            $users = $articlesData['users'];

            $article = null;
            foreach ($articles as $item) {
                if ($item->getId() == $articleId) {
                    $article = $item;
                    break;
                }
            }

            // Check if there is a cached version of the article
            $cacheKey = 'article_' . $articleId;
            if (Cache::has($cacheKey)) {
                $cachedArticle = Cache::get($cacheKey);
                $article = $cachedArticle;
//                var_dump("Cached article (ID: $articleId) used.");
            } else {
                // Get random images
                $images = RandomImage::getRandomImages(1);
                $image = $images[0];

                // Check if there is a cached version of the comments
                $commentsCacheKey = 'comments_' . $articleId;
                if (Cache::has($commentsCacheKey)) {
                    $comments = Cache::get($commentsCacheKey);
//                    var_dump("Cached comments for article (ID: $articleId) used.");
                } else {
                    // Get comments for the article
                    $comments = $this->getComments($articleId, $articles, $users);

                    // Cache the comments
                    Cache::remember($commentsCacheKey, $comments, 20);
//                    var_dump("API request made for comments of article (ID: $articleId).");
                }

                // Cache the article
                $viewData = [
                    'article' => $article,
                    'image' => $image,
                    'comments' => $comments,
                    'users' => $users
                ];
                Cache::remember($cacheKey, $article, 20);

                return new View('article', $viewData);
            }

            // Get random images
            $images = RandomImage::getRandomImages(1);
            $image = $images[0];

            // Get comments for the article
            $comments = $this->getComments($articleId, $articles, $users);

            // Render Twig template
            return new View('article', [
                'article' => $article,
                'image' => $image,
                'comments' => $comments,
                'users' => $users
            ]);

        } catch (GuzzleException $exception) {
            $errorMessage = 'Error fetching article data: ' . $exception->getMessage();

            return new View('Error', ['message' => $errorMessage]);
        }
    }

    public function getComments(int $articleId, array $articles, array $users): array
    {
        $cacheKey = 'comments_' . $articleId;

        if (Cache::has($cacheKey)) {
            $comments = Cache::get($cacheKey);
//            var_dump("Cached comments for article (ID: $articleId) used.");
        } else {
            $url = "https://jsonplaceholder.typicode.com/comments?postId={$articleId}";

            try {
                $response = $this->httpClient->get($url);
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                // Get 10 users who created the articles for replacement
                $articleUsers = array_slice($users, 0, 10);

                $comments = [];
                foreach ($data as $comment) {
                    $id = $comment['id'];
                    $postId = $comment['postId'];
                    $name = $comment['name'];
                    $body = $comment['body'];

                    // Randomly select a user from the 10 article users
                    $user = $articleUsers[array_rand($articleUsers)];

                    // Find the article with the matching ID
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

                // Cache the comments
                Cache::remember($cacheKey, $comments, 20);
//                var_dump("API request made for comments of article (ID: $articleId).");

            } catch (GuzzleException $exception) {
                $comments = [];
            }
        }

        return $comments;
    }

    public function user(Environment $twig, array $vars): View
    {
        $userId = (int) $vars['id'];

        // Check if the user object is cached
        $cacheKey = 'user_' . $userId;
        if (Cache::has($cacheKey)) {
            $userObject = Cache::get($cacheKey);
            $users = [$userId => $userObject];
//            var_dump("Cached user (ID: $userId) used.");
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
//                var_dump("API request made for user (ID: $userId).");

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
        $comments = $this->getCommentsByUser($userId, $articles, $users);

        // Render Twig template
        return new View('User', [
            'author' => $userObject,
            'articles' => $articles,
            'comments' => $comments,
            'users' => $users
        ]);
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