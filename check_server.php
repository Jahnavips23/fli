<?php
// Check if the server is running correctly
echo "Server is running correctly!";

// Check PHP version
echo "<br>PHP Version: " . phpversion();

// Check if MySQL connection works
try {
    $db = new PDO("mysql:host=localhost;dbname=flioneit", "root", "");
    echo "<br>MySQL connection successful!";
} catch (PDOException $e) {
    echo "<br>MySQL connection failed: " . $e->getMessage();
}

// Check if required directories exist
$directories = [
    'assets',
    'assets/css',
    'assets/js',
    'assets/images',
    'includes',
    'database',
    'process',
    'uploads'
];

echo "<br><br>Directory Check:";
foreach ($directories as $dir) {
    echo "<br>$dir: " . (is_dir(__DIR__ . '/' . $dir) ? 'Exists' : 'Missing');
}

// Check if required files exist
$files = [
    'index.php',
    'includes/config.php',
    'includes/functions.php',
    'includes/header.php',
    'includes/footer.php',
    'assets/css/style.css',
    'assets/js/main.js',
    'assets/images/logo.png',
    'assets/images/logo-white.png'
];

echo "<br><br>File Check:";
foreach ($files as $file) {
    echo "<br>$file: " . (file_exists(__DIR__ . '/' . $file) ? 'Exists' : 'Missing');
}
?>