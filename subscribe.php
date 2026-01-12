<?php
require_once 'includes/config.php';

// Initialize variables
$success = false;
$error = '';
$redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : SITE_URL;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $subscriber_type = isset($_POST['subscriber_type']) ? trim($_POST['subscriber_type']) : 'other';
    
    // Validate form data
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (empty($name)) {
        $error = 'Please enter your name.';
    } elseif (!in_array($subscriber_type, ['parent', 'school_staff', 'other'])) {
        $subscriber_type = 'other';
    }
    
    // If no errors, save to database
    if (empty($error)) {
        try {
            // Check if email already exists
            $stmt = $db->prepare("SELECT id FROM newsletter_subscribers WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update existing subscriber
                $stmt = $db->prepare("
                    UPDATE newsletter_subscribers 
                    SET name = :name, subscriber_type = :subscriber_type, active = 1
                    WHERE email = :email
                ");
                $stmt->execute([
                    'name' => $name,
                    'subscriber_type' => $subscriber_type,
                    'email' => $email
                ]);
                
                $success = true;
                $_SESSION['subscription_message'] = 'Thank you! Your subscription has been updated.';
            } else {
                // Insert new subscriber
                $stmt = $db->prepare("
                    INSERT INTO newsletter_subscribers (email, name, subscriber_type)
                    VALUES (:email, :name, :subscriber_type)
                ");
                $stmt->execute([
                    'email' => $email,
                    'name' => $name,
                    'subscriber_type' => $subscriber_type
                ]);
                
                $success = true;
                $_SESSION['subscription_message'] = 'Thank you for subscribing to our newsletter!';
            }
        } catch (PDOException $e) {
            error_log("Error saving newsletter subscription: " . $e->getMessage());
            $error = 'An error occurred while processing your subscription. Please try again later.';
        }
    }
    
    // Set error message in session if there was an error
    if (!empty($error)) {
        $_SESSION['subscription_error'] = $error;
    }
    
    // Redirect back to the referring page
    header('Location: ' . $redirect_url);
    exit;
} else {
    // If not a POST request, redirect to home page
    header('Location: ' . SITE_URL);
    exit;
}
?>