<?php

namespace App\Domain\Contracts;

use App\Models\User;

interface AuthService
{
    public function loginWithPassword(string $email, string $password, string $scope = ''): array;
    public function refreshToken(string $refreshToken, string $scope = ''): array;
    public function logoutUser(User $user): void;
}
