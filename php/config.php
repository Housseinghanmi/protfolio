<?php
/**
 * Configuration File for Visitor Tracking System
 * Edit this file to customize tracker behavior
 */

// ============================================
// EMAIL CONFIGURATION
// ============================================

// Enable/disable email notifications
define('SEND_EMAIL_NOTIFICATIONS', true);

// Your email address (where to send visitor notifications)
define('ADMIN_EMAIL', 'houcaineghanmi@gmail.com');

// Email subject prefix
define('EMAIL_SUBJECT_PREFIX', '[New Portfolio Visitor]');

// Enable summary emails (optional)
define('SEND_DAILY_SUMMARY', false);
define('DAILY_SUMMARY_TIME', '09:00'); // 24-hour format
define('DAILY_SUMMARY_EMAIL', 'houcaineghanmi@gmail.com');

// ============================================
// TRACKER CONFIGURATION
// ============================================

// API key for dashboard (change this!)
define('ANALYTICS_API_KEY', 'your_secure_api_key_change_me_12345');

// Secret key for clearing data (change this!)
define('CLEAR_DATA_SECRET_KEY', 'your_secret_delete_key_change_me_xyz789');

// Maximum visitors to store (auto-cleanup after this)
define('MAX_VISITORS_STORED', 10000);

// ============================================
// SECURITY CONFIGURATION
// ============================================

// Rate limiting: max requests per minute per IP
define('RATE_LIMIT_REQUESTS', 10);

// Block known bot user agents
define('BLOCK_BOTS', true);

// Log all requests for security audit
define('LOG_ALL_REQUESTS', true);

// ============================================
// DATA CONFIGURATION
// ============================================

// Data directory (relative to this file's directory)
define('DATA_DIR', __DIR__ . '/../data');

// ============================================
// EMAIL CONFIGURATION DETAILS
// ============================================

/**
 * Email notifications include:
 *
 * For Gmail:
 * 1. Enable "Less secure app access" in Google Account settings
 *    https://myaccount.google.com/lesssecureapps
 *
 * 2. Or better: Use App Password
 *    - Enable 2-factor authentication
 *    - Generate App Password at https://myaccount.google.com/apppasswords
 *
 * For other email providers:
 * - Check their SMTP settings
 * - Usually port 587 (TLS) or 465 (SSL)
 */

// SMTP settings (if needed)
define('USE_SMTP', false); // Set to true to use SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');

// Simple PHP mail() function settings
define('EMAIL_FROM', 'noreply@your-portfolio.com');
define('EMAIL_FROM_NAME', 'Portfolio Tracker');

?>
