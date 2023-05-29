<?php declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;

class RegistrationService
{
    public function register(string $username, string $email, string $password): bool
    {
        if (User::findByUsername($username) || User::findByEmail($email)) {
            return false;
        }

        $user = new User(0, '', $username, $email);

        $user->setPassword(password_hash($password, PASSWORD_DEFAULT));

        $user->save();

        return true;
    }
}