# Z9 International - Unified Authentication System

## Overview

A complete, production-ready authentication system for SOFTWAREA+ (Z9 Software House) that enables:

✅ **Unified User Accounts** - One login across all Z9 projects  
✅ **Complete User Profiles** - Name, Country, Location (required), Profile Pictures  
✅ **Secure Authentication** - Bcrypt password hashing, prepared statements  
✅ **Cross-Platform Support** - Z9 Software House, Z9 Mine, NASYM, future projects  
✅ **Session Management** - Remember me, 30-day tokens, automatic expiry  
✅ **Security Features** - Brute force protection, login attempt logging, IP tracking  
✅ **Professional UI** - Modern, responsive login/signup pages  
✅ **User Dashboard** - Profile management, activity history, applications tracking  

---

## 📁 Files Created

### Frontend Files

| File | Purpose | Status |
|------|---------|--------|
| `signup.html` | User registration page with validation | ✅ Complete |
| `login.html` | User login page with remember me | ✅ Complete |
| `dashboard.html` | User profile dashboard | ✅ Complete |
| `auth.css` | Authentication styling (login/signup) | ✅ Complete |
| `auth.js` | Shared authentication utilities | ✅ Complete |

### Backend Files

| File | Purpose | Status |
|------|---------|--------|
| `auth_config.php` | Database connection & security functions | ✅ Complete |
| `register.php` | User registration handler | ✅ Complete |
| `login.php` | User login handler | ✅ Complete |
| `profile.php` | Profile management & picture upload | ✅ Complete |

### Database Files

| File | Purpose | Status |
|------|---------|--------|
| `database_auth_update.sql` | User tables, sessions, history tables | ✅ Complete |

### Updated Files

| File | Changes | Status |
|------|---------|--------|
| `index.html` | Added login/signup buttons, user menu | ✅ Updated |
| `script.js` | Added auth check & logout function | ✅ Updated |
| `style.css` | Added auth navbar styles | ✅ Updated |

---

## 🗄️ Database Schema

### Users Table
Stores all user account information:
```sql
- id (Primary Key)
- username (UNIQUE)
- email (UNIQUE)
- password_hash (bcrypt)
- full_name
- country
- location (REQUIRED)
- profile_pic_url
- status (active/suspended/inactive)
- email_verified
- created_at, updated_at, last_login
- login_attempts (for brute force protection)
```

### User Sessions Table
Manages active sessions and "Remember Me" tokens:
```sql
- id (Primary Key)
- user_id (Foreign Key)
- session_token (secure 64-char token)
- platform (z9-software-house, z9-mine, nasym, etc.)
- user_agent, ip_address
- expires_at (30 days from creation)
```

### Login History Table
Security audit trail of all login attempts:
```sql
- id (Primary Key)
- user_id, username, email
- ip_address, user_agent
- platform, login_status
- failure_reason (if failed)
- login_at timestamp
```

### User Project Access Table
Track which projects user can access:
```sql
- user_id, project_name
- access_level (viewer, contributor, admin)
- granted_at timestamp
```

### Profile Update History Table
Track all profile changes for audit/recovery:
```sql
- user_id, field_name
- old_value, new_value
- updated_at timestamp
```

---

## 🔐 Security Features

### Password Security
- **Bcrypt Hashing**: Cost factor 12 (industry standard)
- **Strength Validation**: 
  - Minimum 8 characters
  - 1 uppercase letter required
  - 1 lowercase letter required
  - 1 number required
  - Real-time strength indicator on signup

### SQL Injection Prevention
- **Prepared Statements**: All database queries use parameterized statements
- **Input Sanitization**: `htmlspecialchars()`, `strip_tags()`, `trim()`

### Session Security
- **Secure Tokens**: 64-character random tokens from `random_bytes()`
- **Session Expiry**: 30-day expiration with automatic cleanup
- **Device Tracking**: User agent & IP address logging
- **Remember Me**: Optional 30-day persistent login

### Brute Force Protection
- **Login Attempt Counting**: Track failed attempts
- **Auto-Suspension**: Account suspended after 5 failed attempts
- **Attempt Logging**: All attempts logged to database

### XSS Prevention
- **Output Escaping**: HTML escaping with `htmlspecialchars()`
- **Input Validation**: Email & username format validation
- **CSRF Ready**: Can be extended with CSRF tokens

---

## 🚀 Installation Guide

### Step 1: Import Database
```bash
mysql -u root -p z9_international < database_auth_update.sql
```

### Step 2: Update Credentials
Edit `auth_config.php`:
```php
$db_host = 'localhost';
$db_user = 'your_db_user';
$db_password = 'your_db_password';
$db_name = 'z9_international';
```

### Step 3: Create Uploads Directory
```bash
mkdir -p uploads/profile_pics
chmod 755 uploads/profile_pics
```

### Step 4: Set Permissions
```bash
chmod 644 *.php
chmod 755 uploads/
```

### Step 5: Test Login
- Navigate to: `http://localhost/signup.html`
- Create test account
- Login and access dashboard

---

## 📝 API Endpoints

### Register User
```
POST /register.php
Content-Type: application/json

{
  "username": "john_doe",
  "email": "john@example.com",
  "password": "SecurePass123",
  "full_name": "John Doe",
  "country": "Pakistan",
  "location": "Daggar, Buner"
}

Response (201):
{
  "success": true,
  "message": "Account created successfully!",
  "session_token": "abc123...",
  "user": { ... }
}
```

### Login User
```
POST /login.php
Content-Type: application/json

{
  "username": "john_doe",
  "password": "SecurePass123",
  "platform": "z9-software-house"
}

Response (200):
{
  "success": true,
  "message": "Login successful!",
  "session_token": "xyz789...",
  "user": { ... }
}
```

### Get Profile
```
GET /profile.php?token=SESSION_TOKEN

Response (200):
{
  "success": true,
  "user": {
    "id": 1,
    "username": "john_doe",
    "email": "john@example.com",
    "full_name": "John Doe",
    "country": "Pakistan",
    "location": "Daggar, Buner",
    "profile_pic_url": "/uploads/profile_pics/...",
    "status": "active",
    "created_at": "2024-12-25 10:30:00",
    "last_login": "2024-12-25 15:45:00"
  }
}
```

### Update Profile
```
POST /profile.php
Content-Type: application/json
Authorization: Bearer SESSION_TOKEN

{
  "full_name": "John Updated",
  "country": "USA",
  "location": "New York, NY"
}

Response (200):
{
  "success": true,
  "message": "Profile updated successfully!"
}
```

### Upload Profile Picture
```
POST /profile.php
Content-Type: multipart/form-data
Authorization: Bearer SESSION_TOKEN

[binary image file]

Response (200):
{
  "success": true,
  "message": "Profile picture updated successfully!",
  "profile_pic_url": "/uploads/profile_pics/profile_1_1703500800.jpg"
}
```

### Logout
```
DELETE /profile.php
Authorization: Bearer SESSION_TOKEN

Response (200):
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

## 🎯 Frontend Features

### Sign Up Page (`signup.html`)
- ✅ Real-time password strength indicator
- ✅ Form validation (email, username format, location required)
- ✅ Password confirmation
- ✅ Terms of Service checkbox
- ✅ Responsive design (mobile-first)
- ✅ Dark/light theme support
- ✅ Success modal on registration
- ✅ Auto-login after signup
- ✅ Redirect to dashboard

### Login Page (`login.html`)
- ✅ Username or email login
- ✅ Remember me (30 days)
- ✅ Forgot password link (placeholder)
- ✅ Brute force protection indicators
- ✅ Account suspension warnings
- ✅ Session-based auto-redirect
- ✅ Security notice (lock icon)
- ✅ Responsive design
- ✅ Dark/light theme support

### Dashboard (`dashboard.html`)
- ✅ User profile display
- ✅ Profile picture upload
- ✅ Edit name, country, location
- ✅ Account statistics
- ✅ Job applications tracking
- ✅ Activity log
- ✅ Settings panel
- ✅ Sidebar navigation
- ✅ Quick actions
- ✅ Responsive layout
- ✅ Dark/light theme

### Navigation Integration
- ✅ Show login/signup buttons when logged out
- ✅ Show user menu with profile when logged in
- ✅ Display user avatar & name
- ✅ Dropdown with Dashboard & Logout links
- ✅ Persistent session check

---

## 🔄 User Flow

### Registration Flow
1. User visits `/signup.html`
2. Fills form (username, email, password, name, country, location)
3. Submits form → `register.php`
4. Validation → Password hashing → Database insert
5. Session token created
6. Auto-login → Redirect to `/dashboard.html`
7. User can now access all platforms

### Login Flow
1. User visits `/login.html`
2. Enters username/email + password
3. Submits → `login.php`
4. Validation → Password verify → Session creation
5. Token stored in localStorage
6. Redirect to `/dashboard.html` or previous page
7. Session token expires in 30 days (if remember me checked)

### Profile Update Flow
1. User clicks "Edit Profile" in dashboard
2. Modal opens with current info
3. Edits name/country/location
4. Optionally uploads new profile picture
5. Submits → `profile.php`
6. Updates database + logs change history
7. Profile update reflected immediately
8. Photo saved to `/uploads/profile_pics/`

---

## 📊 Database Views

Helpful views for reporting:

### `active_users`
All active user accounts with key info

### `users_by_country`
User distribution by country

### `recent_registrations`
Last 50 registered users

---

## 🛡️ Brute Force Protection

**What happens:**
- Track login attempts in memory
- After 5 failed attempts:
  - Account automatically suspended
  - Logged to database
  - User cannot login
  - Admin notification (future feature)

**Reset:**
- Manual database update: `UPDATE users SET status='active', login_attempts=0 WHERE id=X`
- Email verification (future feature)
- Admin panel reset (future feature)

---

## 📱 Responsive Design

### Desktop (1200px+)
- Side-by-side auth form and branding
- Full navigation menu
- Full-width dashboard

### Tablet (768px-1199px)
- Adjusted layouts
- Mobile-friendly menus
- Touch-optimized buttons

### Mobile (<768px)
- Single column auth
- Full-screen forms
- Hamburger navigation
- Font size 16px (prevents zoom)

---

## 🌙 Dark Mode

- Automatic dark mode toggle
- Persistent preference in localStorage
- Smooth transitions
- Accessible color contrast
- Supported on all auth pages

---

## ⚙️ Configuration

### Password Requirements
```php
// In auth_config.php - validatePasswordStrength()
- Minimum 8 characters
- 1 uppercase letter
- 1 lowercase letter  
- 1 number
- Optional: Special characters (for future)
```

### Session Duration
```php
// In auth_config.php - startUserSession()
$expires = date('Y-m-d H:i:s', strtotime('+30 days'));
```

### Max Login Attempts
```php
// In login.php
if ($user['login_attempts'] >= 5) {
    // Suspend account
}
```

### File Upload Limits
```php
// In profile.php
$max_file_size = 5 * 1024 * 1024; // 5MB
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
```

---

## 🔮 Future Enhancements

### Phase 2 (Planned)
- [ ] Email verification on signup
- [ ] Forgot password / Reset password
- [ ] Two-factor authentication (2FA)
- [ ] Social login (Google, GitHub)
- [ ] Export profile as PDF
- [ ] Account deletion with data cleanup
- [ ] Activity notifications

### Phase 3 (Planned)
- [ ] OAuth 2.0 support
- [ ] API key generation for apps
- [ ] Single Sign-On (SSO) across projects
- [ ] Advanced analytics dashboard
- [ ] User roles & permissions
- [ ] Account recovery options
- [ ] Session device management

### Phase 4 (Planned)
- [ ] Biometric login
- [ ] Hardware security keys
- [ ] Session replay protection
- [ ] Anomaly detection
- [ ] Admin user management
- [ ] Audit reporting

---

## 🐛 Troubleshooting

### Account Locked After Failed Logins
**Solution:** Reset via database:
```sql
UPDATE users SET status='active', login_attempts=0 WHERE username='username';
```

### Profile Picture Not Uploading
**Check:**
1. `/uploads/profile_pics/` directory exists
2. Directory has 755 permissions
3. File is JPG/PNG/GIF
4. File size < 5MB

### Session Expires Immediately
**Check:**
1. Database connection working
2. Sessions table populated
3. DateTime in database correct
4. Token in localStorage

### Login Page Redirects on Load
**Reason:** Session still valid in localStorage  
**Solution:** Clear localStorage manually:
```javascript
localStorage.clear();
```

---

## 📊 Testing Credentials

**Default Admin (if created):**
- Username: `admin`
- Email: `admin@z9.com`
- Password: `Admin@123` (change immediately!)

**Test User:**
Create via signup page for full testing

---

## 📧 Support & Contact

For issues with authentication system:
- Email: support@z9international.com
- Contact form: Available on main website
- Dashboard: Support ticket system (future)

---

## 📄 License

Z9 International Software House © 2024
All rights reserved.

---

## 🎉 Summary

**Complete Unified Authentication System Ready for Production:**

✅ Secure password hashing (bcrypt)  
✅ Session management (30 days)  
✅ Profile picture uploads  
✅ Brute force protection  
✅ Login history tracking  
✅ Cross-platform support  
✅ Responsive design  
✅ Dark/light theme  
✅ Professional UI  
✅ Easy installation  

**Ready to attract and manage user talent across all Z9 platforms!** 🚀

