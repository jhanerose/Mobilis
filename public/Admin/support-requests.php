<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';
requireAuth(['admin']);

$success = '';
$errors = [];
$user = currentUser() ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $requestId = (int) ($_POST['request_id'] ?? 0);

    if ($action === 'respond_contact' && $requestId > 0) {
        $responseStatus = (string) ($_POST['response_status'] ?? 'read');
        $responseMessage = trim((string) ($_POST['response_message'] ?? ''));

        $result = respondToAdminContactMessage(
            $requestId,
            (int) ($user['user_id'] ?? 0),
            $responseStatus,
            $responseMessage
        );

        if (($result['ok'] ?? false) === true) {
            $success = 'Response sent successfully.';
        } else {
            $errors[] = (string) ($result['error'] ?? 'Could not save response.');
        }
    } elseif ($action === 'reset_password' && $requestId > 0) {
        $newPassword = trim((string) ($_POST['new_password'] ?? ''));
        $confirmPassword = trim((string) ($_POST['confirm_password'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));

        if ($newPassword === '' || strlen($newPassword) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        } else {
            $resetRequests = getPasswordResetRequests(200);
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
                        <th>Customer</th>
                        <th>Subject and message</th>
                        <th>Admin response</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Respond</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($contactMessages === []): ?>
                    <tr>
                        <td colspan="7" class="muted">No contact submissions yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($contactMessages as $msg): ?>
                        <tr>
                            <td>#<?= (int) $msg['message_id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars((string) $msg['full_name']) ?></strong>
                                <p class="muted"><?= htmlspecialchars((string) $msg['email']) ?></p>
                                <p class="muted"><?= htmlspecialchars((string) ($msg['phone'] ?? '')) ?></p>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars((string) $msg['subject']) ?></strong>
                                <p><?= nl2br(htmlspecialchars((string) ($msg['message'] ?? ''))) ?></p>
                            </td>
                            <td>
                                <?php if (trim((string) ($msg['admin_response'] ?? '')) !== ''): ?>
                                    <p><?= nl2br(htmlspecialchars((string) $msg['admin_response'])) ?></p>
                                    <p class="muted">Updated: <?= htmlspecialchars((string) ($msg['responded_at'] ?? 'N/A')) ?></p>
                                <?php else: ?>
                                    <p class="muted">No response yet.</p>
                                <?php endif; ?>
                            </td>
                            <td><span class="pill support-status-<?= htmlspecialchars((string) $msg['status']) ?>"><?= htmlspecialchars(ucfirst((string) $msg['status'])) ?></span></td>
                            <td><?= htmlspecialchars((string) $msg['created_at']) ?></td>
                            <td>
                                <form method="post" style="display:grid; gap:6px; min-width:220px;">
                                    <input type="hidden" name="action" value="respond_contact">
                                    <input type="hidden" name="request_id" value="<?= (int) $msg['message_id'] ?>">
                                    <select name="response_status" required>
                                        <option value="read" <?= (string) ($msg['status'] ?? '') === 'read' ? 'selected' : '' ?>>Mark as read</option>
                                        <option value="resolved" <?= (string) ($msg['status'] ?? '') === 'resolved' ? 'selected' : '' ?>>Mark as resolved</option>
                                    </select>
                                    <textarea name="response_message" rows="3" maxlength="1200" placeholder="Write response to this request..." required><?= htmlspecialchars((string) ($msg['admin_response'] ?? '')) ?></textarea>
                                    <button type="submit" class="primary-btn">Save response</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
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
                                <button type="button" class="ghost-btn" onclick="showResetModal(<?= (int) $request['request_id'] ?>, '<?= htmlspecialchars((string) $request['email']) ?>', '<?= htmlspecialchars((string) ($request['license_number'] ?? '')) ?>', '<?= htmlspecialchars((string) ($request['reason'] ?? '')) ?>')">Reset</button>
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
            <h4>Reset User Password</h4>
            <button type="button" class="modal-close" onclick="hideResetModal()">×</button>
        </div>
        <form method="post" class="modal-body" id="resetForm">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="request_id" id="modalRequestId">
            <input type="hidden" name="email" id="modalEmail">
            
            <div class="user-info" id="userInfo">
                <div class="user-info-item">
                    <span class="user-info-label">Email:</span>
                    <span class="user-info-value" id="modalEmailDisplay"></span>
                </div>
                <div class="user-info-item">
                    <span class="user-info-label">License:</span>
                    <span class="user-info-value" id="modalLicenseDisplay">N/A</span>
                </div>
                <div class="user-info-item">
                    <span class="user-info-label">Reason:</span>
                    <span class="user-info-value" id="modalReasonDisplay">N/A</span>
                </div>
            </div>
            
            <div class="password-field-wrapper">
                <label for="new_password">New password</label>
                <input id="new_password" type="password" name="new_password" required minlength="8" placeholder="At least 8 characters" oninput="checkPasswordStrength()">
                <button type="button" class="password-toggle" onclick="togglePasswordVisibility('new_password', this)">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
            
            <div class="password-strength">
                <div class="strength-meter">
                    <div class="strength-meter-fill" id="strengthMeter"></div>
                </div>
                <div class="strength-text" id="strengthText">Enter a password</div>
            </div>
            
            <div class="password-field-wrapper">
                <label for="confirm_password">Confirm password</label>
                <input id="confirm_password" type="password" name="confirm_password" required minlength="8" placeholder="Re-enter password">
                <button type="button" class="password-toggle" onclick="togglePasswordVisibility('confirm_password', this)">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
            
            <p class="form-hint">Password must be at least 8 characters long.</p>
            
            <div class="modal-footer">
                <button type="button" class="ghost-btn" onclick="hideResetModal()">Cancel</button>
                <button type="submit" class="primary-btn" id="resetBtn">Reset Password</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentRequestData = {};

function showResetModal(requestId, email, license, reason) {
    document.getElementById('modalRequestId').value = requestId;
    document.getElementById('modalEmail').value = email;
    document.getElementById('modalEmailDisplay').textContent = email;
    document.getElementById('modalLicenseDisplay').textContent = license || 'N/A';
    document.getElementById('modalReasonDisplay').textContent = reason || 'N/A';
    
    // Reset form
    document.getElementById('resetForm').reset();
    document.getElementById('strengthMeter').className = 'strength-meter-fill';
    document.getElementById('strengthMeter').style.width = '0%';
    document.getElementById('strengthText').textContent = 'Enter a password';
    
    currentRequestData = { requestId, email, license, reason };
    document.getElementById('resetModal').style.display = 'flex';
}

function hideResetModal() {
    document.getElementById('resetModal').style.display = 'none';
}

function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    
    if (isPassword) {
        button.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 5.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
            <line x1="1" y1="1" x2="23" y2="23"></line>
        </svg>`;
    } else {
        button.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
        </svg>`;
    }
}

function checkPasswordStrength() {
    const password = document.getElementById('new_password').value;
    const meter = document.getElementById('strengthMeter');
    const text = document.getElementById('strengthText');
    
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    meter.className = 'strength-meter-fill';
    
    if (strength <= 1) {
        meter.classList.add('weak');
        text.textContent = 'Weak password';
    } else if (strength <= 3) {
        meter.classList.add('medium');
        text.textContent = 'Medium password';
    } else {
        meter.classList.add('strong');
        text.textContent = 'Strong password';
    }
}

// Form submission with loading state
document.getElementById('resetForm').addEventListener('submit', function(e) {
    const resetBtn = document.getElementById('resetBtn');
    const password = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match.');
        return;
    }
    
    resetBtn.disabled = true;
    resetBtn.textContent = 'Resetting...';
});

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('resetModal');
    if (event.target === modal) {
        hideResetModal();
    }
}

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        hideResetModal();
    }
});
</script>

<?php viewEnd();
?>
