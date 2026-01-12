# Client Inquiry Management System Setup

This directory contains scripts to set up the Client Inquiry Management System.

## Database Setup

There are two ways to create the required database tables:

### Option 1: Using PHP Script

Run the following command from the command line:

```
php create_inquiry_tables.php
```

### Option 2: Using SQL Script

If the PHP script doesn't work, you can manually import the SQL script:

1. Open phpMyAdmin or your preferred MySQL client
2. Select your database (flioneit)
3. Import the `inquiry_tables.sql` file

## Required Tables

The following tables will be created:

- `client_inquiries` - Stores client inquiry information
- `client_inquiry_notes` - Stores notes and activity history for inquiries
- `email_templates` - Stores email templates for welcome emails
- `email_attachments` - Stores file attachments for emails
- `inquiry_sources` - Stores sources of inquiries (e.g., IndiaMart, Website)
- `inquiry_types` - Stores types of inquiries (e.g., Product, Service)

## Default Data

The setup will also create:

- Default inquiry sources
- Default inquiry types
- A default welcome email template

## Troubleshooting

If you encounter any issues:

1. Make sure your database connection is properly configured in `includes/config.php`
2. Check that the database user has sufficient privileges to create tables
3. If using the PHP script, ensure PHP has the PDO MySQL extension enabled
4. If all else fails, use the SQL script directly