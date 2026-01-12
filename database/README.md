# FLIONE Database Setup

This directory contains SQL scripts for setting up the FLIONE website database.

## Quick Setup

The easiest way to set up the database is to use the `update_database.php` script in the root directory:

1. Make sure your MySQL server is running
2. Navigate to `http://localhost/flioneit.com/update_database.php` in your browser
3. The script will create the database and all required tables with sample data

## Manual Setup

If you prefer to set up the database manually:

1. Create a new MySQL database named `flioneit`
2. Import the `full_setup.sql` file into your database using one of these methods:
   - Using phpMyAdmin: Select the database and import the SQL file
   - Using MySQL command line: `mysql -u username -p flioneit < full_setup.sql`

## Database Structure

The database includes the following tables:

- `users` - Admin and user accounts
- `blog_categories` - Categories for blog posts
- `blog_posts` - Blog content
- `carousel_slides` - Homepage carousel slides
- `downloads` - Downloadable resources
- `newsletter_subscribers` - Email newsletter subscribers
- `settings` - Site configuration settings
- `testimonials` - Client testimonials
- `contact_messages` - Messages from the contact form
- `services` - Services offered by FLIONE

## Default Credentials

After setup, you can log in to the admin panel with:

- Username: `admin`
- Password: `admin123`

**Important:** Change the default password after your first login for security reasons.

## Sample Data

The setup includes sample data for:

- Services
- Testimonials
- Blog categories
- Site settings

You can modify or delete this sample data through the admin panel after setup.