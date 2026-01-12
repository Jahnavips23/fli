<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'flioneit');

// Sample testimonials
$testimonials = [
    [
        'name' => 'Sarah Johnson',
        'position' => 'Principal',
        'organization' => 'Oakridge Academy',
        'content' => 'FLIONE has transformed our school\'s technology infrastructure. Our students are more engaged, and our teachers have the tools they need to deliver exceptional education.',
        'rating' => 5,
        'active' => 1,
        'display_order' => 1
    ],
    [
        'name' => 'Michael Chen',
        'position' => 'IT Director',
        'organization' => 'Westfield Schools',
        'content' => 'The implementation process was seamless, and the ongoing support has been exceptional. FLIONE truly understands the unique challenges schools face with technology integration.',
        'rating' => 5,
        'active' => 1,
        'display_order' => 2
    ],
    [
        'name' => 'Emily Rodriguez',
        'position' => 'Science Teacher',
        'organization' => 'Greenwood High',
        'content' => 'As a teacher, I appreciate how FLIONE\'s solutions are designed with the classroom in mind. The technology enhances my teaching without getting in the way.',
        'rating' => 5,
        'active' => 1,
        'display_order' => 3
    ]
];

// Try to connect to the database
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully to the database.<br>";
    
    // Check if testimonials table exists
    $stmt = $db->query("SHOW TABLES LIKE 'testimonials'");
    if ($stmt->rowCount() > 0) {
        echo "Testimonials table exists.<br>";
        
        // Create testimonials table if it doesn't exist
        $db->exec("
            CREATE TABLE IF NOT EXISTS testimonials (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                position VARCHAR(100),
                organization VARCHAR(100),
                content TEXT NOT NULL,
                rating INT NOT NULL DEFAULT 5,
                image VARCHAR(255),
                active TINYINT(1) NOT NULL DEFAULT 1,
                display_order INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // Clear existing testimonials
        $db->exec("TRUNCATE TABLE testimonials");
        echo "Cleared existing testimonials.<br>";
        
        // Insert sample testimonials
        $stmt = $db->prepare("
            INSERT INTO testimonials (name, position, organization, content, rating, active, display_order)
            VALUES (:name, :position, :organization, :content, :rating, :active, :display_order)
        ");
        
        foreach ($testimonials as $testimonial) {
            $stmt->execute($testimonial);
        }
        
        echo "Inserted " . count($testimonials) . " testimonials.<br>";
        
        // Verify testimonials
        $stmt = $db->query("SELECT * FROM testimonials");
        $inserted_testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Inserted Testimonials:</h3>";
        echo "<pre>";
        print_r($inserted_testimonials);
        echo "</pre>";
    } else {
        echo "Testimonials table does not exist. Creating it now.<br>";
        
        // Create testimonials table
        $db->exec("
            CREATE TABLE IF NOT EXISTS testimonials (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                position VARCHAR(100),
                organization VARCHAR(100),
                content TEXT NOT NULL,
                rating INT NOT NULL DEFAULT 5,
                image VARCHAR(255),
                active TINYINT(1) NOT NULL DEFAULT 1,
                display_order INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        echo "Testimonials table created.<br>";
        
        // Insert sample testimonials
        $stmt = $db->prepare("
            INSERT INTO testimonials (name, position, organization, content, rating, active, display_order)
            VALUES (:name, :position, :organization, :content, :rating, :active, :display_order)
        ");
        
        foreach ($testimonials as $testimonial) {
            $stmt->execute($testimonial);
        }
        
        echo "Inserted " . count($testimonials) . " testimonials.<br>";
        
        // Verify testimonials
        $stmt = $db->query("SELECT * FROM testimonials");
        $inserted_testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Inserted Testimonials:</h3>";
        echo "<pre>";
        print_r($inserted_testimonials);
        echo "</pre>";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
    
    // If the error is about the database not existing, try to create it
    if (strpos($e->getMessage(), "Unknown database") !== false) {
        try {
            $db = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database
            $db->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
            echo "Database created successfully.<br>";
            
            // Connect to the new database
            $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create testimonials table
            $db->exec("
                CREATE TABLE IF NOT EXISTS testimonials (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    position VARCHAR(100),
                    organization VARCHAR(100),
                    content TEXT NOT NULL,
                    rating INT NOT NULL DEFAULT 5,
                    image VARCHAR(255),
                    active TINYINT(1) NOT NULL DEFAULT 1,
                    display_order INT NOT NULL DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");
            
            echo "Testimonials table created.<br>";
            
            // Insert sample testimonials
            $stmt = $db->prepare("
                INSERT INTO testimonials (name, position, organization, content, rating, active, display_order)
                VALUES (:name, :position, :organization, :content, :rating, :active, :display_order)
            ");
            
            foreach ($testimonials as $testimonial) {
                $stmt->execute($testimonial);
            }
            
            echo "Inserted " . count($testimonials) . " testimonials.<br>";
            
            // Verify testimonials
            $stmt = $db->query("SELECT * FROM testimonials");
            $inserted_testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<h3>Inserted Testimonials:</h3>";
            echo "<pre>";
            print_r($inserted_testimonials);
            echo "</pre>";
        } catch (PDOException $e2) {
            echo "Error creating database: " . $e2->getMessage();
        }
    }
}
?>