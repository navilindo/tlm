# E-Learning Management System (LMS) - Installation Guide

## System Requirements

- **PHP**: 8.0 or higher
- **MySQL**: 8.0 or higher  
- **Web Server**: Apache/Nginx
- **Extensions**: PDO, PDO_MySQL, OpenSSL, mbstring

## Installation Steps

### 1. Database Setup

1. Create a MySQL database:
   ```sql
   CREATE DATABASE lms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Import the database schema:
   ```bash
   mysql -u root -p lms_db < database.sql
   ```

### 2. Configuration

1. Update database credentials in `config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'lms_db');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

2. Update site URL in `config.php`:
   ```php
   define('SITE_URL', 'http://your-domain.com/lms');
   ```

3. Set proper file permissions:
   ```bash
   chmod 755 assets/uploads/
   chmod 755 assets/uploads/avatars/
   ```

### 3. Web Server Configuration

#### Apache (.htaccess)
Create `.htaccess` in the lms directory:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net; img-src 'self' data:; font-src 'self' cdn.jsdelivr.net"

# Prevent access to sensitive files
<Files "*.sql">
    Order allow,deny
    Deny from all
</Files>
```

#### Nginx
Add to your server configuration:
```nginx
location /lms/ {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}

# Security headers
add_header X-Content-Type-Options nosniff;
add_header X-Frame-Options DENY;
add_header X-XSS-Protection "1; mode=block";
```

### 4. Email Configuration

For password reset and email verification, configure SMTP in `config.php`:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
```

### 5. SSL Certificate (Recommended)

Install SSL certificate for HTTPS:
```bash
# Using Let's Encrypt
certbot --apache -d your-domain.com
```

### 6. Initial Setup

1. Access the LMS at `http://your-domain.com/lms`
2. Register the first admin user
3. Login to admin panel to configure system settings

## Default Admin Account

- **Email**: admin@lms.com
- **Password**: admin123
- **⚠️ Change this password immediately after first login!**

## File Structure

```
lms/
├── index.php                 # Main entry point
├── config.php               # Configuration settings
├── database.sql             # Database schema
├── README.md               # This file
├── includes/
│   ├── database.php         # Database connection
│   ├── functions.php        # Utility functions
│   ├── auth.php            # Authentication functions
│   └── security.php        # Security functions
├── auth/
│   ├── login.php           # Login page
│   ├── register.php        # Registration page
│   └── logout.php          # Logout script
├── admin/
│   └── dashboard.php       # Admin dashboard
├── instructor/
│   └── dashboard.php       # Instructor dashboard
├── student/
│   └── dashboard.php       # Student dashboard
└── assets/
    ├── css/
    │   └── style.css       # Custom styles
    ├── js/
    │   └── auth.js         # Auth JavaScript
    └── uploads/            # File uploads
        └── avatars/        # User avatars
```

## Features Implemented

### ✅ Core Features
- **User Management**: Registration, login, logout, profile management
- **Role System**: Admin, Instructor, Student roles with permissions
- **Course Management**: Create, edit, organize courses
- **Content Delivery**: Text, video, PDF, links, quizzes, assignments
- **Assessment System**: Quizzes with auto-grading, manual assignment grading
- **Communication**: Course announcements, user messaging
- **Progress Tracking**: Lesson completion, course progress
- **File Uploads**: Avatar uploads, assignment submissions

### ✅ Security Features
- **SQL Injection Protection**: All queries use prepared statements
- **Password Security**: password_hash() with proper salt
- **CSRF Protection**: Token-based form protection
- **XSS Prevention**: Input sanitization and output escaping
- **Session Security**: Secure session management
- **Rate Limiting**: Login attempt protection
- **File Upload Security**: Type and size validation

### ✅ User Interface
- **Responsive Design**: Bootstrap 5.2+ mobile-friendly interface
- **Modern UI**: Professional, clean design
- **Role-based Dashboards**: Different interfaces for each user type
- **Form Validation**: Client and server-side validation
- **Flash Messages**: User feedback system

## Database Tables

### Core Tables
- `users` - User accounts and profiles
- `courses` - Course information and metadata
- `enrollments` - Student course enrollments
- `categories` - Course categorization
- `modules` - Course sections/modules
- `lessons` - Individual lessons/content
- `quizzes` - Quiz definitions
- `questions` - Quiz questions
- `quiz_attempts` - Student quiz attempts
- `assignments` - Assignment definitions
- `assignment_submissions` - Student submissions

### Supporting Tables
- `forum_categories` - Discussion forums
- `forum_topics` - Forum discussions
- `forum_replies` - Discussion replies
- `messages` - User messaging
- `announcements` - Course announcements
- `lesson_progress` - Progress tracking
- `activity_log` - User activity logging
- `system_settings` - Configuration settings

## Next Steps for Full Implementation

### Course Management
- Course creation/editing interface
- Content upload and management
- Course publishing workflow

### Assessment System
- Quiz builder interface
- Question bank management
- Assignment submission system
- Gradebook functionality

### Communication
- Discussion forum implementation
- Real-time messaging
- Email notification system

### Administrative Features
- User management interface
- Course approval system
- System configuration
- Reporting and analytics

### Additional Features
- Certificate generation
- Payment processing integration
- Mobile app API
- Advanced reporting
- Multi-language support

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify database credentials in config.php
   - Ensure MySQL service is running
   - Check database exists and user has permissions

2. **Session Errors**
   - Ensure PHP session directory is writable
   - Check session.save_path in php.ini
   - Verify session_start() is called before output

3. **File Upload Issues**
   - Check upload_max_filesize in php.ini
   - Ensure uploads directory is writable
   - Verify POST_MAX_SIZE setting

4. **Email Not Working**
   - Configure SMTP settings
   - Check firewall settings
   - Verify email service credentials

### Debug Mode

Enable error reporting for debugging (remove in production):
```php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

## Security Checklist

- [ ] Change default admin password
- [ ] Configure HTTPS/SSL certificate
- [ ] Set secure file permissions (755 for directories, 644 for files)
- [ ] Disable PHP error display in production
- [ ] Configure proper CSP headers
- [ ] Set up regular database backups
- [ ] Monitor activity logs for suspicious activity
- [ ] Keep PHP and MySQL updated
- [ ] Use strong database passwords
- [ ] Implement rate limiting for sensitive endpoints

## Support

For technical support or questions about the LMS system, please refer to:
- PHP Documentation: https://www.php.net/docs.php
- MySQL Documentation: https://dev.mysql.com/doc/
- Bootstrap Documentation: https://getbootstrap.com/docs/

## License

This LMS system is provided as-is for educational and development purposes. Please ensure compliance with all applicable licenses for third-party components used.
