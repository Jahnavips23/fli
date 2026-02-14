<?php
require_once 'includes/config.php';

// Hardcoded images from for-schools.php
$interactive_images = [
    0 => 'science_lab.jpg',
    1 => 'autotronic_lab.jpg',
    2 => '3d_printing_lab.jpg',
    3 => 'computer_lab.jpg',
    4 => 'robotics_lab.jpg',
    5 => 'computer_lab_setup.jpg'
];

try {
    // Fetch all services using the same order as for-schools.php
    $stmt = $db->prepare("SELECT id, title FROM services WHERE active = 1 ORDER BY display_order ASC, created_at DESC");
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($services) . " active services.\n";

    $updated_count = 0;

    foreach ($services as $index => $service) {
        if (array_key_exists($index, $interactive_images)) {
            $image_filename = $interactive_images[$index];
            $image_path = 'assets/images/services/' . $image_filename;

            // Check if file exists (optional, but good for verification)
            if (file_exists(ROOT_PATH . $image_path)) {
                echo "Updating Service ID {$service['id']} ('{$service['title']}') with image: $image_path\n";

                $update_stmt = $db->prepare("UPDATE services SET image = :image WHERE id = :id");
                $update_stmt->execute([
                    'image' => $image_path,
                    'id' => $service['id']
                ]);
                $updated_count++;
            } else {
                echo "Warning: Image file not found for Service ID {$service['id']}: " . ROOT_PATH . $image_path . "\n";
                // Still update DB? probably yes, to fix the data structure even if file is missing locally (might come from git/deployment)
                // But let's be safe and assume file exists if it was working on frontend
                $update_stmt = $db->prepare("UPDATE services SET image = :image WHERE id = :id");
                $update_stmt->execute([
                    'image' => $image_path,
                    'id' => $service['id']
                ]);
                $updated_count++;
            }
        } else {
            echo "No image mapping for Service ID {$service['id']} (Index: $index)\n";
        }
    }

    echo "Migration completed. Updated $updated_count services.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>