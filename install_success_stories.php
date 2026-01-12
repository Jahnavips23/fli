<?php
require_once 'includes/config.php';

// Check if the user is logged in as admin
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Access denied. You must be logged in as an administrator to run this script.");
}

// Read the SQL file
$sql_file = file_get_contents('database/success_stories.sql');

// Split the SQL file into individual statements
$statements = explode(';', $sql_file);

// Execute each statement
$success = true;
$error_messages = [];

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (!empty($statement)) {
        try {
            $db->exec($statement);
        } catch (PDOException $e) {
            $success = false;
            $error_messages[] = $e->getMessage();
        }
    }
}

// Output the result
if ($success) {
    echo "<h1>Success Stories Table Installation</h1>";
    echo "<p>The success_stories table has been successfully created and populated with sample data.</p>";
    echo "<p>You can now manage success stories from the <a href='admin/pages/success-stories.php'>admin panel</a>.</p>";
} else {
    echo "<h1>Installation Error</h1>";
    echo "<p>There was an error creating the success_stories table:</p>";
    echo "<ul>";
    foreach ($error_messages as $message) {
        echo "<li>" . htmlspecialchars($message) . "</li>";
    }
    echo "</ul>";
}
?>