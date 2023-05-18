<?php

namespace App\Services\Article;

use App\Cache;
use App\Models\Article;
use App\Models\User;
use App\Models\Comment;
use GuzzleHttp\Client;

class IndexArticleService
{
    private Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function execute(): array
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

            // Create user objects and cache individually
            $users = [];
            foreach ($userData as $userItem) {
                $userId = $userItem['id'];
                $userName = $userItem['name'];
                $userUsername = $userItem['username'];
                $userEmail = $userItem['email'];

                $userObject = new User($userId, $userName, $userUsername, $userEmail);
                $users[$userId] = $userObject;
            }

            // Create article objects and cache individually
            $articles = [];
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

                    $articleObject = new Article($userId, $id, $title, $body, $user);

                    Cache::remember($cacheKey, $articleObject, 20);
                    $articles[$id] = $articleObject;
                }
            }

            return ['articles' => $articles, 'users' => $users];
        } catch (\Exception $exception) {
            return ['error' => $exception->getMessage()];
        }
    }
}