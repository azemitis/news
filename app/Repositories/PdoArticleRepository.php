<?php declare(strict_types=1);

namespace App\Repositories;

use App\Models\Article;
use App\Models\User;
use App\Utils\RandomImage;
use mysqli;

class PdoArticleRepository
{
    private mysqli $connection;

    public function __construct()
    {
        $this->connection = new mysqli(
            'localhost',
            'root',
            'root',
            'tvnet'
        );
        if ($this->connection->connect_errno) {
            throw new \Exception('Failed to connect to MySQL: '
                . $this->connection->connect_error);
        }
    }

    public function fetchArticlesData(): array
    {
        try {
            $query = "SELECT * FROM articles";
            $statement = $this->connection->query($query);
            $articlesData = $statement->fetchAll(PDO::FETCH_ASSOC);

            $articles = [];
            $users = [];

            foreach ($articlesData as $articleData) {
                $userId = $articleData['user_id'];
                $id = $articleData['id'];
                $title = $articleData['title'];
                $body = $articleData['body'];

                if (!isset($users[$userId])) {
                    $users[$userId] = $this->fetchUser($userId);
                }

                $user = $users[$userId];

                $articles[$id] =
                    new Article($userId, $id, $title, $body, $user);
            }

            $images = RandomImage::getRandomImages(count($articles));
            $imageIndex = 0;

            foreach ($articles as $id => $article) {
                $article->setImage($images[$imageIndex]);
                $imageIndex++;
            }

            return [
                'articles' => $articles,
                'images' => $images,
                'users' => $users,
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'articles' => [],
                'images' => [],
                'users' => [],
            ];
        }
    }

    private function fetchUser(int $userId): User
    {
        $query = "SELECT * FROM users WHERE id = :userId";
        $statement = $this->connection->prepare($query);
        $statement->bindValue(':userId', $userId, PDO::PARAM_INT);
        $statement->execute();
        $userData = $statement->fetch(PDO::FETCH_ASSOC);

        $user = new User(
            $userData['id'],
            $userData['name'],
            $userData['username'],
            $userData['email']
        );

        return $user;
    }
}
