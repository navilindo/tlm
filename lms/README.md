# E-Learning Management System (LMS)

A complete, modular e-learning platform built with PHP 8.0+, MySQL 8.0+, and Bootstrap 5.2+. This LMS provides a comprehensive solution for online education with features similar to Moodle but with a simpler, more customizable architecture.

## 🚀 Features

### Core Functionality
- **Multi-Role System**: Admin, Instructor, and Student roles with appropriate permissions
- **Course Management**: Create, edit, and manage courses with modules and lessons
- **User Management**: Registration, verification, profile management, and role assignment
- **Content Delivery**: Support for text, video, PDF, links, quizzes, and assignments
- **Assessment System**: Quizzes with auto-grading and manual assignment grading
- **Progress Tracking**: Real-time progress monitoring for students and instructors
- **Discussion Forums**: Course-specific forums for student interaction
- **Communication**: Announcements, messaging, and email notifications

### Security Features
- **CSRF Protection**: Form protection against cross-site request forgery
- **SQL Injection Prevention**: All database queries use prepared statements
- **XSS Protection**: Output sanitization for all user-generated content
- **Password Security**: Secure hashing with `password_hash()` and `password_verify()`
- **Session Management**: Secure session handling with timeout and validation
- **Rate Limiting**: Protection against brute force attacks
- **Input Validation**: Comprehensive server-side and client-side validation

### User Experience
- **Responsive Design**: Mobile-friendly Bootstrap 5.2+ interface
- **Modern UI**: Clean, professional design with intuitive navigation
- **Dashboard Analytics**: Role-specific dashboards with relevant statistics
- **File Upload**: Secure file handling with type and size validation
- **Progress Visualization**: Visual progress bars and completion tracking
- **Search Functionality**: Course and content search capabilities

## 📋 Requirements

### Server Requirements
- **PHP**: Version 8.0 or higher
- **MySQL**: Version 8.0 or higher (or MariaDB 10.3+)
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP Extensions**: PDO, PDO_MySQL, mbstring, openssl, json, session

### Browser Support
- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## 🛠️ Installation Guide

### Step 1: Download and Setup

1. **Clone or Download** the LMS files to your web server directory:
   ```bash
   # For web server document root (e.g., /var/www/html/lms or C:\xampp\htdocs\lms)
   ```

2. **Set Permissions** (Linux/Mac):
   ```bash
   chmod 755 lms/
   chmod 644 lms/*.php
   chmod 755 lms/includes/
   chmod 755 lms/assets/
   chmod 755 lms/assets/uploads/
   ```

### Step 2: Database Setup

1. **Create Database**:
   ```sql
   CREATE DATABASE lms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Import Database Schema**:
   ```bash
   mysql -u username -p lms_db < database.sql
   ```
   Or import `database.sql` through phpMyAdmin.

### Step 3: Configuration

1. **Edit Configuration File** (`config.php`):
   ```php
   // Database Configuration
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'lms_db');
   define('DB_USER', 'your_db_username');
   define('DB_PASS', 'your_db_password');
   
   // Site Configuration
   define('SITE_URL', 'http://localhost/lms');
   define('SITE_NAME', 'Your LMS Name');
   define('SITE_DESCRIPTION', 'Your LMS Description');
   
   // Email Configuration (for notifications)
   define('SMTP_HOST', 'localhost');
   define('SMTP_PORT', 587);
   define('SMTP_USERNAME', 'your_email@example.com');
   define('SMTP_PASSWORD', 'your_email_password');
   ```

2. **Create Upload Directories**:
   ```bash
   mkdir -p assets/uploads/{avatars,courses,assignments}
   chmod 755 assets/uploads/
   chmod 755 assets/uploads/*/
   ```

### Step 4: Web Server Configuration

#### Apache Configuration
Ensure mod_rewrite is enabled and create `.htaccess`:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security Headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net; img-src 'self' data: *.gravatar.com; font-src 'self' cdn.jsdelivr.net"
```

#### Nginx Configuration
```nginx
location /lms {
    try_files $uri $uri/ /index.php?$query_string;
    
    # Security headers
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options DENY;
    add_header X-XSS-Protection "1; mode=block";
    add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload";
}
```

### Step 5: Initial Setup

1. **Access the LMS** at `http://your-domain.com/lms/`

2. **Login with Default Admin Account**:
   - Email: `admin@lms.com`
   - Password: `admin123`

3. **Change Default Password** immediately after first login

4. **Configure System Settings** through the admin panel

5. **Create Categories** for organizing courses

6. **Set up Email Configuration** for notifications

## 👥 Demo Accounts

The system comes with pre-configured demo accounts:

| Role | Email | Password | Description |
|------|-------|----------|-------------|
| Admin | admin@lms.com | admin123 | Full system access |
| Instructor | instructor@lms.com | instructor123 | Course creation and management |
| Student | student@lms.com | student123 | Course enrollment and learning |

## 📚 User Guide

### For Administrators
1. **User Management**: Create, edit, and manage user accounts
2. **Course Approval**: Review and approve instructor course submissions
3. **System Configuration**: Configure system-wide settings
4. **Reports & Analytics**: View system statistics and user activity
5. **Database Management**: Backup and maintain the database

### For Instructors
1. **Course Creation**: Build comprehensive courses with modules and lessons
2. **Content Management**: Upload videos, PDFs, create quizzes and assignments
3. **Student Management**: View enrolled students and their progress
4. **Grading**: Review assignments and manage quiz attempts
5. **Analytics**: Track course performance and student engagement

### For Students
1. **Course Enrollment**: Browse and enroll in available courses
2. **Learning Progress**: Track completion through course materials
3. **Assignments**: Submit assignments and take quizzes
4. **Discussion**: Participate in course forums and discussions
5. **Certificates**: Earn certificates upon course completion

## 🔧 Configuration Options

### System Settings
- **Registration**: Enable/disable new user registration
- **Email Verification**: Require email verification for new accounts
- **File Upload**: Configure maximum file sizes and allowed types
- **Session Management**: Set session timeout and security options
- **Course Approval**: Require admin approval for published courses

### Course Settings
- **Enrollment Types**: Public, private, or invite-only courses
- **Pricing**: Free or paid courses with multiple currencies
- **Prerequisites**: Set course prerequisites and difficulty levels
- **Completion Criteria**: Configure certificate generation requirements

## 🔒 Security Considerations

### Production Security Checklist
- [ ] Change all default passwords
- [ ] Enable HTTPS/SSL certificates
- [ ] Configure secure session handling
- [ ] Set up regular database backups
- [ ] Enable server-side input validation
- [ ] Configure firewall rules
- [ ] Regular security updates
- [ ] Monitor access logs
- [ ] Set up intrusion detection
- [ ] Configure rate limiting

### File Permissions
```bash
# Recommended permissions
find lms/ -type f -exec chmod 644 {} \;
find lms/ -type d -exec chmod 755 {} \;
chmod 600 config.php
chmod 755 assets/uploads/
```

## 📊 Database Schema

The LMS uses a normalized database schema with the following main tables:

- **users**: User accounts and authentication
- **categories**: Course categories for organization
- **courses**: Course information and metadata
- **enrollments**: Student course enrollments
- **modules**: Course sections/chapters
- **lessons**: Individual lessons within modules
- **quizzes**: Quiz definitions and settings
- **questions**: Quiz questions and answers
- **assignments**: Assignment definitions
- **submissions**: Student assignment submissions
- **forums**: Discussion forum structure
- **messages**: Private messaging system

## 🎨 Customization

### Styling
- Modify `assets/css/style.css` for custom styling
- Use Bootstrap 5.2+ classes for consistency
- Custom CSS variables defined in `:root`

### Functionality
- Add new features in `includes/` directory
- Extend database schema as needed
- Create new dashboard pages following existing patterns

### Templates
- All pages follow consistent HTML structure
- Use existing CSS classes for styling
- Responsive design with Bootstrap grid system

## 📈 Performance Optimization

### Database Optimization
- Indexes created for frequently queried columns
- Optimized queries with proper JOINs
- Pagination implemented for large datasets

### Caching
- Session-based caching for user data
- Database query optimization
- Static asset caching recommended

### File Handling
- Secure file upload validation
- Image optimization for course thumbnails
- Video streaming optimization recommended

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Verify database credentials in `config.php`
   - Ensure MySQL service is running
   - Check database user permissions

2. **File Upload Errors**
   - Verify upload directory permissions
   - Check PHP upload limits in `php.ini`
   - Ensure disk space is available

3. **Session Issues**
   - Verify session configuration in `php.ini`
   - Check session directory permissions
   - Ensure cookies are enabled

4. **Email Not Working**
   - Configure SMTP settings in `config.php`
   - Test email server connectivity
   - Check spam folders for notifications

### Error Logs
Enable error logging in `config.php`:
```php
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/error.log');
```

## 📝 Development

### Code Structure
```
lms/
├── config.php              # Main configuration
├── database.sql            # Database schema
├── index.php              # Homepage
├── auth/                  # Authentication pages
├── admin/                 # Admin dashboard
├── instructor/            # Instructor dashboard
├── student/               # Student dashboard
├── courses/               # Course-related pages
├── includes/              # Core functionality
├── assets/                # Static assets
└── api/                   # API endpoints
```

### Adding New Features
1. Follow existing code patterns
2. Use prepared statements for database queries
3. Implement proper validation and sanitization
4. Add CSRF protection to forms
5. Create appropriate database migrations

## 📄 License

This LMS is released under the MIT License. See LICENSE file for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📞 Support

For support and questions:
- Check the troubleshooting section
- Review the code documentation
- Create an issue in the repository

## 🗺️ Roadmap

### Upcoming Features
- Mobile app integration
- Advanced analytics dashboard
- Video conferencing integration
- Certificate verification system
- Multi-language support
- Advanced reporting tools
- Integration with external tools (Zoom, Google Classroom)
- AI-powered content recommendations

### Version History
- **v1.0.0**: Initial release with core LMS functionality
- Features: User management, course creation, basic assessments

---

**Built with ❤️ for educators and learners worldwide**
