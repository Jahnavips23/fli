<?php
// Set the content type to plain text for debugging
header('Content-Type: text/plain');

// Function to create a placeholder image
function create_placeholder_image($width, $height, $bg_color, $text_color, $text, $save_path) {
    // Create image
    $image = imagecreatetruecolor($width, $height);
    
    // Convert hex colors to RGB
    list($r1, $g1, $b1) = sscanf($bg_color, "#%02x%02x%02x");
    list($r2, $g2, $b2) = sscanf($text_color, "#%02x%02x%02x");
    
    // Allocate colors
    $bg = imagecolorallocate($image, $r1, $g1, $b1);
    $text_color = imagecolorallocate($image, $r2, $g2, $b2);
    
    // Fill background
    imagefill($image, 0, 0, $bg);
    
    // Add text
    $font_size = 5;
    $font = 1; // Built-in font
    
    // Get text dimensions
    $text_width = imagefontwidth($font_size) * strlen($text);
    $text_height = imagefontheight($font_size);
    
    // Center text
    $x = ($width - $text_width) / 2;
    $y = ($height - $text_height) / 2;
    
    // Add text to image
    imagestring($image, $font_size, $x, $y, $text, $text_color);
    
    // Ensure directory exists
    $dir = dirname($save_path);
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    
    // Save image
    imagepng($image, $save_path);
    imagedestroy($image);
    
    echo "Created: $save_path\n";
}

// Create logo images
create_placeholder_image(200, 50, "#0056b3", "#ffffff", "Flione IT", __DIR__ . "/assets/images/logo/logo.png");
create_placeholder_image(200, 50, "#ffffff", "#0056b3", "Flione IT", __DIR__ . "/assets/images/logo-white.png");

// Create carousel slides
create_placeholder_image(1920, 600, "#0056b3", "#ffffff", "Welcome to Flione IT", __DIR__ . "/assets/images/carousel/slide1.jpg");
create_placeholder_image(1920, 600, "#004494", "#ffffff", "Solutions for Schools", __DIR__ . "/assets/images/carousel/slide2.jpg");
create_placeholder_image(1920, 600, "#003d7a", "#ffffff", "Kid-Friendly Technology", __DIR__ . "/assets/images/carousel/slide3.jpg");

// Create about image
create_placeholder_image(600, 400, "#0056b3", "#ffffff", "About Flione IT", __DIR__ . "/assets/images/about-img.png");

// Create testimonial images
create_placeholder_image(150, 150, "#0056b3", "#ffffff", "John", __DIR__ . "/assets/images/testimonials/testimonial-1.jpg");
create_placeholder_image(150, 150, "#004494", "#ffffff", "Sarah", __DIR__ . "/assets/images/testimonials/testimonial-2.jpg");
create_placeholder_image(150, 150, "#003d7a", "#ffffff", "Michael", __DIR__ . "/assets/images/testimonials/testimonial-3.jpg");

// Create client logos
for ($i = 1; $i <= 6; $i++) {
    create_placeholder_image(200, 100, "#ffffff", "#0056b3", "Client $i", __DIR__ . "/assets/images/clients/client-$i.png");
}

// Create blog images
create_placeholder_image(800, 500, "#0056b3", "#ffffff", "Blog Default", __DIR__ . "/assets/images/blog/blog-default.jpg");

// Create counter background
create_placeholder_image(1920, 500, "#0056b3", "#ffffff", "Counter Background", __DIR__ . "/assets/images/counter-bg.jpg");

echo "All placeholder images have been created successfully!";
?>