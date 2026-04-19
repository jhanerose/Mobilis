<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';
requireAuth(['admin']);

$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $requestId = (int) ($_POST['request_id'] ?? 0);

    if ($action === 'reset_password' && $requestId > 0) {
        $newPassword = trim((string) ($_POST['new_password'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));

        if ($newPassword === '' || strlen($newPassword) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        } else {
            $resetRequests = getPasswordResetRequests(1);
            $request = null;
            foreach ($resetRequests as $r) {
                if ((int) $r['request_id'] === $requestId) {
                    $request = $r;
                    break;
                }
            }

            if ($request === null) {
                $errors[] = 'Password reset request not found.';
            } else {
                $user = findUserByEmail($request['email']);
                if ($user === null) {
                    $errors[] = 'User not found for this email address.';
                } else {
                    if (resetUserPassword((int) $user['user_id'], $newPassword)) {
                        updatePasswordResetRequestStatus($requestId, 'completed');
                        $success = 'Password reset successfully for ' . htmlspecialchars($request['email']);
                    } else {
                        $errors[] = 'Failed to reset password. Please try again.';
                    }
                }
            }
        }
    } elseif ($action === 'reject_request' && $requestId > 0) {
        updatePasswordResetRequestStatus($requestId, 'rejected');
        $success = 'Password reset request rejected.';
    }
}

$contactMessages = getAdminContactMessages(30);
$resetRequests = getPasswordResetRequests(30);

viewBegin('app', appLayoutData('Support inbox', 'support', ['role' => 'admin']));
?>
<section class="page-content-head">
    <h3>All support requests</h3>
    <?php if ($success !== ''): ?>
        <div class="alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($errors !== []): ?>
        <div class="alert-error">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="content-grid split-grid">
    <article class="card">
        <div class="card-header">
            <h4>Contact admin submissions</h4>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($contactMessages as $msg): ?>
                    <tr>
                        <td>#<?= (int) $msg['message_id'] ?></td>
                        <td><?= htmlspecialchars((string) $msg['full_name']) ?></td>
                        <td><?= htmlspecialchars((string) $msg['email']) ?></td>
                        <td><?= htmlspecialchars((string) $msg['subject']) ?></td>
                        <td><span class="pill support-status-<?= htmlspecialchars((string) $msg['status']) ?>"><?= htmlspecialchars(ucfirst((string) $msg['status'])) ?></span></td>
                        <td><?= htmlspecialchars((string) $msg['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="card">
        <div class="card-header">
            <h4>Password reset requests</h4>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>License no.</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($resetRequests as $request): ?>
                    <tr>
                        <td>#<?= (int) $request['request_id'] ?></td>
                        <td><?= htmlspecialchars((string) $request['email']) ?></td>
                        <td><?= htmlspecialchars((string) ($request['license_number'] ?? 'N/A')) ?></td>
                        <td><?= htmlspecialchars((string) ($request['reason'] ?? '-')) ?></td>
                        <td><span class="pill support-status-<?= htmlspecialchars((string) $request['status']) ?>"><?= htmlspecialchars(ucfirst((string) $request['status'])) ?></span></td>
                        <td><?= htmlspecialchars((string) $request['created_at']) ?></td>
                        <td>
                            <?php if ($request['status'] === 'pending'): ?>
                                <button type="button" class="ghost-btn" onclick="showResetModal(<?= (int) $request['request_id'] ?>, '<?= htmlspecialchars((string) $request['email']) ?>')">Reset</button>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="action" value="reject_request">
                                    <input type="hidden" name="request_id" value="<?= (int) $request['request_id'] ?>">
                                    <button type="submit" class="ghost-btn text-error">Reject</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted"><?= htmlspecialchars(ucfirst((string) $request['status'])) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<!-- Password Reset Modal -->
<div id="resetModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h4>Reset Password</h4>
            <button type="button" class="modal-close" onclick="hideResetModal()">×</button>
        </div>
        <form method="post" class="modal-body">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="request_id" id="modalRequestId">
            <input type="hidden" name="email" id="modalEmail">
            
            <p>Reset password for: <strong id="modalEmailDisplay"></strong></p>
            
            <label for="new_password">New password
                <input id="new_password" type="password" name="new_password" required minlength="6" placeholder="At least 6 characters">
            </label>
            
            <div class="modal-footer">
                <button type="button" class="ghost-btn" onclick="hideResetModal()">Cancel</button>
                <button type="submit" class="primary-btn">Reset Password</button>
            </div>
        </form>
    </div>
</div>

<script>
function showResetModal(requestId, email) {
    document.getElementById('modalRequestId').value = requestId;
    document.getElementById('modalEmail').value = email;
    document.getElementById('modalEmailDisplay').textContent = email;
    document.getElementById('resetModal').style.display = 'flex';
}

function hideResetModal() {
    document.getElementById('resetModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('resetModal');
    if (event.target === modal) {
        hideResetModal();
    }
}
</script>

<?php viewEnd();
?>
