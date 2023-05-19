<?php declare(strict_types=1);

namespace App\Repositories;

use App\Cache;
use App\Controllers\RandomImage;
use App\Models\Article;
use App\Models\User;
use GuzzleHttp\Client;

class ArticleRepository
{
    private Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getArticles(): array
    {
        $cacheKey = 'articles';

        if (Cache::has($cacheKey)) {
            $cachedData = Cache::get($cacheKey);
            $articles = $cachedData['articles'];
            $images = $cachedData['images'];
            $users = $cachedData['users'];
        } else {
            $articles = [];
            $images = [];
            $users = [];

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

            // Create user objects and cache individually
            foreach ($userData as $userItem) {
                $userId = $userItem['id'];
                $userName = $userItem['name'];
                $userUsername = $userItem['username'];
                $userEmail = $userItem['email'];

                $userObject = new User($userId, $userName, $userUsername, $userEmail);
                $users[$userId] = $userObject;
            }

            // Create article objects and cache individually
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

                    // Get user of the article by ID
                    $user = $users[$userId];

                    // Get the next image from the random images list
                    $image = $images[$imageIndex];
                    $imageIndex++;

                    $articleObject = new Article($userId, $id, $title, $body, $user, $image);

                    Cache::remember($cacheKey, $articleObject, 20);
                    $articles[$id] = $articleObject;
                }
            }

            // Cache the articles, images, and users
            $cachedData = [
                'articles' => $articles,
                'images' => $images,
                'users' => $users
            ];
            Cache::remember($cacheKey, $cachedData, 20);
        }

        return [
            'articles' => $articles,
            'images' => $images,
            'users' => $users
        ];
    }
}