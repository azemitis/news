<?php declare(strict_types=1);

namespace App\Repositories;

use App\Utils\RandomImage;
use App\Models\Article;
use App\Models\User;
use PDO;

class LocalArticleRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function fetchArticlesData(): array
    {
        $query = "SELECT * FROM articles";
        $statement = $this->pdo->query($query);
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        $articles = [];

        foreach ($data as $article) {
            $userId = $article['user_id'];
            $title = $article['title'];
            $body = $article['body'];

            $user = new User($userId, $userName, $userUsername, $userEmail);
            $users[$userId] = $user;

            $articleObject = new Article($userId, $id, $title, $body, $user, $image);
            $articles[$id] = $articleObject;
        }

        $cachedData = [
            'articles' => $articles,
            'images' => $images,
            'users' => $users
        ];

        return $cachedData;
    }
}