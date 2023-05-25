<?php declare(strict_types=1);

namespace App\Repositories;

use App\Models\Article;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class IndexArticleRepository
{
    private Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getAllArticles(): array
    {
        // Fetch articles from the external API
        $url = 'https://jsonplaceholder.typicode.com/posts';
        $response = $this->httpClient->get($url);
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        // Transform API data into Article objects
        $articles = [];
        foreach ($data as $article) {
            // Create an Article object based on the API data
            $id = $article['id'];
            $userId = $article['userId'];
            $title = $article['title'];
            $body = $article['body'];

            $articleObject = new Article($id, $userId, $title, $body, $userId);

            $articles[] = $articleObject;
        }

        return $articles;
    }
}