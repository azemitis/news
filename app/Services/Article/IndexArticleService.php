<?php

namespace App\Services\Article;

use App\Repositories\ArticleRepository;
use App\Views\View;

class IndexArticleService
{
    private ArticleRepository $articleRepository;

    public function __construct()
    {
        $this->articleRepository = new ArticleRepository();
    }

    public function index(): View
    {
        try {
            $articlesData = $this->articleRepository->fetchArticlesData();
            $articles = $articlesData['articles'];
            $images = $articlesData['images'];
            $users = $articlesData['users'];

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