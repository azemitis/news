<?php declare(strict_types=1);

namespace App\Repositories;

use App\Cache;
use App\Utils\RandomImage;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use GuzzleHttp\Client;
use App\Services\Comments\CommentService;
use App\Controllers\HomeController;

class ShowArticleRepository
{
    private Client $httpClient;
    private HomeController $homeController;

    public function __construct(Client $httpClient, HomeController $homeController)
    {
        $this->httpClient = $httpClient;
        $this->homeController = $homeController;
    }

    public function fetchArticleData(int $articleId, array $articles, array $users): array    {
        $article = null;
        foreach ($articles as $item) {
            if ($item->getId() == $articleId) {
                $article = $item;
                break;
            }
        }

        // Check if there is a cached version of the article
        $cacheKey = 'article_' . $articleId;
        if (Cache::has($cacheKey)) {
            $cachedArticle = Cache::get($cacheKey);
            $article = $cachedArticle;
        } else {
            // Get random images
            $images = RandomImage::getRandomImages(1);
            $image = $images[0];

            $commentService = new CommentService($this->httpClient, $this->homeController);

            // Check if there is a cached version of the comments
            $commentsCacheKey = 'comments_' . $articleId;
            if (Cache::has($commentsCacheKey)) {
                $comments = Cache::get($commentsCacheKey);
            } else {
                $comments = $commentService->getComments($articleId, $articles, $users);

                // Cache comments
                Cache::remember($commentsCacheKey, $comments, 20);
            }

            // Cache the article
            $viewData = [
                'article' => $article,
                'image' => $image,
                'comments' => $comments,
                'users' => $users
            ];
            Cache::remember($cacheKey, $article, 20);

            return $viewData;
        }

        // Get random images
        $images = RandomImage::getRandomImages(1);
        $image = $images[0];

        // Create instance of CommentService
        $commentService = new CommentService($this->httpClient, $this->homeController);
        $comments = $commentService->getComments($articleId, $articles, $users);

        return [
            'article' => $article,
            'image' => $image,
            'comments' => $comments,
            'users' => $users
        ];
    }
}