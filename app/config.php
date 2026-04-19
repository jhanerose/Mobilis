<?php
declare(strict_types=1);

$firstNonEmpty = static function (array $keys): ?string {
    foreach ($keys as $key) {
        $value = getenv($key);
        if (is_string($value) && $value !== '') {
            return $value;
        }
    }

    return null;
};

$dbUrl = $firstNonEmpty(['MYSQL_URL', 'DATABASE_URL']);
$dbUrlParts = $dbUrl ? parse_url($dbUrl) : false;
$dbUrlPath = is_array($dbUrlParts) ? (string) ($dbUrlParts['path'] ?? '') : '';

return [
    'app_name' => 'Mobilis Vehicle Rental',
    'db' => [
        'host' => $firstNonEmpty(['MOBILIS_DB_HOST', 'MYSQLHOST'])
            ?? (is_array($dbUrlParts) ? (string) ($dbUrlParts['host'] ?? '') : '')
            ?: '127.0.0.1',
        'port' => $firstNonEmpty(['MOBILIS_DB_PORT', 'MYSQLPORT'])
            ?? (is_array($dbUrlParts) ? (string) ($dbUrlParts['port'] ?? '') : '')
            ?: '3306',
        'name' => $firstNonEmpty(['MOBILIS_DB_NAME', 'MYSQLDATABASE'])
            ?? ltrim($dbUrlPath, '/')
            ?: 'mobilis_db',
        'user' => $firstNonEmpty(['MOBILIS_DB_USER', 'MYSQLUSER'])
            ?? (is_array($dbUrlParts) ? (string) ($dbUrlParts['user'] ?? '') : '')
            ?: 'root',
        'pass' => $firstNonEmpty(['MOBILIS_DB_PASS', 'MYSQLPASSWORD'])
            ?? (is_array($dbUrlParts) ? (string) ($dbUrlParts['pass'] ?? '') : '')
            ?: '',
    ],
    'python_bin' => getenv('MOBILIS_PYTHON_BIN') ?: 'python3',
    'mileage_alert_threshold' => 80000,
];
