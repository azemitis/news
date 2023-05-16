<?php declare(strict_types=1);

namespace App;

use Carbon\Carbon;

class Cache
{
    public static function remember(string $key, $data, int $ttl): void
    {
        $cacheFile = self::getCacheFilePath($key);
        self::forget($key);

        file_put_contents($cacheFile, json_encode([
            'expires_at' => Carbon::now()->addSeconds($ttl),
            'content' => serialize($data)
        ]));
    }

    public static function forget(string $key): void
    {
        $cacheFile = self::getCacheFilePath($key);

        if (file_exists($cacheFile)) {
            $content = json_decode(file_get_contents($cacheFile));
            $expiresAt = Carbon::parse($content->expires_at);

            if (Carbon::now() > $expiresAt) {
                unlink($cacheFile);
            }
        }
    }

    public static function get(string $key)
    {
        if (!self::has($key)) {
            return null;
        }

        $cacheFile = self::getCacheFilePath($key);
        $content = json_decode(file_get_contents($cacheFile));

        return unserialize($content->content);
    }

    public static function has(string $key): bool
    {
        $cacheFile = self::getCacheFilePath($key);

        if (!file_exists($cacheFile)) {
            return false;
        }

        $content = json_decode(file_get_contents($cacheFile));

        return Carbon::now() < Carbon::parse($content->expires_at);
    }

    private static function getCacheFilePath(string $key): string
    {
        return __DIR__ . '/../cache/' . $key . '.json';
    }
}