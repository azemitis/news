<?php declare(strict_types=1);

namespace App\Controllers;

use App\Cache;
use App\Models\Article;
//use App\Models\Author;
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
            // Fetch article
            $url = 'https://jsonplaceholder.typicode.com/posts';

            $response = $this->httpClient->get($url);
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            // Create article object
            $articles = [];
            foreach ($data as $article) {
                $userId = $article['userId'];
                $id = $article['id'];
                $title = $article['title'];
                $body = $article['body'];

                $articleObject = new Article($userId, $id, $title, $body);
                $articles[] = $articleObject;
            }

            // Get random image
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

    public function article(Environment $twig, array $vars): View
    {
        $articleId = $vars['id'];

        try {
            // Fetch article
            $url = "https://jsonplaceholder.typicode.com/posts/{$articleId}";

            $response = $this->httpClient->get($url);
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            // Create article object
            $userId = $data['userId'];
            $id = $data['id'];
            $title = $data['title'];
            $body = $data['body'];

            $article = new Article($userId, $id, $title, $body);

            // Get random image
            $image = $this->getRandomImages(1)[0];

            // Render Twig template
            return new View('article', [
                'article' => $article,
                'image' => $image,
            ]);
        } catch (GuzzleException $exception) {
            $errorMessage = 'Error fetching article data: ' . $exception->getMessage();

            return new View('Error', ['message' => $errorMessage]);
        }
    }
}