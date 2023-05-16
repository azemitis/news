<?php declare(strict_types=1);

namespace App\Controllers;

use App\Cache;
use App\Models\Article;
use App\Models\User;
use App\Models\Comment;
use App\Views\View;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Twig\Environment;

class HomeController
{
    private Client $httpClient;
//    private array $users = [];

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getRandomImages(int $count): array
    {
        $images = [];

        $sizes = ['400x400', '300x400', '400x300'];
        $colors = ['orange', 'cyan', 'green'];
        $texts = ['Hello from Riga', 'Hello from Latvia', 'Hello from Europe'];

        for ($i = 0; $i < $count; $i++) {
            $size = $sizes[array_rand($sizes)];
            $color = $colors[array_rand($colors)];
            $text = $texts[array_rand($texts)];

            $imageUrl = "https://placehold.co/{$size}/{$color}/white?text=" . urlencode($text);
            $images[] = $imageUrl;
        }

        return $images;
    }

    public function articles(Environment $twig, array $vars): View
    {
        try {
            // Fetch articles
            $url = 'https://jsonplaceholder.typicode.com/posts';
            $response = $this->httpClient->get($url);
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            // Fetch users
            $userUrl = 'https://jsonplaceholder.typicode.com/users';
            $userResponse = $this->httpClient->get($userUrl);
            $userBody = $userResponse->getBody()->getContents();
            $userData = json_decode($userBody, true);

            // Create user objects
            $users = [];
            foreach ($userData as $userItem) {
                $userId = $userItem['id'];
                $userName = $userItem['name'];
                $userUsername = $userItem['username'];
                $userEmail = $userItem['email'];

                $userObject = new User($userId, $userName, $userUsername, $userEmail);
                $users[$userId] = $userObject;
            }

            // Create article objects
            $articles = [];
            foreach ($data as $article) {
                $userId = $article['userId'];
                $id = $article['id'];
                $title = $article['title'];
                $body = $article['body'];

                // Get user of the article by ID
                $user = $users[$userId];

                $articleObject = new Article($userId, $id, $title, $body, $user);
                $articles[] = $articleObject;
            }

            // Get random images
            $images = $this->getRandomImages(count($articles));

            // Render Twig template
            return new View('Articles', [
                'articles' => $articles,
                'images' => $images,
                'users' => $users
            ]);
        } catch (GuzzleException $exception) {
            $errorMessage = 'Error fetching article data: ' . $exception->getMessage();

            return new View('Error', ['message' => $errorMessage]);
        }
    }

    public function getComments(int $articleId, array $articles, array $users): array
    {
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

            return $comments;
        } catch (GuzzleException $exception) {
            return [];
        }
    }

    public function article(Environment $twig, array $vars): View
    {
        $articleId = (int) $vars['id'];

        try {
            $articles = $this->articles($twig, $vars)->getData()['articles'];
            $users = $this->articles($twig, $vars)->getData()['users'];

            $article = null;
            foreach ($articles as $item) {
                if ($item->getId() == $articleId) {
                    $article = $item;
                    break;
                }
            }

            // Get random images
            $images = $this->getRandomImages(1);
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

    public function user(Environment $twig, array $vars): View
    {
        $userId = (int) $vars['id'];

        try {
            // Fetch user
            $userUrl = "https://jsonplaceholder.typicode.com/users/{$userId}";
            $userResponse = $this->httpClient->get($userUrl);
            $userBody = $userResponse->getBody()->getContents();
            $userData = json_decode($userBody, true);

            // Create user object
            $userId = $userData['id'];
            $userName = $userData['name'];
            $userUsername = $userData['username'];
            $userEmail = $userData['email'];

            $userObject = new User($userId, $userName, $userUsername, $userEmail);

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
            $comments = $this->getCommentsByUser($userId, $articles, $this->articles($twig, $vars)->getData()['users']);

            // Render Twig template
            return new View('User', [
                'author' => $userObject,
                'articles' => $articles,
                'comments' => $comments,
                'users' => $this->articles($twig, $vars)->getData()['users']
            ]);

        } catch (GuzzleException $exception) {
            $errorMessage = 'Error fetching user data: ' . $exception->getMessage();

            return new View('Error', ['message' => $errorMessage]);
        }
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

                $article = $articles[$postId] ?? null;
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