<?php
// config/config.php - Global Application Configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../utils/bootstrap.php';
require_once __DIR__ . '/db.php';

// Load Dynamic Site Settings from Database (Fallback to defaults if offline)
$site_settings = [];
try {
    if (isset($pdo)) {
        $stmt_settings = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
        if ($stmt_settings) {
            $site_settings = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
        }
    }
} catch (Exception $e) {
    // Fallback gracefully (site_settings table may not exist yet)
}

function get_setting($key, $default = '') {
    global $site_settings;
    return $site_settings[$key] ?? $default;
}

// Application & Company Constants (Dynamic from Site Settings)
define('APP_NAME', get_setting('app_name', 'Jan Seva Facilitation Portal'));
define('APP_NAME_HI', get_setting('app_name_hi', 'राष्ट्रीय नागरिक सेवा एवं पहचान पत्र सुविधा केंद्र'));
define('COMPANY_NAME', get_setting('company_name', 'Jan Seva Digital Services Facilitator LLP'));
define('APP_TAGLINE', get_setting('app_tagline', 'Digital Citizen Services & Identity Facilitation Gateway'));
define('APP_VERSION', '1.0.0');
// Was hardcoded to '/gp', which only matches the local XAMPP layout
// (htdocs/gp/...). A git-deployed host (e.g. Hostinger) usually serves
// the repo from the domain root, not a /gp subfolder, so every asset
// URL built from BASE_URL ('/gp/css/...', '/gp/js/...') 404'd there.
// Set BASE_URL in that host's own .env (empty string for a root
// deployment); it still defaults to '/gp' for local XAMPP.
define('BASE_URL', env('BASE_URL', '/gp'));

// Legal & Compliance Notice
define('LEGAL_DISCLAIMER', get_setting('legal_disclaimer', 'This is a private digital facilitator platform and is NOT officially affiliated with or endorsed by UIDAI, NHA (ABDM), Ministry of Labour, Ministry of Education, ECI, or any Government department. We charge a nominal service fee for platform maintenance, digital verification processing, and citizen assistance. Official Government portals remain accessible directly at zero cost.'));
define('COMPLIANCE_SHORT_BADGE', 'Private Facilitator • Non-Governmental');

// Support & Helpline Information
define('HELPLINE_NUMBER', get_setting('helpline_number', '1800-11-2026'));
define('PHONE_SECONDARY', get_setting('phone_secondary', '+91 98765 43210'));
define('WHATSAPP_NUMBER', get_setting('whatsapp_number', '919876543210'));
define('SUPPORT_EMAIL', get_setting('support_email', env('CONTACT_RECEIVER_EMAIL', 'support@citizenportal.facilitator.in')));
define('OFFICE_ADDRESS', get_setting('office_address', 'Plot No. 42, Citizen Facilitation Complex, Barakhamba Road, Connaught Place, New Delhi - 110001, India'));
define('OPERATING_HOURS', get_setting('operating_hours', 'Mon - Sat: 9:00 AM - 7:00 PM IST (Sunday Closed)'));

// Social Media Links
define('SOCIAL_FACEBOOK', get_setting('social_facebook', 'https://facebook.com/'));
define('SOCIAL_TWITTER', get_setting('social_twitter', 'https://twitter.com/'));
define('SOCIAL_YOUTUBE', get_setting('social_youtube', 'https://youtube.com/'));
define('SOCIAL_INSTAGRAM', get_setting('social_instagram', 'https://instagram.com/'));
define('SOCIAL_LINKEDIN', get_setting('social_linkedin', 'https://linkedin.com/'));
define('GOOGLE_MAP_EMBED', get_setting('google_map_embed', ''));

// Payment Gateway Configuration (via .env)
define('RAZORPAY_KEY_ID', env('RAZORPAY_KEY_ID', ''));
define('RAZORPAY_KEY_SECRET', env('RAZORPAY_KEY_SECRET', ''));
define('PAYMENT_MODE', env('PAYMENT_MODE', 'sandbox'));
define('DEFAULT_API_FEE', (float) env('DEFAULT_API_FEE', '50.00'));

// SMS OTP Gateway Configuration (via .env)
define('SMS_GATEWAY_API_KEY', env('SMS_GATEWAY_API_KEY', ''));
define('OTP_TEST_MODE', env('OTP_TEST_MODE', 'true') === 'true');
define('OTP_EXPIRY_MINUTES', (int) env('OTP_EXPIRY_MINUTES', '10'));

// Helper Functions (sanitize()/e() already defined in utils/functions.php)

function format_inr($amount) {
    return '₹' . number_format((float)$amount, 2);
}

// Include Services Data Access Layer
require_once __DIR__ . '/../data/services.php';

function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
