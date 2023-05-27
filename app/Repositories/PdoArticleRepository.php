<?php declare(strict_types=1);

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

            foreach ($articlesData as $index => $articleData) {
                $userId = intval($articleData['user_id']);
                $id = intval($articleData['id']);
                $title = $articleData['title'];
                $body = $articleData['content'];

                if (!isset($users[$userId])) {
                    $users[$userId] = $this->fetchUser($userId);
                }

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
        } catch (\Exception $exception) {
            return [];
        }
    }

    private function fetchUser(int $userId): User
    {
        return new User(0, '', '', '');
    }
}
