# 🎯 QUICK SETUP - Email Alerts + Secure Dashboard

**Done in 10 minutes:**

## Step 1: Email Alerts (2 min)

Edit `php/config.php`:

```php
// Line 6: Your email
define('ADMIN_EMAIL', 'your-email@gmail.com');

// Line 9: Enable notifications
define('SEND_EMAIL_NOTIFICATIONS', true);
```

✅ Done! You'll get email for every visitor.

---

## Step 2: Password Protect Dashboard (5 min)

### A. Generate Password

Go to: https://www.web2generators.com/apache-tools/htpasswd-generator

- Username: `admin`
- Password: (something strong)
- Click "Generate"
- Copy the result

### B. Upload Password File

1. Create file named `.htpasswd`
2. Paste the generated content
3. Upload to your server (ask your host where to put it)
4. Copy the FULL PATH (e.g., `/home/username/.htpasswd`)

### C. Update Settings

Edit `analytics/.htaccess`:

Find:
```apache
AuthUserFile /home/username/.htpasswd
```

Change to your actual path.

✅ Done! Dashboard is now password protected.

---

## Step 3: Secure API (1 min)

### A. Generate API Key

Run this (or use online generator):
```bash
openssl rand -hex 32
```

Get something like: `a7c2e9f1b5d3g8h2j6k9m1n4p7q3r5s8`

### B. Add to Config

Edit `php/config.php`:

```php
// Line 14: API Key
define('ANALYTICS_API_KEY', 'a7c2e9f1b5d3g8h2j6k9m1n4p7q3r5s8');
```

### C. Add to Dashboard

Edit `analytics/index.html`:

Find line 237:
```javascript
const apiKey = 'your_secure_api_key_change_me_12345';
```

Change to:
```javascript
const apiKey = 'a7c2e9f1b5d3g8h2j6k9m1n4p7q3r5s8';
```

✅ Done! API is secured.

---

## Step 4: Protect Deletion (1 min)

Edit `php/config.php`:

Find:
```php
define('CLEAR_DATA_SECRET_KEY', 'your_secret_delete_key_change_me_xyz789');
```

Generate a new key and it replace it (different from API key).

✅ Done! Data deletion is protected.

---

## 🧪 Test Everything

1. Visit your portfolio page
2. Check email (check spam too)
3. Go to `http://your-site.com/analytics/`
4. Login with: `admin` / (your password)
5. See visitor data in dashboard

---

## What You Now Have

✅ Email notifications for new visitors
✅ Password protected analytics dashboard
✅ API key security
✅ Deletion protection
✅ Automatic spam/bot blocking
✅ Security logging

---

## Files Changed

- `php/config.php` (NEW)
- `php/track-visitor.php` (email added)
- `php/get-analytics.php` (API key added)
- `php/clear-analytics.php` (security improved)
- `analytics/.htaccess` (password added)
- `analytics/index.html` (API key added)

---

**Full setup guide**: See `EMAIL_ALERTS_SETUP.md`

Done! 🎉
