# 🔔 Email Alerts + Password Protected Dashboard Setup

Complete guide to configure visitor email notifications and secure your analytics dashboard.

## What You Now Have

✅ **Email Notifications** - Get instant alerts when visitors arrive
✅ **Password Protected Dashboard** - Only you can view `/analytics/`
✅ **API Key Protection** - Dashboard requires valid API key
✅ **Rate Limiting** - Prevents automated spam
✅ **Bot Detection** - Blocks crawlers and bots
✅ **Security Logging** - Tracks all suspicious activity

---

## 📧 Setup Email Notifications (5 minutes)

### Step 1: Edit Configuration File

Open: `php/config.php`

Find these lines:
```php
// YOUR EMAIL (where to send alerts)
define('ADMIN_EMAIL', 'your-email@gmail.com');

// Enable notifications
define('SEND_EMAIL_NOTIFICATIONS', true);
```

**Change to:**
```php
define('ADMIN_EMAIL', 'your-real-email@gmail.com');
define('SEND_EMAIL_NOTIFICATIONS', true);
```

### Step 2: Configure Email Settings

**For Gmail:**

Option A - Simple (Less Secure)
```php
// In config.php, set:
define('USE_SMTP', false);
```

Then enable "Less Secure Apps":
1. Go to: https://myaccount.google.com/lesssecureapps
2. Turn ON "Allow less secure apps"

Option B - Secure (Recommended)
```php
// In config.php, set:
define('USE_SMTP', true);
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');  // NOT your Gmail password!
```

To get App Password:
1. Enable 2-factor authentication on Google Account
2. Go to: https://myaccount.google.com/apppasswords
3. Select "Mail" and "Windows Computer"
4. Copy the generated password
5. Paste into `SMTP_PASS` in config.php

**For Other Email Providers:**

Gmail:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
```

Outlook/Hotmail:
```php
define('SMTP_HOST', 'smtp-mail.outlook.com');
define('SMTP_PORT', 587);
```

Yahoo:
```php
define('SMTP_HOST', 'smtp.mail.yahoo.com');
define('SMTP_PORT', 587);
```

Zoho:
```php
define('SMTP_HOST', 'smtp.zoho.com');
define('SMTP_PORT', 587);
```

### Step 3: Test Email Sending

Visit any portfolio page to trigger a visitor event. You should receive an email!

Example email you'll get:
```
Subject: [New Portfolio Visitor] Blog Post

New Visitor Alert!

Time: 2026-03-06 14:30:45
Page: /blog-single.html
IP Address: 192.168.1.100
Location: 36.8065°N, 10.1686°E
Browser: Chrome
Language: en-US
Timezone: Africa/Tunis
Referrer: google.com
Session: session_123...
```

---

## 🔐 Setup Password Protection for Dashboard (10 minutes)

### Step 1: Generate Password File

You need to create a `.htpasswd` file with username/password.

**Option A: Using Online Tool (Easiest)**

1. Go to: https://www.web2generators.com/apache-tools/htpasswd-generator
2. Enter:
   - Username: `admin` (or your preferred name)
   - Password: (something strong)
3. Copy the generated string
4. Create a text file with the generated content
5. Name it `.htpasswd`
6. Upload to your server (anywhere OUTSIDE `/analytics/` directory)

**Option B: Using SSH Command (Linux/Mac)**

```bash
# Generate .htpasswd file
htpasswd -c ~/.htpasswd admin

# It will ask for a password - enter it
# Then upload to server via FTP/SFTP
```

**Option C: Using Windows (PowerShell)**

```powershell
# Install tools or use:
# https://www.web2generators.com/apache-tools/htpasswd-generator
# (via web interface)
```

### Step 2: Update .htaccess File

Edit: `analytics/.htaccess`

Find this line:
```apache
AuthUserFile /home/username/.htpasswd
```

**Change to the FULL PATH** where you uploaded `.htpasswd`:

For shared hosting, typically:
```apache
AuthUserFile /home/your-username/.htpasswd
```

For cPanel hosting, use:
```apache
AuthUserFile /home/your-username/.htpasswd
```

For Plesk hosting, use:
```apache
AuthUserFile /var/www/vhosts/your-domain/.htpasswd
```

**To find your path:**
1. Log into cPanel/Plesk
2. Go to File Manager
3. Right-click home directory
4. Click "Properties"
5. Copy the path

### Step 3: Test Password Protection

1. Navigate to: `http://your-site.com/analytics/`
2. You should see a login popup
3. Username: `admin` (or whatever you set)
4. Password: (the password you set)
5. Click OK
6. Dashboard loads!

---

## 🔑 Setup API Key Protection (2 minutes)

### Step 1: Generate Strong API Key

Use an online generator:
- https://www.uuidgenerator.net/
- Or run: `openssl rand -hex 32`
- Or use: `python3 -c "import secrets; print(secrets.token_hex(32))"`

Example: `a7c2e9f1b5d3g8h2j6k9m1n4p7q3r5s8`

### Step 2: Update Configuration

Edit: `php/config.php`

Find:
```php
define('ANALYTICS_API_KEY', 'your_secure_api_key_change_me_12345');
```

Change to your generated key:
```php
define('ANALYTICS_API_KEY', 'a7c2e9f1b5d3g8h2j6k9m1n4p7q3r5s8');
```

### Step 3: Update Dashboard

Edit: `analytics/index.html`

Find (around line 237):
```javascript
const apiKey = 'your_secure_api_key_change_me_12345';
```

Change to SAME key from config.php:
```javascript
const apiKey = 'a7c2e9f1b5d3g8h2j6k9m1n4p7q3r5s8';
```

---

## 🗑️ Setup Data Deletion Protection (2 minutes)

### Step 1: Generate Deletion Key

Create another strong random key (different from API key):
- Option: `x9z2c5v8b1n4m7a0s3d6f9g2h5j8k1l4`

### Step 2: Update Configuration

Edit: `php/config.php`

Find:
```php
define('CLEAR_DATA_SECRET_KEY', 'your_secret_delete_key_change_me_xyz789');
```

Change to your key:
```php
define('CLEAR_DATA_SECRET_KEY', 'x9z2c5v8b1n4m7a0s3d6f9g2h5j8k1l4');
```

### Step 3: Test

1. Go to dashboard at `/analytics/`
2. Click "Clear Data"
3. A confirmation should appear
4. If you click OK, it requires the deletion key
5. If you don't have the right key, deletion fails

---

## 📋 Configuration Checklist

Mark these as complete:

### Email Setup
- [ ] Changed `ADMIN_EMAIL` to your email
- [ ] Set `SEND_EMAIL_NOTIFICATIONS` to `true`
- [ ] Configured SMTP settings (Gmail/Other)
- [ ] Tested by visiting a portfolio page
- [ ] Received test email notification

### Dashboard Password
- [ ] Generated `.htpasswd` file
- [ ] Uploaded `.htpasswd` to server
- [ ] Updated `.htaccess` with correct path
- [ ] Tested login at `/analytics/`
- [ ] Password protection working ✓

### API Key Protection
- [ ] Generated API key
- [ ] Added to `php/config.php`
- [ ] Added to `analytics/index.html`
- [ ] Dashboard loads successfully
- [ ] API key requirement enforced ✓

### Deletion Protection
- [ ] Generated deletion key
- [ ] Added to `php/config.php`
- [ ] Clear Data button requires key
- [ ] Unauthorized deletions blocked ✓

### Security Features
- [ ] Rate limiting enabled (prevents spam)
- [ ] Bot detection active (blocks crawlers)
- [ ] Security logging enabled
- [ ] `/data/` directory protected
- [ ] All PHP files require valid authentication

---

## 🚀 What Happens Now

### When Someone Visits Your Portfolio:

1. **Immediately** 🔔
   - Visitor tracker captures data
   - Email sent to your inbox with visitor details
   - Data stored in `/data/visitors.json`

2. **Later** 📊
   - You log into `/analytics/` with password
   - Dashboard shows real-time statistics
   - See visitor locations on map
   - Export data for analysis

3. **Security** 🔒
   - No one can access dashboard without password
   - No one can access data API without key
   - No one can delete data without secret key
   - Bots and spam are blocked
   - All attempts are logged

---

## 📧 Email Notification Examples

### Visitor from Google Search
```
Subject: [New Portfolio Visitor] Home Page

Time: 2026-03-06 14:30:45
Page: /index.html
IP: 203.0.113.42
Location: 33.5731°N, -112.2202°W (Phoenix, USA)
Browser: Chrome
Referrer: google.com
```

### Visitor from Direct Link
```
Subject: [New Portfolio Visitor] Portfolio Single 3

Time: 2026-03-06 15:45:22
Page: /portfolio-single-3.html
IP: 192.0.2.15
Location: 40.7128°N, -74.0060°W (New York, USA)
Browser: Safari
Referrer: direct
```

### Visitor from LinkedIn
```
Subject: [New Portfolio Visitor] Blog Post

Time: 2026-03-06 16:12:05
Page: /blog-single.html
IP: 198.51.100.89
Location: 48.8566°N, 2.3522°E (Paris, France)
Browser: Firefox
Referrer: linkedin.com
```

---

## 🛡️ Security Summary

| Feature | What It Does | How to Enable |
|---------|--------------|--------------|
| **Email Alerts** | Notifies you of new visitors | Set email in config.php |
| **Password Protection** | Only you can view dashboard | Upload .htpasswd file |
| **API Key** | Prevents unauthorized API access | Update config.php & index.html |
| **Delete Protection** | Requires secret key to clear data | Set key in config.php |
| **Rate Limiting** | Prevents spam/DoS attacks | Enabled by default |
| **Bot Blocking** | Rejects crawlers/bots | Enabled by default |
| **Security Logging** | Tracks suspicious activity | Auto-logged in `/data/` |

---

## 🔧 Troubleshooting

### Emails Not Arriving

**Problem**: Configured email but not receiving notifications

**Solutions**:
1. Check spam/junk folder
2. Verify email address in config.php
3. Check `SEND_EMAIL_NOTIFICATIONS` is `true`
4. Verify PHP can send mail (ask hosting provider)
5. Test with: `php -r "mail('test@example.com', 'Test', 'Test');"`

### Can't Access Dashboard

**Problem**: Login prompt appears but password doesn't work

**Solutions**:
1. Verify username is correct (usually `admin`)
2. Verify `.htpasswd` file is on server
3. Check path in `.htaccess` is correct
4. Ensure `.htaccess` is in `/analytics/` directory
5. Check file permissions (644 for files, 755 for dirs)

### API Key Error

**Problem**: Dashboard shows "API Key incorrect"

**Solutions**:
1. Verify API key in `config.php`
2. Verify SAME key in `analytics/index.html` line 237
3. No typos or extra spaces
4. Reload page (Ctrl+Shift+Delete to clear cache)

### Deletion Blocked

**Problem**: Can't clear data, always says "Unauthorized"

**Solutions**:
1. Verify secret key in `config.php`
2. Key must NOT be the default placeholder
3. Check it's in `CLEAR_DATA_SECRET_KEY` not `API_KEY`
4. Page refresh might help

---

## 💡 Advanced Options

### Custom Email Messages

Edit `php/track-visitor.php` function `sendVisitorNotificationEmail()` to customize email format.

### Custom Password Prompt

Use different username (not `admin`):
1. Create `.htpasswd` with your username
2. Update `AuthName` in `.htaccess`

### Disable Email Alerts for Testing

In `php/config.php`:
```php
define('SEND_EMAIL_NOTIFICATIONS', false);
```

### Check Security Logs

Logs are stored in:
```
/data/security.log     - All security events
/data/deletion_log.txt - Who deleted data and when
/data/rate_limit.json  - Rate limit tracking
```

---

## 🎯 Next Steps

1. ✅ Configure email settings in `config.php`
2. ✅ Generate `.htpasswd` file
3. ✅ Upload and configure in `.htaccess`
4. ✅ Update API key in config.php and dashboard
5. ✅ Test everything works
6. ✅ Delete old documentation files (optional)

---

## 📚 Files Modified

- ✅ `php/config.php` (NEW - configuration file)
- ✅ `php/track-visitor.php` (email sending added)
- ✅ `php/get-analytics.php` (API key check added)
- ✅ `php/clear-analytics.php` (security enhanced)
- ✅ `analytics/.htaccess` (password protection)
- ✅ `analytics/index.html` (API key integration)

---

**You now have:**
- 🔔 Email alerts for every visitor
- 🔐 Password protected dashboard
- 🔑 API key security
- 🛡️ Anti-spam protection
- 📊 Complete analytics system

**Status**: ✅ Ready to Deploy

---

For more info: See `TRACKER_SETUP.md`
