# Flione IT Website

A fully functional PHP/MySQL website for FLIONE (Future-ready Learning Infrastructure Optimized for Next-gen Education), featuring client-side pages and a comprehensive admin panel for content management.

## Features

### Client-Side Pages
- Home with dynamic carousel
- About Us with company information, mission/vision, and testimonials
- For School resources and information
- For Kids educational content
- Blog with categories and posts
- Downloads section with categorized resources
- Contact form with newsletter subscription

### Admin Panel
- Dashboard with statistics and recent activity
- Content Management
  - Carousel slides
  - Blog posts and categories
  - Downloads
  - Testimonials
- Communication
  - Contact messages
  - Newsletter subscribers
- User Management
- Site Settings

## Installation Instructions

1. **Automated Setup**
   - Place the entire project in your web server's document root
   - Navigate to `http://localhost/flioneit.com/update_database.php` in your browser
   - The script will create the database and all required tables with sample data
   - You can check the database status at `http://localhost/flioneit.com/check_database.php`

2. **Manual Database Setup**
   - Create a MySQL database named `flioneit`
   - Import the database structure and sample data from `database/full_setup.sql`
   - Default database settings:
     - Host: localhost
     - Username: root
     - Password: (empty)
     - Database: flioneit

3. **Configuration**
   - Update database connection details in `includes/config.php` if needed
   - You can modify site settings through the admin panel

4. **Web Server Requirements**
   - PHP 7.4+ with PDO extension enabled
   - MySQL 5.7+ or MariaDB 10.2+
   - Apache with mod_rewrite enabled
   - Access the website at `http://localhost/flioneit.com`

5. **Default Admin Credentials**
   - Username: admin
   - Password: admin123
   - Access the admin panel at `http://localhost/flioneit.com/admin`
   - **Important:** Change the default password after your first login

6. **File Permissions**
   - Make sure the following directories are writable by the web server:
     - `uploads/`
     - `assets/images/blog/`
     - `assets/images/carousel/`
     - `assets/images/testimonials/`
     - `assets/images/services/`

## Directory Structure

- `/admin` - Admin panel with complete content management system
  - `/admin/assets` - Admin-specific CSS, JavaScript, and images
  - `/admin/includes` - Admin PHP includes and functions
  - `/admin/pages` - Admin content management pages
- `/assets` - Contains CSS, JavaScript, images, and other static files
- `/database` - Database structure and sample data
- `/includes` - PHP includes and functions
- `/process` - Backend processing scripts
- `/uploads` - User uploaded files
  - `/uploads/downloads` - Downloadable resources
  - `/uploads/blog` - Blog post images

## Development Notes

- The website uses Bootstrap 5 for responsive design
- Font Awesome is used for icons
- AOS library is used for scroll animations
- jQuery is included for additional functionality

## License

This project is proprietary and confidential. Unauthorized copying, distribution, or use is strictly prohibited.

## Contact

For any questions or support, please contact:
- Email: contact@flioneit.com
- Phone: +1234567890