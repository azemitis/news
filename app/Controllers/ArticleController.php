<?php
//
//namespace App\Controllers;
//
//use App\Cache;
//use App\Models\Article;
//use App\Models\User;
//use App\Views\View;
//use GuzzleHttp\Client;
//use GuzzleHttp\Exception\GuzzleException;
//use Twig\Environment;
//
//class ArticleController
//{
//    private Client $httpClient;
//
//    public function __construct(Client $httpClient)
//    {
//        $this->httpClient = $httpClient;
//    }
//
//    public function articles(Environment $twig, array $vars): View
//    {
//        try {
//            // Fetch articles
//            $url = 'https://jsonplaceholder.typicode.com/posts';
//            $response = $this->httpClient->get($url);
//            $body = $response->getBody()->getContents();
//            $data = json_decode($body, true);
//
//            // Fetch users
//            $userUrl = 'https://jsonplaceholder.typicode.com/users';
//            $userResponse = $this->httpClient->get($userUrl);
//            $userBody = $userResponse->getBody()->getContents();
//            $userData = json_decode($userBody, true);
//
//            // Create user objects and cache individually
//            $users = [];
//            foreach ($userData as $userItem) {
//                $userId = $userItem['id'];
//                $userName = $userItem['name'];
//                $userUsername = $userItem['username'];
//                $userEmail = $userItem['email'];
//
//                $userObject = new User($userId, $userName, $userUsername, $userEmail);
//                $users[$userId] = $userObject;
//            }
//
//            // Create article objects and cache individually
//            $articles = [];
//
//            // Check if there is cached data for each article
//            foreach ($data as $article) {
//                $id = $article['id'];
//                $cacheKey = 'article_' . $id;
//
//                if (Cache::has($cacheKey)) {
//                    $cachedArticle = Cache::get($cacheKey);
//                    $articles[$id] = $cachedArticle;
//                    // var_dump("Cached article (ID: $id) used.");
//                } else {
//                    $userId = $article['userId'];
//                    $title = $article['title'];
//                    $body = $article['body'];
//
//                    // Get user of the article by ID
//                    $user = $users[$userId];
//
//                    $articleObject = new Article($userId, $id, $title, $body, $user);
//
//                    Cache::remember($cacheKey, $articleObject, 20);
//                    $articles[$id] = $articleObject;
//                    // var_dump("API request made for article (ID: $id).");
//                }
//            }
//
//            // Get random images
//            $images = RandomImage::getRandomImages(count($articles));
//
//            // Render Twig template
//            return new View('Articles', [
//                'articles' => $articles,
//                'images' => $images,
//                'users' => $users
//            ]);
//        } catch (GuzzleException $exception) {
//            $errorMessage = 'Error fetching article data: ' . $exception->getMessage();
//
//            return new View('Error', ['message' => $errorMessage]);
//        }
//    }
//
//    public function article(Environment $twig, array $vars): View
//    {
//        $articleId = (int)$vars['id'];
//
//        try {
//            // Fetch articles and users
//            $articlesData = $this->articles($twig, $vars)->getData();
//            $articles = $articlesData['articles'];
//            $users = $articlesData['users'];
//
//            $article = null;
//            foreach ($articles as $item) {
//                if ($item->getId() == $articleId) {
//                    $article = $item;
//                    break;
//                }
//            }
//
//            // Check if there is a cached version of the article
//            $cacheKey = 'article_' . $articleId;
//            if (Cache::has($cacheKey)) {
//                $cachedArticle = Cache::get($cacheKey);
//                $article = $cachedArticle;
//                var_dump("Cached article (ID: $articleId) used.");
//            } else {
//                // Get random images
//                $images = RandomImage::getRandomImages(1);
//                $image = $images[0];
//
//                // Check if there is a cached version of the comments
//                $commentsCacheKey = 'comments_' . $articleId;
//                if (Cache::has($commentsCacheKey)) {
//                    $comments = Cache::get($commentsCacheKey);
//                    var_dump("Cached comments for article (ID: $articleId) used.");
//                } else {
//                    // Get comments for the article
//                    $comments = $this->homeController->getComments($articleId, $articles, $users);
//
//                    // Cache the comments
//                    Cache::remember($commentsCacheKey, $comments, 20);
//                    var_dump("API request made for comments of article (ID: $articleId).");
//                }
//
//                // Render Twig template
//                $viewData = [
//                    'article' => $article,
//                    'image' => $image,
//                    'comments' => $comments,
//                    'users' => $users
//                ];
//
//                // Cache the article
//                Cache::remember($cacheKey, $article, 20);
//
//                return new View('article', $viewData);
//            }
//
//            // Get random images
//            $images = RandomImage::getRandomImages(1);
//            $image = $images[0];
//
//            // Get comments for the article
//            $comments = $this->homeController->getComments($articleId, $articles, $users);
//
//            // Render Twig template
//            return new View('article', [
//                'article' => $article,
//                'image' => $image,
//                'comments' => $comments,
//                'users' => $users
//            ]);
//
//        } catch (GuzzleException $exception) {
//            $errorMessage = 'Error fetching article data: ' . $exception->getMessage();
//
//            return new View('Error', ['message' => $errorMessage]);
//        }
//    }
//}