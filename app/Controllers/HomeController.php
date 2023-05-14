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

    public function getRandomImages(int $count): array
    {
        $images = [];

        $sizes = ['400x500', '400x400', '500x400'];
        $colors = ['orange', 'cyan', 'green'];

        for ($i = 0; $i < $count; $i++) {
            $size = $sizes[array_rand($sizes)];
            $color = $colors[array_rand($colors)];

            $imageUrl = "https://placehold.co/{$size}/{$color}/white";
            $images[] = $imageUrl;
        }

        return $images;
    }

    public function articles(Environment $twig, array $vars)
    {
        $url = 'https://jsonplaceholder.typicode.com/posts';

        $response = file_get_contents($url);
        $data = json_decode($response, true);

        $articles = [];
        foreach ($data as $article) {
            $userId = $article['userId'];
            $id = $article['id'];
            $title = $article['title'];
            $body = $article['body'];

            $articleObject = new Article($userId, $id, $title, $body);
            $articles[] = $articleObject;
        }

        $images = $this->getRandomImages(count($articles));

        return new View('Articles', [
            'articles' => $articles,
            'images' => $images,
        ]);
    }
}