<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'flioneit');

// Function to check if a table exists
function table_exists($db, $table) {
    try {
        $result = $db->query("SHOW TABLES LIKE '$table'");
        return $result->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

// Function to count records in a table
function count_records($db, $table) {
    try {
        $result = $db->query("SELECT COUNT(*) as count FROM $table");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    } catch (Exception $e) {
        return 0;
    }
}

// Try to connect to the database
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection_status = "Connected successfully to the database.";
    $connection_success = true;
} catch (PDOException $e) {
    $db = null;
    $connection_status = "Database connection failed: " . $e->getMessage();
    $connection_success = false;
}

// Check tables if connection successful
$tables = [
    'users',
    'blog_categories',
    'blog_posts',
    'carousel_slides',
    'downloads',
    'newsletter_subscribers',
    'settings',
    'testimonials',
    'contact_messages',
    'services',
    'counters',
    'products',
    'product_enquiries',
    'kids_programs',
    'program_registrations',
    'program_gallery',
    'kids_products',
    'kids_product_inquiries'
];

$table_status = [];
$records_count = [];

if ($connection_success) {
    foreach ($tables as $table) {
        $table_status[$table] = table_exists($db, $table);
        if ($table_status[$table]) {
            $records_count[$table] = count_records($db, $table);
        } else {
            $records_count[$table] = 0;
        }
    }
}

// Calculate overall status
$all_tables_exist = $connection_success && !in_array(false, $table_status);
$has_sample_data = $all_tables_exist && $records_count['services'] > 0 && $records_count['testimonials'] > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Status Check</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .status-icon {
            font-size: 1.5rem;
            margin-right: 10px;
        }
        .status-success {
            color: #198754;
        }
        .status-warning {
            color: #ffc107;
        }
        .status-danger {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">FLIONE Database Status</h4>
                    </div>
                    <div class="card-body">
                        <h5>Connection Status:</h5>
                        <div class="alert <?php echo $connection_success ? 'alert-success' : 'alert-danger'; ?> mb-4">
                            <?php echo htmlspecialchars($connection_status); ?>
                        </div>
                        
                        <?php if ($connection_success): ?>
                            <h5>Database Tables:</h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Table Name</th>
                                            <th>Status</th>
                                            <th>Records</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tables as $table): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($table); ?></td>
                                                <td>
                                                    <?php if ($table_status[$table]): ?>
                                                        <span class="badge bg-success">Exists</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Missing</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $records_count[$table]; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <h5>Overall Status:</h5>
                            <div class="d-flex align-items-center mb-2">
                                <?php if ($all_tables_exist): ?>
                                    <div class="status-icon status-success">✓</div>
                                    <div>All required tables exist</div>
                                <?php else: ?>
                                    <div class="status-icon status-danger">✗</div>
                                    <div>Some tables are missing</div>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex align-items-center mb-4">
                                <?php if ($has_sample_data): ?>
                                    <div class="status-icon status-success">✓</div>
                                    <div>Sample data is loaded</div>
                                <?php else: ?>
                                    <div class="status-icon status-warning">!</div>
                                    <div>Sample data may be missing</div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <h5>Next Steps:</h5>
                        <?php if ($all_tables_exist && $has_sample_data): ?>
                            <div class="alert alert-success">
                                <strong>Database is properly set up!</strong> You can now use the website.
                            </div>
                            <p>You can now:</p>
                            <ul>
                                <li>Visit the <a href="http://localhost/flioneit.com" class="btn btn-sm btn-outline-primary">Website Homepage</a></li>
                                <li>Access the <a href="http://localhost/flioneit.com/admin" class="btn btn-sm btn-outline-secondary">Admin Panel</a> (Username: admin, Password: admin123)</li>
                            </ul>
                        <?php elseif ($connection_success): ?>
                            <div class="alert alert-warning">
                                <strong>Database setup is incomplete.</strong> Please run the database setup script.
                            </div>
                            <p>To complete the setup:</p>
                            <ul>
                                <li>Run the <a href="update_database.php" class="btn btn-sm btn-outline-primary">Database Update Script</a></li>
                                <li>Or manually import <code>database/full_setup.sql</code> into your database</li>
                            </ul>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <strong>Database connection failed.</strong> Please check your database configuration.
                            </div>
                            <p>Possible solutions:</p>
                            <ul>
                                <li>Make sure MySQL server is running</li>
                                <li>Verify database credentials in the script</li>
                                <li>Create the database 'flioneit' if it doesn't exist</li>
                                <li>Make sure PHP PDO extension is enabled</li>
                            </ul>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center">
                        <button type="button" class="btn btn-primary" onclick="window.location.reload();">Refresh Status</button>
                        <?php if ($all_tables_exist && $has_sample_data): ?>
                            <a href="http://localhost/flioneit.com" class="btn btn-success ms-2">Go to Homepage</a>
                        <?php elseif ($connection_success): ?>
                            <a href="update_database.php" class="btn btn-warning ms-2">Run Setup</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>