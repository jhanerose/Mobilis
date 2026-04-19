<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

$code = (int) ($_GET['code'] ?? 500);
$title = (string) ($_GET['title'] ?? 'Server Error');
$message = (string) ($_GET['message'] ?? 'Something went wrong. Please try again later.');

$validCodes = [400, 403, 404, 500, 503];
if (!in_array($code, $validCodes, true)) {
    $code = 500;
}

http_response_code($code);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($code) ?> | <?= htmlspecialchars($title) ?> | Mobilis</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body class="login-body">
<main class="auth-split-shell">
    <section class="auth-brand-panel">
        <a href="/index.php" class="brand hero-brand">
            <img src="/assets/images/logo.png" alt="Mobilis logo" class="brand-logo">
        </a>
        <div class="hero-copy">
            <h2><?= htmlspecialchars($title) ?></h2>
            <p><?= htmlspecialchars($message) ?></p>
        </div>
    </section>

    <section class="auth-form-panel">
        <div class="form-wrap">
            <h3>Error <?= htmlspecialchars($code) ?></h3>
            <p>An error occurred while processing your request.</p>
            
            <div style="margin-top: 24px;">
                <a href="/index.php" class="primary-btn full">Go to dashboard</a>
                <a href="/" class="ghost-link button-like" style="display: block; text-align: center; margin-top: 12px;">Go to home</a>
            </div>
        </div>
    </section>
</main>
</body>
</html>
