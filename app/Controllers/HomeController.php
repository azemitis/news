<?php declare(strict_types=1);

namespace App\Controllers;

use App\Cache;
use App\Models\Article;
use App\Models\Author;
use App\Models\Comment;
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
    public function getRandomImages(int $count): array
    {
        $images = [];

        $sizes = ['400x200', '400x300', '300x200'];
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

            // Fetch authors
            $authorUrl = 'https://jsonplaceholder.typicode.com/users';
            $authorResponse = $this->httpClient->get($authorUrl);
            $authorBody = $authorResponse->getBody()->getContents();
            $authorData = json_decode($authorBody, true);

            // Create author objects
            $authors = [];
            foreach ($authorData as $authorItem) {
                $authorId = $authorItem['id'];
                $authorName = $authorItem['name'];
                $authorUsername = $authorItem['username'];
                $authorEmail = $authorItem['email'];

                $authorObject = new Author($authorId, $authorName, $authorUsername, $authorEmail);
                $authors[$authorId] = $authorObject;
            }

            // Create article objects
            $articles = [];
            foreach ($data as $article) {
                $userId = $article['userId'];
                $id = $article['id'];
                $title = $article['title'];
                $body = $article['body'];

                // Get author of the article by ID
                $author = $authors[$userId];

                $articleObject = new Article($userId, $id, $title, $body, $author);
                $articles[] = $articleObject;
            }

            // Get random images
            $images = $this->getRandomImages(count($articles));

            // Render Twig template
            return new View('Articles', [
                'articles' => $articles,
                'images' => $images,
            ]);
        } catch (GuzzleException $exception) {
            $errorMessage = 'Error fetching article data: ' . $exception->getMessage();

            return new View('Error', ['message' => $errorMessage]);
        }
    }

    public function getComments(int $articleId): array
    {
        $url = "https://jsonplaceholder.typicode.com/comments?postId={$articleId}";

        try {
            $response = $this->httpClient->get($url);
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            $comments = [];
            foreach ($data as $comment) {
                $id = $comment['id'];
                $postId = $comment['postId'];
                $name = $comment['name'];
                $email = $comment['email'];
                $body = $comment['body'];

                $commentObject = new Comment($id, $postId, $name, $email, $body);
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

            $article = null;
            foreach ($articles as $item) {
                if ($item->getId() == $articleId) {
                    $article = $item;
                    break;
                }
            }

            // Get random image
            $image = $this->getRandomImages(1)[0];

            // Get comments for the article
            $comments = $this->getComments($articleId);

            // Render Twig template
            return new View('article', [
                'article' => $article,
                'image' => $image,
                'comments' => $comments,
            ]);
        } catch (GuzzleException $exception) {
            $errorMessage = 'Error fetching article data: ' . $exception->getMessage();

            return new View('Error', ['message' => $errorMessage]);
        }
    }
}