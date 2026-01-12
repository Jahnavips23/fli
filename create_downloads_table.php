<?php
require_once 'includes/config.php';

try {
    // Check if downloads table exists
    $stmt = $db->query("SHOW TABLES LIKE 'downloads'");
    $exists = $stmt->rowCount() > 0;
    
    if (!$exists) {
        echo "Downloads table does not exist. Creating it now...<br>";
        
        // Read SQL file
        $sql = file_get_contents('database/downloads.sql');
        
        // Execute SQL
        $db->exec($sql);
        
        echo "Downloads table created successfully with sample data!<br>";
        
        // Create uploads directory if it doesn't exist
        $upload_dir = 'uploads/downloads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
            echo "Created uploads directory: $upload_dir<br>";
        }
        
        // Create sample files for downloads
        $sample_files = [
            'sample_school_management.zip',
            'sample_learning_app.zip',
            'sample_math_games.apk',
            'sample_science_explorer.ipa',
            'sample_teachers_toolkit.dmg',
            'sample_classroom_assistant.zip',
            'sample_programming_kids.apk',
            'sample_edu_games.zip',
            'sample_timetable_planner.zip',
            'sample_progress_tracker.ipa',
            'sample_stem_activities.apk',
            'sample_user_manual.pdf'
        ];
        
        foreach ($sample_files as $file) {
            $file_path = $upload_dir . $file;
            if (!file_exists($file_path)) {
                // Create a dummy file with some content
                file_put_contents($file_path, 'This is a sample file for demonstration purposes.');
                echo "Created sample file: $file_path<br>";
            }
        }
        
        echo "All done!";
    } else {
        echo "Downloads table already exists.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>