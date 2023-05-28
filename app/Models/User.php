<?php declare(strict_types=1);

namespace App\Models;

class User
{
    private int $id;
    private string $name;
    private string $username;
    private string $email;
    private ?string $password;

    public function __construct(int $id, string $name, string $username, string $email, ?string $password = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }
}
