<?php declare(strict_types=1);

namespace App\Models;

class Comment
{
    private int $id;
    private int $postId;
    private string $name;
    private string $body;
    private Article $article;
    private User $user;

    public function __construct(
        int $id,
        int $postId,
        string $name,
        string $body,
        Article $article,
        User $user
    ) {
        $this->id = $id;
        $this->postId = $postId;
        $this->name = $name;
        $this->body = $body;
        $this->article = $article;
        $this->user = $user;
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

//    public function getEmail(): string
//    {
//        return $this->email;
//    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getArticle(): Article
    {
        return $this->article;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}