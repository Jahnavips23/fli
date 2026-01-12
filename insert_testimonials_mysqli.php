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

// Connect to the database
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "Connected to MySQL server successfully.<br>";

// Create database if it doesn't exist
$mysqli->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
echo "Database created or already exists.<br>";

// Select the database
$mysqli->select_db(DB_NAME);

// Create testimonials table if it doesn't exist
$create_table_sql = "
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
";

if ($mysqli->query($create_table_sql) === TRUE) {
    echo "Testimonials table created or already exists.<br>";
} else {
    echo "Error creating table: " . $mysqli->error . "<br>";
}

// Clear existing testimonials
if ($mysqli->query("TRUNCATE TABLE testimonials") === TRUE) {
    echo "Cleared existing testimonials.<br>";
} else {
    echo "Error clearing testimonials: " . $mysqli->error . "<br>";
}

// Insert sample testimonials
$insert_count = 0;
foreach ($testimonials as $testimonial) {
    $stmt = $mysqli->prepare("
        INSERT INTO testimonials (name, position, organization, content, rating, active, display_order)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param(
        "ssssiis", 
        $testimonial['name'], 
        $testimonial['position'], 
        $testimonial['organization'], 
        $testimonial['content'], 
        $testimonial['rating'], 
        $testimonial['active'], 
        $testimonial['display_order']
    );
    
    if ($stmt->execute()) {
        $insert_count++;
    } else {
        echo "Error inserting testimonial: " . $stmt->error . "<br>";
    }
    
    $stmt->close();
}

echo "Inserted " . $insert_count . " testimonials.<br>";

// Verify testimonials
$result = $mysqli->query("SELECT * FROM testimonials");
if ($result->num_rows > 0) {
    echo "<h3>Inserted Testimonials:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Position</th><th>Organization</th><th>Content</th><th>Rating</th><th>Active</th><th>Order</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['position'] . "</td>";
        echo "<td>" . $row['organization'] . "</td>";
        echo "<td>" . substr($row['content'], 0, 50) . "...</td>";
        echo "<td>" . $row['rating'] . "</td>";
        echo "<td>" . $row['active'] . "</td>";
        echo "<td>" . $row['display_order'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "No testimonials found.<br>";
}

// Close connection
$mysqli->close();
?>