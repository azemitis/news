<?php declare(strict_types=1);

namespace App\Repositories;

use App\Models\Article;

interface ArticleRepositoryInterface
{
    public function all(): array;
    public function getById(int $id): ?Article;
    public function getByUserId(int $userId): array;
}