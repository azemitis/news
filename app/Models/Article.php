<?php declare(strict_types=1);

namespace App\Models;

class Article
{
    private int $userId;
    private int $id;
    private string $title;
    private string $body;
    private User $user;

    public function __construct(int $userId, int $id, string $title, string $body, User $user)
    {
        $this->userId = $userId;
        $this->id = $id;
        $this->title = $title;
        $this->body = $body;
        $this->user = $user;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}