<?php

namespace App\Services\Article;

use App\Repositories\IndexArticleRepository;
use App\Repositories\LocalArticleRepository;
use App\Views\View;
use PDO;

class IndexArticleService
{
    private $articleRepository;

    public function __construct($repository = null)
    {
        if ($repository instanceof IndexArticleRepository || $repository instanceof LocalArticleRepository) {
            $this->articleRepository = $repository;
        } else {
            $this->articleRepository = new IndexArticleRepository();
        }
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