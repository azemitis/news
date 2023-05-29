<?php declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;

class LoginService
{
    public function login(string $username, string $password)
    {
        $user = User::findByUsername($username);

        if ($user && $user->verifyPassword($password)) {
            return true;
        }

        return false;
    }
}