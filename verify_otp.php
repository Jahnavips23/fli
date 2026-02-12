<?php
require 'includes/config.php';

header('Content-Type: application/json'); // IMPORTANT

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['email']) || !isset($_POST['otp'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing data']);
        exit;
    }

    $email = $_POST['email'];
    $otp = $_POST['otp'];

    try {

        $stmt = $db->prepare("
            SELECT * FROM email_verifications
            WHERE email = :email
            AND otp = :otp
            AND expires_at >= NOW()
            AND is_verified = 0
        ");

        $stmt->execute([
            ':email' => $email,
            ':otp' => $otp
        ]);

        if ($stmt->rowCount() > 0) {

            $db->prepare("
                UPDATE email_verifications
                SET is_verified = 1
                WHERE email = :email AND otp = :otp
            ")->execute([
                ':email' => $email,
                ':otp' => $otp
            ]);

            echo json_encode(['status' => 'verified']);

        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid OTP']);
        }

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
