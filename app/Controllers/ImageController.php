<?php declare(strict_types=1);

namespace App\Controllers;

use App\Views\View;

class ImageController
{
    public function getRandomImages(int $count): array
    {
        $images = [];

        $sizes = ['400x400', '300x400', '400x300'];
        $colors = ['orange', 'cyan', 'green'];
        $texts = ['Hello from Riga', 'Hello from Latvia', 'Hello from Europe'];

        for ($i = 0; $i < $count; $i++) {
            $size = $sizes[array_rand($sizes)];
            $color = $colors[array_rand($colors)];
            $text = $texts[array_rand($texts)];

            $imageUrl = "https://placehold.co/{$size}/{$color}/white?text=" . urlencode($text);
            $images[] = $imageUrl;
        }

        return $images;
    }
}