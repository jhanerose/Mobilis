<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

http_response_code(403);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden | Mobilis</title>
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
            <h2>Access Denied</h2>
            <p>You don't have permission to access this page. Please contact your administrator if you believe this is an error.</p>
        </div>
    </section>

    <section class="auth-form-panel">
        <div class="form-wrap">
            <h3>403 Forbidden</h3>
            <p>The page you're trying to access requires special permissions.</p>
            
            <div style="margin-top: 24px;">
                <a href="/index.php" class="primary-btn full">Go to dashboard</a>
                <a href="/logout.php" class="ghost-link button-like" style="display: block; text-align: center; margin-top: 12px;">Sign out</a>
            </div>
        </div>
    </section>
</main>
</body>
</html>
