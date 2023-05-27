<?php declare(strict_types=1);

namespace App\Models;

class Article
{
    private int $id;
    private int $userId;
    private string $title;
    private string $body;
    private User $user;
    private ?string $image;

    public function __construct(int $id, int $userId, string $title, string $body, User $user, ?string $image = null)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->title = $title;
        $this->body = $body;
        $this->user = $user;
        $this->image = $image;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): User
    {
        $this->user = $user;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }
}