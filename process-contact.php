<?php
require_once 'includes/config.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $contact_type = isset($_POST['contact_type']) ? trim($_POST['contact_type']) : '';
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    
    // Get reCAPTCHA response
    $recaptchaResponse = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
    
    // Validate form data
    if (empty($name)) {
        $response['message'] = 'Please enter your name.';
    } elseif (empty($email)) {
        $response['message'] = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
    } elseif (empty($subject)) {
        $response['message'] = 'Please enter a subject.';
    } elseif (empty($message)) {
        $response['message'] = 'Please enter your message.';
    } elseif (empty($recaptchaResponse)) {
        $response['message'] = 'Please complete the reCAPTCHA verification.';
    } elseif (!verifyRecaptcha($recaptchaResponse)) {
        $response['message'] = 'reCAPTCHA verification failed. Please try again.';
    } else {
        try {
            // Insert contact message into database
            $stmt = $db->prepare("
                INSERT INTO contact_messages (name, email, subject, message, contact_type, created_at)
                VALUES (:name, :email, :subject, :message, :contact_type, NOW())
            ");
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message,
                'contact_type' => $contact_type
            ]);
            
            // Subscribe to newsletter if requested
            if ($newsletter) {
                // Check if email already exists in subscribers
                $stmt = $db->prepare("SELECT id FROM newsletter_subscribers WHERE email = :email");
                $stmt->execute(['email' => $email]);
                
                if ($stmt->rowCount() === 0) {
                    // Add new subscriber
                    $subscriber_type = '';
                    switch ($contact_type) {
                        case 'school_admin':
                            $subscriber_type = 'school_staff';
                            break;
                        case 'teacher':
                            $subscriber_type = 'school_staff';
                            break;
                        case 'parent':
                            $subscriber_type = 'parent';
                            break;
                        default:
                            $subscriber_type = 'other';
                    }
                    
                    $stmt = $db->prepare("
                        INSERT INTO newsletter_subscribers (email, name, subscriber_type, active, created_at)
                        VALUES (:email, :name, :subscriber_type, 1, NOW())
                    ");
                    $stmt->execute([
                        'email' => $email,
                        'name' => $name,
                        'subscriber_type' => $subscriber_type
                    ]);
                }
            }
            
            // Send email notification to admin
            $to = ADMIN_EMAIL;
            $email_subject = "New Contact Form Submission: $subject";
            $email_message = "
                <html>
                <head>
                    <title>New Contact Form Submission</title>
                </head>
                <body>
                    <h2>New Contact Form Submission</h2>
                    <p><strong>Name:</strong> $name</p>
                    <p><strong>Email:</strong> $email</p>
                    <p><strong>Subject:</strong> $subject</p>
                    <p><strong>Contact Type:</strong> $contact_type</p>
                    <p><strong>Message:</strong></p>
                    <p>" . nl2br($message) . "</p>
                    <p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>
                </body>
                </html>
            ";
            
            // Set email headers
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: $name <$email>" . "\r\n";
            
            // Send email
            if (mail($to, $email_subject, $email_message, $headers)) {
                $response['success'] = true;
                $response['message'] = 'Your message has been sent successfully. We will get back to you soon!';
                
                // If this is an AJAX request, return JSON response
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }
                
                // Otherwise, redirect with success message
                $_SESSION['contact_success'] = $response['message'];
                header('Location: ' . SITE_URL . '/about.php#contact');
                exit;
            } else {
                $response['message'] = 'There was a problem sending your message. Please try again later.';
            }
        } catch (PDOException $e) {
            $response['message'] = 'An error occurred. Please try again later.';
            error_log("Contact form error: " . $e->getMessage());
        }
    }
}

// If this is an AJAX request, return JSON response
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Otherwise, redirect with error message
$_SESSION['contact_error'] = $response['message'];
header('Location: ' . SITE_URL . '/about.php#contact');
exit;