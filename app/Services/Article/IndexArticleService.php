<?php

namespace App\Services\Article;

use App\Cache;
use App\Controllers\RandomImage;
use App\Models\Article;
use App\Models\User;
use GuzzleHttp\Client;
use App\Views\View;


class IndexArticleService
{
    private Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function articles(): View
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
            $images = RandomImage::getRandomImages(count($data));
            $imageIndex = 0;

            foreach ($data as $article) {
                $id = $article['id'];
                $userId = $article['userId'];
                $title = $article['title'];
                $body = $article['body'];

                // Get user of the article by ID
                $user = $users[$userId];

                // Get the next image from the random images list
                $image = $images[$imageIndex];
                $imageIndex++;

                $articleObject = new Article($userId, $id, $title, $body, $user, $image);
                $articles[] = $articleObject;
            }

            // Render Twig template
            return new View('Articles', [
                'articles' => $articles,
                'images' => $images,
                'users' => $users
            ]);
        } catch (\Exception $exception) {
            $errorMessage = 'Error fetching article data: ' . $exception->getMessage();
            return new View('Error', ['message' => $errorMessage]);
        }
    }
}