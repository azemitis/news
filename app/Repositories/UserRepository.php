<?php


namespace App\Repositories;

use App\Models\User;
use Doctrine\DBAL\Connection;

class UserRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function findByUsername(string $username): ?User
    {
        $query = "SELECT * FROM users WHERE username = ?";
        $statement = $this->connection->executeQuery($query, [$username]);

        $userData = $statement->fetchAssociative();

        return $userData ? $this->createUserFromData($userData) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $query = "SELECT * FROM users WHERE email = ?";
        $statement = $this->connection->executeQuery($query, [$email]);

        $userData = $statement->fetchAssociative();

        return $userData ? $this->createUserFromData($userData) : null;
    }

    public function save(User $user): void
    {
        $query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $this->connection->executeQuery(
            $query,
            [$user->getUsername(), $user->getEmail(), $user->getPassword()]
        );
    }

    private function createUserFromData(array $data): User
    {
        return new User(
            (int)$data['id'],
            $data['username'],
            $data['email'],
            $data['password']
        );
    }
}
