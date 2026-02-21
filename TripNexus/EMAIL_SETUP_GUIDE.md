# TripNexus - Email Notification System Setup Guide

## ✅ Implementation Complete!

All components of the email notification system have been successfully installed and configured.

---

## 🔐 IMPORTANT: Configure Gmail Credentials

Before testing, you **MUST** update the Gmail credentials in your config file:

### Step 1: Update Mail Configuration
Edit the file: `config/mail_config.php`

```php
<?php
define('MAIL_USERNAME', 'your-email@gmail.com');      // ← Change this
define('MAIL_PASSWORD', 'your-16-digit-app-password'); // ← Change this
define('MAIL_FROM_EMAIL', 'your-email@gmail.com');    // ← Change this
```

### Step 2: Get Your Gmail App Password
1. Go to [Google Account](https://myaccount.google.com)
2. Navigate to **Security** in the left menu
3. Look for **App passwords** (If you don't see it, enable 2-Step Verification first)
4. Select **Mail** and **Windows Computer**
5. Google will generate a **16-character password**
6. Copy this password and paste it in `mail_config.php`

---

## 📁 File Structure Created

```
SGP/
├── config/
│   └── mail_config.php                 (Gmail SMTP Configuration)
├── migrations/
│   ├── 001_create_migrations_table.sql
│   ├── 002_add_email_verification_columns.sql
│   ├── 003_create_notifications_table.sql
│   ├── 004_create_bookings_table.sql
│   └── run_migrations.php              (Migration Runner)
├── includes/
│   ├── EmailService.php                (Email Handler Class)
│   └── email_templates/
│       ├── verification_email.html      (Verification Code Email)
│       ├── password_reset_email.html    (Password Reset Email)
│       ├── booking_confirmation_email.html
│       └── order_notification_email.html
├── logs/
│   └── emails/                         (Email Activity Logs)
├── register.php                        (MODIFIED - Email Verification)
├── register.html                       (Unchanged)
├── login.php                           (MODIFIED - Check Email Verified)
├── verify_email.html                   (NEW - Verification Code Input)
├── verify_email.php                    (NEW - Verification Handler)
├── resend_verification.php             (NEW - Resend Code Handler)
├── reset_password_handler.php          (MODIFIED - Send Email)
├── update_password.php                 (MODIFIED - Send Confirmation)
└── dashboard.php                       (MODIFIED - Show Notifications)
```

---

## 🔄 USER FLOWS IMPLEMENTED

### 1. Registration & Email Verification
```
User Registers → Verification Code Generated → Email Sent → 
User Enters Code → Email Verified → User Can Login
```

**Files Involved:**
- `register.html` (form)
- `register.php` (generates code, sends email)
- `verify_email.html` (code input form)
- `verify_email.php` (validates code)
- `resend_verification.php` (resend code)

---

### 2. Login with Verification Check
```
User Logs In → Check if Email Verified → 
If Not Verified: Show Alert & Redirect → 
If Verified: Create Session → Dashboard
```

**Files Involved:**
- `login.html` (form)
- `login.php` (check verification status)

---

### 3. Password Reset Flow
```
User Clicks Forgot Password → Enter Email → Reset Link Sent → 
User Clicks Link → New Password Page → Update Password → 
Confirmation Email
```

**Files Involved:**
- `forgot_password.html` (form)
- `reset_password_handler.php` (generate token, send email)
- `new_password.html` (new password form)
- `update_password.php` (validate token, update password)

---

### 4. Dashboard Notifications
```
All Emails Sent → Notifications Saved to DB → 
Display in Dashboard with Badges & Icons
```

**Files Involved:**
- `dashboard.php` (display notifications)
- Database: `notifications` table

---

## 📧 EMAIL TEMPLATES

All email templates are professionally designed with:
- ✅ TripNexus branding
- ✅ Mobile-responsive HTML
- ✅ Clear call-to-action buttons
- ✅ Security notices
- ✅ Professional styling

**Templates:**
1. **Verification Email** - 6-digit code with 15-minute expiry
2. **Password Reset Email** - Reset link with 60-minute expiry
3. **Booking Confirmation** - Order details and reference number
4. **Order Notification** - Status updates and tracking info

---

## 🗂️ Database Schema

### Users Table (Enhanced)
```sql
- is_verified (BOOLEAN)
- email_verified_at (TIMESTAMP)
- verification_code (VARCHAR)
- verification_code_expiry (TIMESTAMP)
- reset_token (VARCHAR)
- token_expiry (TIMESTAMP)
```

### Notifications Table (NEW)
```sql
- id (PRIMARY KEY)
- user_id (FOREIGN KEY)
- type (verification, password_reset, booking, order)
- subject (VARCHAR)
- message (TEXT)
- email_sent_at (TIMESTAMP)
- is_read (BOOLEAN)
- created_at (TIMESTAMP)
```

### Bookings Table (NEW)
```sql
- id (PRIMARY KEY)
- user_id (FOREIGN KEY)
- service_type (flight, bus, train, hotel)
- destination (VARCHAR)
- booking_date (DATE)
- travel_date (DATE)
- status (pending, confirmed, cancelled)
- price (DECIMAL)
```

---

## 🧪 TESTING GUIDE

### Test 1: Email Verification
1. Go to `http://localhost/SGP/register.html`
2. Fill in the registration form with a **real email** (use your Gmail)
3. Submit registration
4. You should receive a verification code email
5. Enter the code in the verification page
6. You should see "Email verified successfully!"
7. Now you can log in

### Test 2: Login with Unverified Email
1. Create a user account
2. Don't verify the email
3. Try to log in with that email
4. You should see "Please verify your email first"

### Test 3: Password Reset
1. Go to `http://localhost/SGP/forgot_password.html`
2. Enter your email
3. Submit
4. Check your email for reset link
5. Click the link
6. Enter new password
7. You should see "Password updated successfully!"

### Test 4: Notifications in Dashboard
1. Log in to an account
2. Go to dashboard
3. You should see a "Notifications" card
4. It should show recent emails sent to you (verification, password reset, etc.)

---

## ⚙️ CONFIGURATION NOTES

### Verification Code
- **Length:** 6 digits
- **Expiry:** 15 minutes (configurable in `mail_config.php`)
- **Format:** Random numeric (e.g., 123456)

### Password Reset Token
- **Length:** 64 characters (32 bytes hex)
- **Expiry:** 60 minutes (configurable in `mail_config.php`)
- **Format:** Secure random hex string

### Email Logs
- **Location:** `logs/emails/` directory
- **Format:** Daily log files (YYYY-MM-DD.log)
- **Contains:** Timestamp, Status, Email, Message

### Security Features
✅ Passwords hashed with `PASSWORD_DEFAULT`
✅ Verification codes expire automatically
✅ Reset tokens expire automatically
✅ SQL injection protection (prepared statements)
✅ XSS protection (htmlspecialchars)
✅ Email validation (both client & server)

---

## 🚀 QUICK START

### 1. Configure Gmail Credentials
Edit `config/mail_config.php` with your Gmail app password

### 2. Migrations Already Run
All database tables are created. No action needed.

### 3. Start Using
- Registration with email verification: `register.html`
- Login: `login.html`
- Forgot password: `forgot_password.html`
- Dashboard: `dashboard.php` (after login)

---

## 🔍 TROUBLESHOOTING

### Email Not Sending?
1. Check if Gmail credentials are correct in `config/mail_config.php`
2. Check email logs in `logs/emails/` directory
3. Verify 2-Step Verification is enabled on Gmail account
4. Verify you're using a 16-character **App Password** (not regular password)

### Verification Code Not Arriving?
1. Check spam/junk folder
2. Wait 30 seconds (email delay)
3. Check email logs for errors
4. Use "Resend Code" button

### Can't Login After Verification?
1. Verify the email address is correct
2. Check if `is_verified` column has been set to `true` in database
3. Clear browser cookies and try again

### Database Errors?
1. Run migrations again: `php migrations/run_migrations.php`
2. Check Supabase connection in `db.php`
3. Verify PostgreSQL is running

---

## 📞 SUPPORT

For any issues or improvements:
1. Check email logs: `logs/emails/[date].log`
2. Verify database schema
3. Test SMTP connection with credentials
4. Verify all required files are created

---

## ✨ FEATURES SUMMARY

| Feature | Status | Implementation |
|---------|--------|-----------------|
| Email Verification | ✅ | Automatic 6-digit code |
| Password Reset | ✅ | Secure token-based |
| Booking Confirmation | ✅ | HTML template ready |
| Order Notifications | ✅ | HTML template ready |
| Notification Dashboard | ✅ | Real-time display |
| Email Logs | ✅ | Daily log files |
| Database Migration | ✅ | Automatic runner |
| Gmail SMTP | ✅ | TLS encrypted |

---

## 🎉 YOU'RE ALL SET!

The email notification system is fully implemented and ready to use. Just configure your Gmail credentials and start sending emails!

For questions or customizations, ensure all files match the structure above.

**Happy Coding!** 🚀
