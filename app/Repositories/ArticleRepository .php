<?php

namespace App\Repositories;

use App\Cache;
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
            $articles = Cache::get($cacheKey);
            var_dump("Cached articles used.");
        } else {
            try {
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
                    $id = $article['id'];
                    $userId = $article['userId'];
                    $title = $article['title'];
                    $body = $article['body'];

                    // Get user of the article by ID
                    $user = $users[$userId];

                    $articleObject = new Article($userId, $id, $title, $body, $user);
                    $articles[$id] = $articleObject;
                }

                // Cache the articles
                Cache::remember($cacheKey, $articles, 20);
                var_dump("API request made for articles.");

            } catch (\Exception $exception) {
                $articles = [];
            }
        }

        return $articles;
    }
}