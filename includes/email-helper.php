<?php
/**
 * Email Helper Functions
 * 
 * This file contains functions for sending emails using PHPMailer
 */

// Include PHPMailer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send an email using PHPMailer with SMTP settings from the database
 * 
 * @param string $to_email Recipient email address
 * @param string $to_name Recipient name
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param string $alt_body Plain text alternative body (optional)
 * @return array ['success' => bool, 'message' => string]
 */
function send_email($to_email, $to_name, $subject, $body, $alt_body = '') {
    global $db;
    
    try {
        // Get SMTP settings from database
        $smtp_settings = [];
        $stmt = $db->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'smtp_%'");
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        foreach ($results as $row) {
            $smtp_settings[$row['setting_key']] = $row['setting_value'];
        }
        
        // Check if required SMTP settings are available
        if (empty($smtp_settings['smtp_host']) || empty($smtp_settings['smtp_username']) || 
            empty($smtp_settings['smtp_password']) || empty($smtp_settings['smtp_from_email'])) {
            return [
                'success' => false,
                'message' => 'SMTP settings are not configured properly.'
            ];
        }
        
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $smtp_settings['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_settings['smtp_username'];
        $mail->Password = $smtp_settings['smtp_password'];
        
        // Set port if available
        if (!empty($smtp_settings['smtp_port'])) {
            $mail->Port = (int)$smtp_settings['smtp_port'];
        } else {
            $mail->Port = 587; // Default port
        }
        
        // Set encryption if available (tls or ssl)
        $mail->SMTPSecure = 'tls'; // Default to TLS
        
        // Set from address
        $from_name = !empty($smtp_settings['smtp_from_name']) ? $smtp_settings['smtp_from_name'] : 'FLIONE';
        $mail->setFrom($smtp_settings['smtp_from_email'], $from_name);
        
        // Add recipient
        $mail->addAddress($to_email, $to_name);
        
        // Set email format to HTML
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        // Set alternative body if provided
        if (!empty($alt_body)) {
            $mail->AltBody = $alt_body;
        } else {
            $mail->AltBody = strip_tags($body);
        }
        
        // Send the email
        $mail->send();
        
        return [
            'success' => true,
            'message' => 'Email sent successfully.'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Email could not be sent. Error: ' . $mail->ErrorInfo
        ];
    }
}

/**
 * Send project update notification to customer
 * 
 * @param int $update_id The ID of the project update
 * @return array ['success' => bool, 'message' => string]
 */
function send_project_update_notification($update_id) {
    global $db;
    
    try {
        // Get update details with project and status information
        $stmt = $db->prepare("
            SELECT pu.*, p.title as project_title, p.order_id, p.customer_name, p.customer_email,
                   s.name as status_name, s.color as status_color
            FROM project_updates pu
            JOIN projects p ON pu.project_id = p.id
            JOIN project_statuses s ON pu.status_id = s.id
            WHERE pu.id = :update_id AND pu.notify_customer = 1 AND pu.notification_sent = 0
        ");
        $stmt->bindParam(':update_id', $update_id);
        $stmt->execute();
        $update = $stmt->fetch();
        
        if (!$update) {
            return [
                'success' => false,
                'message' => 'Update not found or notification not required.'
            ];
        }
        
        // Get site URL for tracking link
        $site_url = SITE_URL;
        $tracking_url = $site_url . '/track-project.php?id=' . $update['order_id'];
        
        // Prepare email content
        $subject = "Project Update: " . $update['project_title'];
        
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { text-align: center; margin-bottom: 20px; }
                .status { display: inline-block; padding: 8px 16px; border-radius: 4px; color: white; background-color: {$update['status_color']}; }
                .content { margin: 20px 0; }
                .footer { margin-top: 30px; font-size: 12px; color: #777; text-align: center; }
                .button { display: inline-block; padding: 10px 20px; background-color: #3498db; color: white; text-decoration: none; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Project Update</h2>
                </div>
                
                <p>Dear {$update['customer_name']},</p>
                
                <p>There has been an update to your project <strong>{$update['project_title']}</strong> (Order ID: {$update['order_id']}).</p>
                
                <div class='content'>
                    <p><strong>New Status:</strong> <span class='status'>{$update['status_name']}</span></p>
                    <p><strong>Update Details:</strong></p>
                    <p>" . nl2br($update['comments']) . "</p>
                </div>
                
                <p>You can track your project's progress at any time by visiting the link below:</p>
                
                <p style='text-align: center;'>
                    <a href='{$tracking_url}' class='button'>Track Your Project</a>
                </p>
                
                <p>If you have any questions, please don't hesitate to contact us.</p>
                
                <p>Thank you for choosing FLIONE.</p>
                
                <div class='footer'>
                    <p>This is an automated message, please do not reply directly to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Send the email
        $result = send_email(
            $update['customer_email'],
            $update['customer_name'],
            $subject,
            $body
        );
        
        if ($result['success']) {
            // Mark notification as sent
            $stmt = $db->prepare("UPDATE project_updates SET notification_sent = 1 WHERE id = :update_id");
            $stmt->bindParam(':update_id', $update_id);
            $stmt->execute();
        }
        
        return $result;
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}
?>