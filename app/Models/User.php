<?php declare(strict_types=1);

namespace App\Models;

class User
{
    private int $id;
    private string $name;
    private string $username;
    private string $email;

    public function __construct(int $id, string $name, string $username, string $email)
    {

        $this->id = $id;
        $this->name = $name;
        $this->username = $username;
        $this->email = $email;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}