<?php
require_once '../includes/config.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
    $first_name = isset($_POST['first_name']) ? sanitize_input($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_input($_POST['last_name']) : '';
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : '';
    $cover_letter = isset($_POST['cover_letter']) ? sanitize_input($_POST['cover_letter']) : '';
    
    // Validate required fields
    if (empty($job_id) || empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill in all required fields.'
        ]);
        exit;
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please enter a valid email address.'
        ]);
        exit;
    }
    
    // Check if job exists
    try {
        $stmt = $db->prepare("SELECT id, title FROM job_listings WHERE id = :id AND is_active = 1");
        $stmt->bindParam(':id', $job_id);
        $stmt->execute();
        $job = $stmt->fetch();
        
        if (!$job) {
            echo json_encode([
                'success' => false,
                'message' => 'The job position is no longer available.'
            ]);
            exit;
        }
    } catch (PDOException $e) {
        error_log("Error checking job: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred. Please try again later.'
        ]);
        exit;
    }
    
    // Handle resume upload
    $resume_path = '';
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['resume']['tmp_name'];
        $file_name = $_FILES['resume']['name'];
        $file_size = $_FILES['resume']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validate file extension
        $allowed_extensions = ['pdf', 'doc', 'docx'];
        if (!in_array($file_ext, $allowed_extensions)) {
            echo json_encode([
                'success' => false,
                'message' => 'Only PDF, DOC, and DOCX files are allowed for resumes.'
            ]);
            exit;
        }
        
        // Validate file size (5MB max)
        if ($file_size > 5 * 1024 * 1024) {
            echo json_encode([
                'success' => false,
                'message' => 'Resume file size exceeds the 5MB limit.'
            ]);
            exit;
        }
        
        // Create unique filename
        $new_file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $first_name . '_' . $last_name) . '.' . $file_ext;
        $upload_dir = '../uploads/resumes/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $upload_path = $upload_dir . $new_file_name;
        
        // Move uploaded file
        if (move_uploaded_file($file_tmp, $upload_path)) {
            $resume_path = 'uploads/resumes/' . $new_file_name;
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to upload resume. Please try again.'
            ]);
            exit;
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Please upload your resume.'
        ]);
        exit;
    }
    
    // Save application to database
    try {
        $stmt = $db->prepare("
            INSERT INTO job_applications (job_id, first_name, last_name, email, phone, resume_path, cover_letter, status)
            VALUES (:job_id, :first_name, :last_name, :email, :phone, :resume_path, :cover_letter, 'New')
        ");
        
        $stmt->bindParam(':job_id', $job_id);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':resume_path', $resume_path);
        $stmt->bindParam(':cover_letter', $cover_letter);
        
        $stmt->execute();
        
        // Send notification email to admin
        $to = ADMIN_EMAIL;
        $subject = "New Job Application: {$job['title']}";
        $message = "
            <html>
            <head>
                <title>New Job Application</title>
            </head>
            <body>
                <h2>New Job Application Received</h2>
                <p><strong>Position:</strong> {$job['title']}</p>
                <p><strong>Applicant:</strong> {$first_name} {$last_name}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Phone:</strong> {$phone}</p>
                <p>Please log in to the admin panel to review this application.</p>
            </body>
            </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . SITE_EMAIL . "\r\n";
        
        mail($to, $subject, $message, $headers);
        
        // Send confirmation email to applicant
        $to_applicant = $email;
        $subject_applicant = "Your Application to FLIONE: {$job['title']}";
        $message_applicant = "
            <html>
            <head>
                <title>Application Confirmation</title>
            </head>
            <body>
                <h2>Thank You for Your Application</h2>
                <p>Dear {$first_name},</p>
                <p>Thank you for applying for the <strong>{$job['title']}</strong> position at FLIONE.</p>
                <p>We have received your application and will review it shortly. If your qualifications match our requirements, we will contact you for the next steps in the hiring process.</p>
                <p>Please note that due to the volume of applications we receive, we may not be able to respond to all applicants individually.</p>
                <p>Best regards,<br>The FLIONE Recruitment Team</p>
            </body>
            </html>
        ";
        
        mail($to_applicant, $subject_applicant, $message_applicant, $headers);
        
        echo json_encode([
            'success' => true,
            'message' => 'Your application has been submitted successfully. Thank you for your interest in joining FLIONE!'
        ]);
    } catch (PDOException $e) {
        error_log("Error saving application: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while submitting your application. Please try again later.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}
?>