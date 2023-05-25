<?php declare(strict_types=1);

namespace App\Repositories;

use App\Cache;
use App\Utils\RandomImage;
use App\Models\Article;
use App\Models\User;
use GuzzleHttp\Client;

class IndexArticleRepository
{
    private Client $httpClient;
    private const API_URL = 'https://jsonplaceholder.typicode.com';

    public function __construct()
    {
        $this->httpClient = new Client();
    }

    public function fetchArticlesData(): array
    {
        $cacheKey = 'articles';

        if (Cache::has($cacheKey)) {
            $cachedData = Cache::get($cacheKey);
            return $cachedData;
        }

        $response = $this->httpClient->get(self::API_URL . '/posts');
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        $userResponse = $this->httpClient->get(self::API_URL . '/users');
        $userBody = $userResponse->getBody()->getContents();
        $userData = json_decode($userBody, true);

        $users = [];
        foreach ($userData as $userItem) {
            $userId = $userItem['id'];
            $userName = $userItem['name'];
            $userUsername = $userItem['username'];
            $userEmail = $userItem['email'];

            $userObject = new User($userId, $userName, $userUsername, $userEmail);
            $users[$userId] = $userObject;
        }

        $articles = [];
        $images = RandomImage::getRandomImages(count($data));
        $imageIndex = 0;

        foreach ($data as $article) {
            $id = $article['id'];
            $cacheKey = 'article_' . $id;

            if (Cache::has($cacheKey)) {
                $cachedArticle = Cache::get($cacheKey);
                $articles[$id] = $cachedArticle;
            } else {
                $userId = $article['userId'];
                $title = $article['title'];
                $body = $article['body'];

                $user = $users[$userId];

                $image = $images[$imageIndex];
                $imageIndex++;

                $articleObject = new Article($userId, $id, $title, $body, $user, $image);

                Cache::remember($cacheKey, $articleObject, 20);
                $articles[$id] = $articleObject;
            }
        }

        $cachedData = [
            'articles' => $articles,
            'images' => $images,
            'users' => $users
        ];
        Cache::remember($cacheKey, $cachedData, 20);

        return $cachedData;
    }
}