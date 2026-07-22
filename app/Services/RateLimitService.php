<?php

namespace App\Services;

class RateLimitService
{
    public function checkRateLimit($ip)
    {
        $maxAttempts = env('MAX_QUERY_ATTEMPTS', 5);
        $cooldownMinutes = env('QUERY_COOLDOWN_MINUTES', 1);

        $cacheFile = storage_path('app/rate_limit_' . md5($ip) . '.txt');

        if (!file_exists($cacheFile)) {
            file_put_contents($cacheFile, json_encode(['count' => 0, 'timestamp' => time()]));
            return true;
        }

        $data = json_decode(file_get_contents($cacheFile), true);

        $now = time();
        if ($now - $data['timestamp'] > $cooldownMinutes * 60) {
            file_put_contents($cacheFile, json_encode(['count' => 0, 'timestamp' => $now]));
            return true;
        }

        if ($data['count'] >= $maxAttempts) {
            return false;
        }

        $data['count']++;
        file_put_contents($cacheFile, json_encode($data));
        return true;
    }
}