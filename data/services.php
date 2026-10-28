<?php
// data/services.php - Citizen Services Repository & Data Access Layer
require_once __DIR__ . '/../config/config.php';

/**
 * Fetch all citizen services from database
 * 
 * @param PDO $pdo
 * @param string|null $category
 * @param string|null $serviceType ('api' or 'redirect')
 * @param bool $onlyActive
 * @return array
 */
function get_all_services($pdo, $category = null, $serviceType = null, $onlyActive = true) {
    $sql = "SELECT * FROM services WHERE 1=1";
    $params = [];

    if ($onlyActive) {
        $sql .= " AND is_active = 1";
    }

    if (!empty($category) && $category !== 'all') {
        $sql .= " AND category = ?";
        $params[] = $category;
    }

    if (!empty($serviceType) && $serviceType !== 'all') {
        $sql .= " AND service_type = ?";
        $params[] = $serviceType;
    }

    $sql .= " ORDER BY sort_order ASC, id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map('hydrate_service_json_fields', $rows);
}

/**
 * Fetch a single service by slug
 * 
 * @param PDO $pdo
 * @param string $slug
 * @return array|null
 */
function get_service_by_slug($pdo, $slug) {
    $slug = trim($slug);
    if (empty($slug)) return null;

    $stmt = $pdo->prepare("SELECT * FROM services WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        // Fallback: match by like or without trailing/leading dashes
        $clean = preg_replace('/[^a-zA-Z0-9]/', '', $slug);
        $stmt_fallback = $pdo->prepare("SELECT * FROM services WHERE REPLACE(slug, '-', '') = ? LIMIT 1");
        $stmt_fallback->execute([$clean]);
        $row = $stmt_fallback->fetch(PDO::FETCH_ASSOC);
    }

    return $row ? hydrate_service_json_fields($row) : null;
}

/**
 * Fetch a single service by ID
 * 
 * @param PDO $pdo
 * @param int $id
 * @return array|null
 */
function get_service_by_id($pdo, $id) {
    $id = (int)$id;
    if ($id <= 0) return null;

    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? hydrate_service_json_fields($row) : null;
}

/**
 * Decode JSON columns into PHP arrays for easy template usage
 */
function hydrate_service_json_fields($row) {
    if (!$row) return null;

    $jsonFields = [
        'benefits_json' => 'benefits',
        'eligibility_json' => 'eligibility',
        'documents_json' => 'documents',
        'steps_json' => 'steps',
        'faqs_json' => 'faqs',
        'requirements_json' => 'requirements'
    ];

    foreach ($jsonFields as $dbCol => $alias) {
        if (!empty($row[$dbCol])) {
            $decoded = json_decode($row[$dbCol], true);
            $row[$alias] = is_array($decoded) ? $decoded : [];
        } else {
            $row[$alias] = [];
        }
    }

    // Fallbacks if empty
    if (empty($row['benefits'])) {
        $row['benefits'] = [
            "Official digital verification and statutory record linkage.",
            "High resolution QR-coded PVC card format.",
            "Immediate access via citizen status tracking portal."
        ];
    }
    if (empty($row['eligibility'])) {
        $row['eligibility'] = [
            "Citizen of India with valid demographic records.",
            "Active Indian mobile phone number for OTP verification."
        ];
    }
    if (empty($row['documents'])) {
        $row['documents'] = [
            "Aadhaar Card or Demographic Proof",
            "Active mobile number"
        ];
    }
    if (empty($row['steps'])) {
        $row['steps'] = [
            ["title" => "Fill Citizen Details", "desc" => "Enter personal information and contact details."],
            ["title" => "Verify via OTP", "desc" => "Confirm verification code sent to your phone."],
            ["title" => "Complete Application", "desc" => "Download your card or visit official portal."]
        ];
    }

    return $row;
}
