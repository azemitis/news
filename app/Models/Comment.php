<?php declare(strict_types=1);

namespace App\Models;

class Comment
{
    private int $id;
    private int $postId;
    private string $name;
    private string $email;
    private string $body;

    public function __construct(int $id, int $postId, string $name, string $email, string $body)
    {
        $this->id = $id;
        $this->postId = $postId;
        $this->name = $name;
        $this->email = $email;
        $this->body = $body;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPostId(): int
    {
        return $this->postId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}