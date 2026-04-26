<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';
requireAuth(['admin', 'staff']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . baseUrl() . '/Staff/bookings.php');
    exit;
}

$rentalId = (int) ($_POST['id'] ?? 0);
$action = trim((string) ($_POST['action'] ?? ''));
$redirect = trim((string) ($_POST['redirect'] ?? baseUrl() . '/Staff/bookings.php'));

$target = baseUrl() . '/Staff/bookings.php';
if ($redirect !== '' && str_starts_with($redirect, baseUrl() . '/Staff/bookings.php')) {
    $target = $redirect;
} else if ($redirect !== '' && str_starts_with($redirect, 'bookings.php')) {
    $target = baseUrl() . '/Staff/bookings.php';
}

$result = applyBookingAction($rentalId, $action);
$notice = ($result['ok'] ?? false) ? 'action_success' : 'action_error';

$separator = str_contains($target, '?') ? '&' : '?';
header('Location: ' . $target . $separator . 'notice=' . urlencode($notice));
exit;
