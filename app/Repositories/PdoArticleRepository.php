<?php

namespace App\Repositories;

use App\Models\Article;
use App\Models\User;
use App\Utils\RandomImage;
use Doctrine\DBAL\DriverManager;

class PdoArticleRepository
//    implements ArticleRepositoryInterface
{
    private \Doctrine\DBAL\Connection $connection;

    public function __construct()
    {
        $connectionParams = [
            'dbname' => 'news',
            'user' => 'root',
            'password' => 'root',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        ];

        $this->connection = DriverManager::getConnection($connectionParams);
    }

    public function all(): array
    {
        try {
            $query = "SELECT * FROM tvnet";
            $statement = $this->connection->query($query);
            $articlesData = $statement->fetchAll();

            $articles = [];
            $users = [];
            $images = RandomImage::getRandomImages(count($articlesData));

            $userData = $this->getByUserId();
            foreach ($userData as $user) {
                $users[$user['id']] = new User($user['id'], $user['username'], $user['email'], $user['password']);
            }

            foreach ($articlesData as $index => $articleData) {
                $userId = intval($articleData['user_id']);
                $id = intval($articleData['id']);
                $title = $articleData['title'];
                $body = $articleData['content'];

                $user = $users[$userId];
                $image = $images[$index];

                $article = new Article($id, $userId, $title, $body, $user);
                $article->setImage($image);

                $articles[$id] = $article;
            }

            return [
                'articles' => $articles,
                'users' => $users,
                'images' => $images,
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getById(int $id): ?Article
    {
        return null;
    }

    private function getByUserId(): array
    {
        $query = "SELECT * FROM users";
        $statement = $this->connection->query($query);
        return $statement->fetchAll();
    }
}