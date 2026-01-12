<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'flioneit');

// Try to connect to the database
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection_status = "Connected successfully to the database.";
} catch (PDOException $e) {
    $db = null;
    $connection_status = "Database connection failed: " . $e->getMessage();
}

// Function to execute SQL file
function execute_sql_file($db, $file_path) {
    if (!file_exists($file_path)) {
        return "Error: File not found: $file_path";
    }
    
    try {
        // Read the SQL file
        $sql = file_get_contents($file_path);
        
        // Split SQL file into individual statements
        $statements = explode(';', $sql);
        
        // Execute each statement
        $count = 0;
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $db->exec($statement);
                $count++;
            }
        }
        
        return "Successfully executed $count statements from $file_path";
    } catch (PDOException $e) {
        return "Error executing SQL: " . $e->getMessage();
    }
}

// Files to execute
$sql_files = [
    'database/full_setup.sql',
    'database/counters.sql',
    'database/products.sql',
    'database/kids_programs.sql',
    'database/kids_products.sql',
    'database/kids_product_inquiries.sql'
];

// Execute each file
$results = [];
if ($db) {
    foreach ($sql_files as $file) {
        $results[$file] = execute_sql_file($db, $file);
    }
} else {
    foreach ($sql_files as $file) {
        $results[$file] = "Skipped: Database connection failed";
    }
}

// Display results
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Update</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Database Update Results</h4>
                    </div>
                    <div class="card-body">
                        <h5>Database Connection:</h5>
                        <div class="alert <?php echo $db ? 'alert-success' : 'alert-danger'; ?> mb-4">
                            <?php echo htmlspecialchars($connection_status); ?>
                        </div>
                        
                        <h5>SQL Execution Results:</h5>
                        <ul class="list-group mb-4">
                            <?php foreach ($results as $file => $result): ?>
                                <li class="list-group-item">
                                    <strong><?php echo htmlspecialchars($file); ?>:</strong>
                                    <?php if (strpos($result, 'Error') === 0): ?>
                                        <span class="text-danger"><?php echo htmlspecialchars($result); ?></span>
                                    <?php else: ?>
                                        <span class="text-success"><?php echo htmlspecialchars($result); ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <h5>Next Steps:</h5>
                        <?php if ($db): ?>
                        <p>Your database has been updated with the latest schema and sample data. You can now:</p>
                        <ul>
                            <li>Visit the <a href="http://localhost/flioneit.com" class="btn btn-sm btn-outline-primary">Website Homepage</a></li>
                            <li>Access the <a href="http://localhost/flioneit.com/admin" class="btn btn-sm btn-outline-secondary">Admin Panel</a></li>
                            <li>For security reasons, please delete this file after use.</li>
                        </ul>
                        <?php else: ?>
                        <p>Database connection failed. Please check the following:</p>
                        <ul>
                            <li>Make sure MySQL server is running</li>
                            <li>Verify database credentials in the script</li>
                            <li>Create the database 'flioneit' if it doesn't exist</li>
                            <li>Make sure PHP PDO extension is enabled</li>
                        </ul>
                        <div class="alert alert-info">
                            <strong>Manual Database Setup:</strong>
                            <ol>
                                <li>Create a database named 'flioneit'</li>
                                <li>Import the SQL files from the 'database' directory:
                                    <ul>
                                        <li>full_setup.sql</li>
                                        <li>counters.sql</li>
                                        <li>products.sql</li>
                                        <li>kids_programs.sql</li>
                                        <li>kids_products.sql</li>
                                        <li>kids_product_inquiries.sql</li>
                                    </ul>
                                </li>
                            </ol>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center">
                        <?php if ($db): ?>
                        <a href="http://localhost/flioneit.com" class="btn btn-primary">Go to Homepage</a>
                        <?php else: ?>
                        <button type="button" class="btn btn-primary" onclick="window.location.reload();">Try Again</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>