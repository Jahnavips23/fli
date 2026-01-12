<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Set header to JSON
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get form data
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$type = isset($_POST['type']) ? trim($_POST['type']) : '';

// Get reCAPTCHA response
$recaptchaResponse = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';

// Validate data
if (empty($email)) {
    echo json_encode([
        'success' => false,
        'message' => 'Email is required'
    ]);
    exit;
}

if (empty($type)) {
    echo json_encode([
        'success' => false,
        'message' => 'Subscriber type is required'
    ]);
    exit;
}

// Verify reCAPTCHA
if (empty($recaptchaResponse)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please complete the reCAPTCHA verification'
    ]);
    exit;
}

if (!verifyRecaptcha($recaptchaResponse)) {
    echo json_encode([
        'success' => false,
        'message' => 'reCAPTCHA verification failed. Please try again.'
    ]);
    exit;
}

// Subscribe to newsletter
$result = subscribe_to_newsletter($email, $name, $type);

if ($result === true) {
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for subscribing to our newsletter!'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $result
    ]);
}
?>