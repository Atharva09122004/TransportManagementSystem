<?php
// api/forgot_password.php
require_once 'config.php';
require_once 'mail_config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(["status" => "error", "message" => "Method not allowed"], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || trim($data['email']) === '') {
    sendJsonResponse(["status" => "error", "message" => "Email address is required."], 400);
}

$email = trim(strtolower($data['email']));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJsonResponse(["status" => "error", "message" => "Please enter a valid email address."], 400);
}

// Look up user by email
$stmt = $pdo->prepare("SELECT id, name FROM users WHERE LOWER(email) = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

// Always respond success to prevent email enumeration attacks
if (!$user) {
    sendJsonResponse(["status" => "success", "message" => "If that email is registered, a reset link has been sent."]);
}

// Delete any existing unused tokens for this email
$pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

// Generate a secure random token
$token = bin2hex(random_bytes(32)); // 64 char hex string
$expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Store token in DB
$stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
$stmt->execute([$email, $token, $expiresAt]);

// Build reset link
$resetLink = APP_BASE_URL . "/reset_password.html?token=" . $token;

// Send email via PHPMailer
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;

    $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
    $mail->addAddress($email, $user['name']);

    $mail->isHTML(true);
    $mail->Subject = 'Reset Your Nexus TMS Password';
    $mail->Body    = "
    <div style='font-family: Inter, sans-serif; max-width: 600px; margin: 0 auto; background: #0f172a; color: #f8fafc; padding: 2rem; border-radius: 12px; border: 1px solid rgba(59,130,246,0.3);'>
        <div style='text-align:center; margin-bottom: 2rem;'>
            <div style='width:48px;height:48px;background:linear-gradient(135deg,#3b82f6,#8b5cf6);border-radius:12px;margin:0 auto 1rem;display:inline-block;'></div>
            <h1 style='font-size:1.75rem;margin:0;color:#f8fafc;'>Nexus<span style='color:#3b82f6;'>TMS</span></h1>
        </div>
        <h2 style='color:#f8fafc; margin-bottom: 1rem;'>Password Reset Request</h2>
        <p style='color:#94a3b8; line-height:1.6;'>Hi <strong style='color:#f8fafc;'>{$user['name']}</strong>,</p>
        <p style='color:#94a3b8; line-height:1.6;'>We received a request to reset your password. Click the button below to set a new password. This link is valid for <strong style='color:#f8fafc;'>1 hour</strong>.</p>
        <div style='text-align:center; margin: 2rem 0;'>
            <a href='{$resetLink}' style='display:inline-block; background:linear-gradient(135deg,#3b82f6,#6366f1); color:white; padding:0.9rem 2.5rem; border-radius:9999px; text-decoration:none; font-weight:700; font-size:1rem;'>Reset My Password</a>
        </div>
        <p style='color:#94a3b8; font-size:0.85rem; line-height:1.6;'>If the button doesn't work, copy and paste this link into your browser:</p>
        <p style='color:#3b82f6; font-size:0.8rem; word-break:break-all;'>{$resetLink}</p>
        <hr style='border:none; border-top:1px solid rgba(255,255,255,0.08); margin: 1.5rem 0;'>
        <p style='color:#64748b; font-size:0.8rem;'>If you didn't request a password reset, you can safely ignore this email. Your password will not change.</p>
    </div>
    ";
    $mail->AltBody = "Hi {$user['name']},\n\nReset your Nexus TMS password by clicking this link:\n{$resetLink}\n\nThis link expires in 1 hour.\n\nIf you didn't request this, ignore this email.";

    $mail->send();
    sendJsonResponse(["status" => "success", "message" => "If that email is registered, a reset link has been sent."]);

} catch (Exception $e) {
    // Log error server-side but don't expose SMTP details to client
    error_log("PHPMailer Error: " . $mail->ErrorInfo);
    sendJsonResponse(["status" => "error", "message" => "Failed to send email. Please check SMTP configuration in api/mail_config.php."], 500);
}
