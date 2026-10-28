-- Citizen Services Portal Database Schema
USE citizen_portal_db;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(120) NULL,
    otp_code VARCHAR(10) NULL,
    otp_expires_at DATETIME NULL,
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Services Table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(150) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    service_type ENUM('api', 'redirect') NOT NULL,
    category ENUM('Health', 'Education', 'Identity', 'Welfare') NOT NULL,
    fee DECIMAL(10,2) DEFAULT 0.00,
    icon VARCHAR(60) DEFAULT 'fas fa-id-card',
    short_desc VARCHAR(255) NOT NULL,
    full_desc TEXT NOT NULL,
    requirements_json LONGTEXT NULL,
    official_url VARCHAR(255) NULL,
    badge_text VARCHAR(50) DEFAULT 'Popular',
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    service_id INT NOT NULL,
    order_id VARCHAR(64) NOT NULL UNIQUE,
    payment_id VARCHAR(64) NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'INR',
    status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT 'razorpay',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (user_id),
    INDEX (service_id),
    INDEX (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Service Requests / Generated Cards Table
CREATE TABLE IF NOT EXISTS service_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(64) NOT NULL,
    user_id INT NULL,
    service_id INT NOT NULL,
    input_payload LONGTEXT NOT NULL,
    response_status ENUM('success', 'failed', 'processing') DEFAULT 'processing',
    generated_file_path VARCHAR(255) NULL,
    card_data_json LONGTEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (order_id),
    INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Admin Users Table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role VARCHAR(30) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Doorstep / Assisted Requests Table
CREATE TABLE IF NOT EXISTS doorstep_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    service_id INT NOT NULL,
    citizen_name VARCHAR(120) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    state VARCHAR(80) NOT NULL,
    city VARCHAR(80) NOT NULL,
    address TEXT NOT NULL,
    pincode VARCHAR(10) NOT NULL,
    service_needed VARCHAR(150) NOT NULL,
    preferred_slot VARCHAR(50) NULL,
    status ENUM('new', 'contacted', 'scheduled', 'completed', 'cancelled') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Default Admin (admin / admin123)
INSERT INTO admin_users (username, password_hash, name, email, role)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@citizenportal.gov.facilitator.in', 'superadmin')
ON DUPLICATE KEY UPDATE username=username;

-- Seed Services
INSERT INTO services (service_name, slug, service_type, category, fee, icon, short_desc, full_desc, requirements_json, official_url, badge_text, is_active, sort_order)
VALUES
(
    'ABHA Health ID Card',
    'abha-card',
    'api',
    'Health',
    50.00,
    'fas fa-heartbeat',
    'Ayushman Bharat Health Account (14-digit Health ID) instant generation & digital PVC card download.',
    'ABHA (Ayushman Bharat Health Account) is a unique 14-digit identification number under the National Digital Health Mission (NDHM / ABDM) that digitally links all your health records, lab reports, prescriptions, and health insurance across verified hospitals and doctors across India.',
    '{"id_type": "Aadhaar / Mobile", "docs": ["Aadhaar Card or Mobile Number", "Active Mobile for OTP verification", "Basic Demographic details"]}',
    'https://healthid.ndhm.gov.in',
    'Instant PDF',
    1,
    1
),
(
    'APAAR / ABC Student ID Card',
    'apaar-card',
    'api',
    'Education',
    50.00,
    'fas fa-graduation-cap',
    'Automated Permanent Academic Account Registry (One Nation One Student ID) card download.',
    'APAAR (Automated Permanent Academic Account Registry) ID, formulated under the National Education Policy (NEP 2020), assigns a unique 12-digit academic identity to every student in India to track degrees, marksheet credits, scholarships, and academic transfer records via DigiLocker / ABC.',
    '{"id_type": "Student Aadhaar / School PEN", "docs": ["Student Aadhaar Number", "School / University Name", "Student Mobile Number"]}',
    'https://apaar.education.gov.in',
    'Official Format',
    1,
    2
),
(
    'e-Shram Worker ID Card',
    'eshram-card',
    'api',
    'Welfare',
    50.00,
    'fas fa-hard-hat',
    'Universal Account Number (UAN) for unorganized sector workers with social security benefits.',
    'e-Shram card issued by the Ministry of Labour and Employment registers unorganized workers (laborers, gig workers, street vendors, domestic workers) to provide them with government social security benefits, PMSBY accidental insurance cover, and financial assistance during national crises.',
    '{"id_type": "Aadhaar Linked Mobile", "docs": ["Aadhaar Number", "Aadhaar linked Mobile Number", "Bank Account Details", "Age between 16-59 years"]}',
    'https://eshram.gov.in',
    '₹50 Service Fee',
    1,
    3
),
(
    'Voter ID (EPIC) Verification Card',
    'voter-id',
    'api',
    'Identity',
    50.00,
    'fas fa-vote-yea',
    'Instant Election Photo Identity Card (EPIC) status verification & formatted digital card.',
    'Verify your Election Photo Identity Card (EPIC) registered with the Election Commission of India (ECI), fetch polling station details, electoral constituency, and generate a printable verified digital identity pass.',
    '{"id_type": "EPIC Number", "docs": ["10-character EPIC Voter ID number", "State of registration", "Mobile Number for OTP verification"]}',
    'https://voters.eci.gov.in',
    'ECI Data',
    1,
    4
),
(
    'Aadhaar Services & Update Guidance',
    'aadhaar-services',
    'redirect',
    'Identity',
    0.00,
    'fas fa-fingerprint',
    'Step-by-step document eligibility wizard & official UIDAI portal redirection for updates/enrollment.',
    'Assisted guidance engine for Aadhaar address update, mobile number linking, biometric lock/unlock, mandatory child biometric update (MBU), and PVC card ordering with document checklist and official UIDAI deep-links.',
    '{"wizard_steps": ["Select Service Type (New / Address / Biometric)", "Document Checklist (PoI, PoA, DoB, PoR)", "UIDAI Appointment / myAadhaar Redirection"]}',
    'https://myaadhaar.uidai.gov.in',
    'Assisted Flow',
    1,
    5
),
(
    'State Ration Card (NFSA) Guidance',
    'ration-card',
    'redirect',
    'Welfare',
    0.00,
    'fas fa-wheat-awn',
    'State-wise food security ration card application checklist, member addition & official portal links.',
    'Interactive eligibility checker for National Food Security Act (NFSA) ration cards, Antyodaya Anna Yojana (AAY), and Priority Household (PHH) cards. Directs citizens to their respective State Food & Civil Supplies departmental portal.',
    '{"wizard_steps": ["Select State & Card Category", "Mandatory Family Documents & Income Proof", "Direct State Food Portal Deep-Link"]}',
    'https://nfsa.gov.in/portal/State_UT_Portal',
    'State Portals',
    1,
    6
),
(
    'PAN Card Correction / Verification',
    'pan-card',
    'redirect',
    'Identity',
    0.00,
    'fas fa-address-card',
    'Assisted wizard for instant e-PAN, name/DOB corrections, and NSDL / UTIITSL official redirection.',
    'Assisted pathway for citizens wishing to correct misspelled names, update father name or date of birth on PAN Card, or link PAN with Aadhaar. Provides official links to NSDL and UTIITSL portals with document verification checklist.',
    '{"wizard_steps": ["Select Correction Type", "Check Supporting Documents (Class 10 mark sheet, Voter ID)", "Direct NSDL / UTIITSL Portal"]}',
    'https://www.onlineservices.nsdl.com/paam/endUserRegisterContact.html',
    'NSDL / UTI',
    1,
    7
),
(
    'e-District State Certificates',
    'edistrict-certificates',
    'redirect',
    'Welfare',
    0.00,
    'fas fa-certificate',
    'Domicile, Caste, Income, and Birth/Death certificate guidance for all Indian States & UTs.',
    'Navigate state-specific e-District portals across India (e.g. eDistrict UP, Aaple Sarkar Maharashtra, RTPS Bihar, edistrict Delhi). Check required affidavits, revenue records, and land ownership proofs.',
    '{"wizard_steps": ["Select State & Certificate Needed", "Check Local Tehsildar / Revenue Requirements", "State e-District Direct Redirection"]}',
    'https://services.india.gov.in',
    'State Portals',
    1,
    8
)
ON DUPLICATE KEY UPDATE service_name=VALUES(service_name);

