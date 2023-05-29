<?php declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Views\View;
use GuzzleHttp\Client;

class RegistrationService
{
    private Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function index()
    {
        return new View('Registration', ['message' => 'message']);

    }

    public function register(string $username, string $email, string $password): bool
    {
        if ($this->userRepository->findByUsername($username) || $this->userRepository->findByEmail($email)) {
            return false;
        }

        $user = new User(0, $username, $email, '');
        $user->setPassword(password_hash($password, PASSWORD_DEFAULT));

        $this->userRepository->save($user);

        return true;
    }
}