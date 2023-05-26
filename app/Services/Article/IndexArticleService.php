<?php declare(strict_types=1);

namespace App\Services\Article;

use App\Repositories\IndexArticleRepository;
use App\Repositories\PdoArticleRepository;
use App\Views\View;

class IndexArticleService
{
    private $articleRepository;

    public function __construct()
    {
//        $this->articleRepository = new PdoArticleRepository();
        $this->articleRepository = new IndexArticleRepository();
    }

    public function index(): View
    {
        try {
            $articlesData = $this->articleRepository->all();
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