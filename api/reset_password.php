<?php
// api/reset_password.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(["status" => "error", "message" => "Method not allowed"], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$action = isset($_GET['action']) ? $_GET['action'] : '';

// ── Action: verify token is valid (called when page loads) ──────────────────
if ($action === 'verify') {
    if (!isset($data['token']) || trim($data['token']) === '') {
        sendJsonResponse(["status" => "error", "message" => "Reset token is missing."], 400);
    }

    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()");
    $stmt->execute([trim($data['token'])]);
    $reset = $stmt->fetch();

    if ($reset) {
        sendJsonResponse(["status" => "success", "message" => "Token is valid."]);
    } else {
        sendJsonResponse(["status" => "error", "message" => "This reset link is invalid or has expired."], 400);
    }
}

// ── Action: reset the password using a valid token ──────────────────────────
if ($action === 'reset') {
    if (!isset($data['token'], $data['password']) || trim($data['token']) === '' || trim($data['password']) === '') {
        sendJsonResponse(["status" => "error", "message" => "Token and new password are required."], 400);
    }

    if (strlen($data['password']) < 6) {
        sendJsonResponse(["status" => "error", "message" => "Password must be at least 6 characters."], 400);
    }

    // Fetch the reset record
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()");
    $stmt->execute([trim($data['token'])]);
    $reset = $stmt->fetch();

    if (!$reset) {
        sendJsonResponse(["status" => "error", "message" => "This reset link is invalid or has expired."], 400);
    }

    // Update the user's password
    $hash = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE LOWER(email) = LOWER(?)");
    $stmt->execute([$hash, $reset['email']]);

    // Mark token as used
    $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?")->execute([$reset['token']]);

    sendJsonResponse(["status" => "success", "message" => "Password reset successfully! You can now log in."]);
}

sendJsonResponse(["status" => "error", "message" => "Unknown action."], 400);
