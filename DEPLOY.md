# FLIONE Deployment Guide

## Prerequisites
- Apache with modules: mod_rewrite, mod_headers, mod_deflate, mod_expires
- PHP 7.4+ with PDO
- MySQL/MariaDB database
- Optional: Composer (for vlucas/phpdotenv)

## Steps
1. Upload codebase to the server (document root of the vhost)
2. Create `.env` with production values:

```
APP_ENV=production
SITE_URL=https://site.flioneit.com

DB_HOST=localhost
DB_NAME=flionxga_frontend
DB_USER=flionxga_userf
DB_PASS=REDACTED

SITE_NAME=Flione IT
SITE_EMAIL=contact@flioneit.com
```

3. Ensure `.htaccess` is active:
   - Forces HTTPS + canonical host
   - Adds security & caching headers
   - Protects uploads execution

4. Permissions
- Directories: 755
- Files: 655
- Ensure `uploads/` is writable by the web server user if uploads occur

5. Apache VirtualHost (example)
```
<VirtualHost *:80>
    ServerName site.flioneit.com
    Redirect permanent / https://site.flioneit.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName site.flioneit.com
    DocumentRoot /var/www/flioneit.com

    <Directory /var/www/flioneit.com>
        AllowOverride All
        Require all granted
    </Directory>

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/site.flioneit.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/site.flioneit.com/privkey.pem
</VirtualHost>
```

6. Verify
- Visit https://site.flioneit.com/about.php
- Check images load (About page and Testimonials)
- Test blog, downloads, newsletter subscribe
- Check admin: https://site.flioneit.com/admin

## Notes
- reCAPTCHA keys are test keys. Replace in `includes/recaptcha-config.php` for production.
- Error display is disabled in production; logs are enabled.
- `SITE_URL` and DB settings are taken from `.env` if present.