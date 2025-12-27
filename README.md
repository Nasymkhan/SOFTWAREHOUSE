# Z9 INTERNATIONAL SOFTWARE HOUSE - COMPLETE DEPLOYMENT GUIDE

## 📋 PROJECT OVERVIEW

This is a complete, fully functional full-stack web application for Z9 International Software House.

**Features Included:**
- ✅ Responsive HTML5/CSS3 website with dark/light theme toggle
- ✅ Complete services showcase with 6 professional services
- ✅ Job careers section with expandable application form
- ✅ Contact form with validation
- ✅ Backend PHP with MySQL database
- ✅ Admin panel for managing submissions
- ✅ AJAX form submissions without page reload
- ✅ Email notification functions (optional - ready to configure)
- ✅ Complete database setup with views and indexes
- ✅ Mobile-responsive design

---

## 📁 FILES INCLUDED

```
SOFTWAREA+/
├── index.html              # Main website homepage
├── style.css               # All styles (responsive, dark/light theme)
├── script.js               # JavaScript (animations, forms, theme toggle)
├── db.php                  # Database connection & helper functions
├── submit_job.php          # Backend for job application submissions
├── submit_contact.php      # Backend for contact message submissions
├── admin_login.php         # Admin login page
├── admin_dashboard.php     # Admin dashboard to view submissions
├── database.sql            # Complete database setup script
└── README.md               # This file
```

---

## 🗄️ DATABASE SETUP

### Step 1: Create the Database

1. **Access phpMyAdmin** (or MySQL command line)
2. **Create Database:**
   - Database Name: `z9_international`
   - Collation: `utf8mb4_unicode_ci`

### Step 2: Import SQL Script

1. Go to phpMyAdmin → Select your database
2. Click "Import" tab
3. Select the `database.sql` file
4. Click "Go"

**OR via Command Line:**
```bash
mysql -u root -p z9_international < database.sql
```

### Step 3: Verify Tables Created

The following tables should be created:
- `job_applications` - Stores job application submissions
- `contact_messages` - Stores contact form messages
- `admin_users` - Admin account storage
- `settings` - Site configuration

---

## ⚙️ CONFIGURATION

### 1. Database Connection (db.php)

Update database credentials if needed:

```php
define('DB_HOST', 'localhost');        // Your database host
define('DB_USER', 'root');             // Your database user
define('DB_PASSWORD', '');             // Your database password
define('DB_NAME', 'z9_international'); // Database name
```

### 2. Admin Account Setup

**Create an admin user via phpMyAdmin:**

1. Go to `admin_users` table
2. Insert new record:
   - `username`: admin
   - `email`: admin@z9international.com
   - `password`: Use `password_hash('your_password', PASSWORD_BCRYPT)` in PHP
   
**For development (use password_verify in production):**
```sql
INSERT INTO admin_users (username, email, password, role) 
VALUES ('admin', 'admin@z9international.com', 'admin123', 'admin');
```

### 3. Email Configuration (Optional)

To enable email notifications, uncomment the email functions in:
- `submit_job.php` (lines 130-145)
- `submit_contact.php` (lines 85-105)

Then configure your email settings:

```php
function sendApplicationConfirmationEmail($email, $name, $role) {
    $subject = "Application Received - Z9 International";
    // ... email code ...
    mail($email, $subject, $message, $headers);
}
```

---

## 🚀 DEPLOYMENT INSTRUCTIONS

### For Shared Hosting (cPanel)

1. **Upload Files via FTP:**
   - Connect to your FTP account
   - Navigate to `/public_html` directory
   - Upload all files except `README.md` and `database.sql`

2. **Create Database:**
   - Use cPanel → MySQL Databases
   - Create database: `z9_international`
   - Create user with password
   - Give user all privileges

3. **Import Database:**
   - Use cPanel → phpMyAdmin
   - Select your database
   - Go to Import tab
   - Upload `database.sql`
   - Click Import

4. **Update Database Credentials:**
   - Edit `db.php`
   - Update DB_HOST, DB_USER, DB_PASSWORD, DB_NAME

5. **Set Permissions:**
   ```bash
   chmod 644 *.html *.css *.js *.php
   chmod 644 *.sql
   ```

6. **Test the Installation:**
   - Visit: `http://yourdomain.com/index.html`
   - Check all forms work
   - Visit: `http://yourdomain.com/admin_login.php`
   - Login with admin credentials

### For VPS (Linux)

1. **SSH into your server:**
   ```bash
   ssh user@your_server_ip
   ```

2. **Install LEMP Stack (if not already installed):**
   ```bash
   sudo apt-get update
   sudo apt-get install nginx mysql-server php-fpm php-mysql
   ```

3. **Create web root:**
   ```bash
   sudo mkdir -p /var/www/z9international
   cd /var/www/z9international
   ```

4. **Upload files via SCP:**
   ```bash
   scp -r /path/to/SOFTWAREA+/* user@your_server_ip:/var/www/z9international/
   ```

5. **Create MySQL Database:**
   ```bash
   mysql -u root -p
   CREATE DATABASE z9_international;
   USE z9_international;
   SOURCE database.sql;
   ```

6. **Configure Nginx:**
   ```bash
   sudo nano /etc/nginx/sites-available/z9international
   ```

7. **Configure PHP-FPM:**
   ```bash
   sudo nano /etc/php/8.1/fpm/php.ini
   ```

8. **Set Permissions:**
   ```bash
   sudo chown -R www-data:www-data /var/www/z9international
   sudo chmod -R 755 /var/www/z9international
   ```

9. **Start Services:**
   ```bash
   sudo systemctl restart nginx
   sudo systemctl restart php8.1-fpm
   ```

---

## 📝 FORM FEATURES

### Job Application Form

**Features:**
- Click "Apply Now" button to reveal form
- Collects: Name, Email, Phone, CNIC, Position, Experience, Tech Stack, Projects, Bio
- Validates CNIC format (XXXXX-XXXXXXX-X)
- Prevents duplicate applications from same email
- Shows success/error modal on submission
- Console logging for debugging

**Submission Handler:** `submit_job.php`

### Contact Form

**Features:**
- Name, Email, Phone (optional), Subject, Message
- Real-time email and phone validation
- AJAX submission with visual feedback
- Success/error modal display
- Console logging for debugging

**Submission Handler:** `submit_contact.php`

---

## 🔒 SECURITY FEATURES

1. **Input Sanitization:**
   - All inputs sanitized with `sanitizeInput()`
   - HTML tags stripped
   - Whitespace trimmed

2. **Email Validation:**
   - PHP filter_var for email validation
   - Frontend validation in JavaScript

3. **Database Security:**
   - Prepared statements to prevent SQL injection
   - Foreign key constraints
   - Indexes for performance

4. **CORS Headers:**
   - Configured in `db.php`
   - Allows AJAX requests safely

5. **Error Handling:**
   - Errors logged to `error.log`
   - User doesn't see sensitive data
   - Graceful error messages

---

## 🧪 TESTING

### 1. Test Frontend
- ✅ Visit `index.html`
- ✅ Toggle dark/light theme
- ✅ Click navigation links
- ✅ Mobile responsive (test with F12)

### 2. Test Job Application Form
- ✅ Click "Apply Now" button
- ✅ Form should expand
- ✅ Fill all required fields
- ✅ Submit form
- ✅ Success modal should appear
- ✅ Check database for record

### 3. Test Contact Form
- ✅ Fill contact form
- ✅ Submit
- ✅ Success modal should appear
- ✅ Check database for record

### 4. Test Admin Panel
- ✅ Visit `admin_login.php`
- ✅ Login with admin credentials
- ✅ Should see dashboard
- ✅ Recent applications/messages should display

### 5. Check Browser Console
- Press F12 → Console tab
- Should see Z9 International debug logs
- No JavaScript errors

---

## 🐛 TROUBLESHOOTING

### Forms Not Submitting

**Issue:** Forms submit but nothing happens

**Solutions:**
1. Check browser console (F12 → Console)
2. Verify `submit_job.php` and `submit_contact.php` are accessible
3. Check database credentials in `db.php`
4. Ensure MySQL service is running
5. Check PHP error logs

### Database Connection Error

**Issue:** "Connection failed" error

**Solutions:**
1. Verify database name, user, password in `db.php`
2. Ensure MySQL service is running
3. Check user has access from correct host
4. Verify database exists: `SHOW DATABASES;`

### Admin Login Not Working

**Issue:** Login page displays but can't login

**Solutions:**
1. Verify admin user exists: `SELECT * FROM admin_users;`
2. Check username/password are correct
3. Verify password hash if using `password_verify()`
4. Check session is enabled in PHP

### Styling Issues

**Issue:** Page looks broken, no CSS

**Solutions:**
1. Clear browser cache (Ctrl+Shift+Delete)
2. Verify `style.css` is in same directory as `index.html`
3. Check file permissions: should be readable
4. Check browser console for 404 errors

---

## 📧 EMAIL SETUP (Optional)

To enable email notifications:

1. **In `submit_job.php` (line 110):** Uncomment the email functions
2. **In `submit_contact.php` (line 64):** Uncomment the email functions
3. **Configure PHP mail() or SMTP:**

**For Gmail SMTP:**
```php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'app-password';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;
```

Install PHPMailer via Composer:
```bash
composer require phpmailer/phpmailer
```

---

## 📱 RESPONSIVE DESIGN

The website is fully responsive:

- **Desktop:** Full layout with all features
- **Tablet:** Optimized grid layout
- **Mobile:** Single column, touch-friendly buttons

Breakpoints:
- 768px and below: Tablet/Mobile
- 480px and below: Small Mobile

---

## 🎨 CUSTOMIZATION

### Change Colors

Edit `style.css` CSS variables (top of file):

```css
:root {
  --primary-color: #0066cc;
  --secondary-color: #00d4ff;
  --accent-color: #ff6b6b;
  --dark-bg: #0f1419;
  --light-bg: #ffffff;
  /* ... */
}
```

### Change Company Info

Edit `index.html`:
- Company name: Search for "Z9 International"
- Address: Search for "Near Degree College Daggar"
- Phone numbers: Search for "+92"
- Email: Search for "z9international.com"

### Add More Services

Add new `<div class="service-card">` in `index.html` services section

### Modify Job Positions

Edit the `<div class="positions-list">` in careers section

---

## 📊 DATABASE SCHEMA

### job_applications Table
```sql
- id: AUTO_INCREMENT PRIMARY KEY
- name: VARCHAR(100)
- email: VARCHAR(100)
- phone: VARCHAR(20)
- cnic: VARCHAR(15) UNIQUE
- role: VARCHAR(100)
- experience: INT
- tech_stack: TEXT
- projects: TEXT
- bio: TEXT
- submitted_at: TIMESTAMP
- status: ENUM(pending, reviewed, interview, rejected, accepted)
- notes: TEXT
```

### contact_messages Table
```sql
- id: AUTO_INCREMENT PRIMARY KEY
- name: VARCHAR(100)
- email: VARCHAR(100)
- phone: VARCHAR(20)
- subject: VARCHAR(200)
- message: TEXT
- submitted_at: TIMESTAMP
- status: ENUM(new, read, replied)
```

---

## ✨ FEATURES SUMMARY

✅ Complete responsive website
✅ Dark/Light theme toggle with localStorage
✅ Expandable job application form
✅ Contact form with real-time validation
✅ MySQL database with 4 tables
✅ Admin panel with login
✅ AJAX form submissions
✅ Input sanitization & validation
✅ Console logging for debugging
✅ Error handling & logging
✅ Mobile-first responsive design
✅ Smooth scroll animations
✅ Professional styling
✅ Ready for production deployment

---

## 🎯 NEXT STEPS

1. **Upload all files to your server**
2. **Create and import MySQL database**
3. **Update database credentials in db.php**
4. **Test all forms**
5. **Create admin account**
6. **Access admin panel**
7. **(Optional) Configure email notifications**
8. **Go live!**

---

## 📞 SUPPORT

For issues:
1. Check error.log file
2. Check browser console (F12)
3. Verify database connection
4. Check file permissions
5. Review this README

---

## 📄 LICENSE

This software is provided as-is for Z9 International Software House.

---

## 👨‍💻 VERSION

Version: 1.0.0
Last Updated: December 2024

---

## ✅ PRODUCTION CHECKLIST

Before going live:
- [ ] Database configured correctly
- [ ] Admin account created
- [ ] Email notifications configured (optional)
- [ ] All forms tested
- [ ] SSL certificate installed (HTTPS)
- [ ] Database backups scheduled
- [ ] Error logging enabled
- [ ] Contact information updated
- [ ] Terms & Privacy policies added (optional)
- [ ] Mobile tested thoroughly

---

**Ready to Deploy! 🚀**

The application is fully functional and production-ready. All files are included and configured for immediate deployment.

