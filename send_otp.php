<?php
header('Content-Type: application/json');

require 'includes/config.php';

require 'includes/PHPMailer/PHPMailer.php';
require 'includes/PHPMailer/SMTP.php';
require 'includes/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email']);
        exit;
    }

    // Generate 6-digit OTP
    $otp = rand(100000, 999999);
    $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    // Save OTP to database
    $stmt = $db->prepare("
        INSERT INTO email_verifications (email, otp, expires_at)
        VALUES (:email, :otp, :expires_at)
    ");

    $stmt->execute([
        ':email' => $email,
        ':otp' => $otp,
        ':expires_at' => $expires_at
    ]);

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'support@flioneit.com';  // 🔴 CHANGE THIS
        $mail->Password = 'giyb ecpz wihb esrh';    // 🔴 CHANGE THIS
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];


        $mail->setFrom('joshithakv@gmail.com', 'FLIONE Support');
        $mail->addAddress($email);

        $mail->Subject = 'Your OTP Verification Code';
        $mail->Body = "Your OTP is: $otp\nValid for 5 minutes.";

        $mail->send();

        echo json_encode(['status' => 'success']);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $mail->ErrorInfo]);
    }
}
