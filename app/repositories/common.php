<?php
declare(strict_types=1);

if (!function_exists('dbNow')) {
    function dbNow(): string
    {
        return date('Y-m-d');
    }
}

if (!function_exists('fallbackMetrics')) {
    function fallbackMetrics(): array
    {
        return [
            'total_fleet' => 48,
            'active_rentals' => 31,
            'revenue_today' => 28450.00,
            'utilization_rate' => 64,
        ];
    }
}
