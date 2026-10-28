<?php
// api/generate_card.php - Processes card payload, simulates ABDM/DigiLocker API response, and stores card data
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

function generate_qr_data_uri($data_text) {
    // Generate clean SVG QR-style graphic representation or public QR generator data URI
    return "https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=" . urlencode($data_text);
}

function process_card_generation($pdo, $order_id, $payment_id, $payload = null) {
    // 1. Fetch Order
    $stmt = $pdo->prepare("SELECT o.*, s.slug, s.service_name FROM orders o JOIN services s ON o.service_id = s.id WHERE o.order_id = ? LIMIT 1");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        return ['success' => false, 'message' => 'Order not found'];
    }

    // 2. Mark order as paid
    $updateOrder = $pdo->prepare("UPDATE orders SET status = 'paid', payment_id = ? WHERE id = ?");
    $updateOrder->execute([$payment_id, $order['id']]);

    // 3. Extract inputs
    $input_payload = $payload ?: ($_SESSION['current_order']['payload'] ?? []);
    $name = trim($input_payload['full_name'] ?? ($input_payload['name'] ?? 'Citizen Applicant'));
    $dob = trim($input_payload['dob'] ?? '1995-08-15');
    $gender = trim($input_payload['gender'] ?? 'Male');
    $phone = trim($input_payload['phone'] ?? '9876543210');
    $state = trim($input_payload['state'] ?? 'Delhi');
    $address = trim($input_payload['address'] ?? 'H.No 142, Main Road, New Delhi, 110001');

    $slug = $order['slug'];
    $card_data = [];

    if ($slug === 'abha-card') {
        // ABHA 14 Digit Health ID: 91-XXXX-XXXX-XXXX
        $rand_part = str_pad((string)mt_rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT);
        $abha_number = '91-' . substr($rand_part, 0, 4) . '-' . substr($rand_part, 4, 4) . '-' . substr($rand_part, 8, 2) . mt_rand(10, 99);
        $clean_name = strtolower(preg_replace('/[^a-zA-Z]/', '', $name));
        $abha_address = ($clean_name ?: 'citizen') . mt_rand(10, 99) . '@abdm';

        $card_data = [
            'type' => 'ABHA',
            'title' => 'Ayushman Bharat Health Account',
            'authority' => 'National Health Authority (ABDM)',
            'name' => strtoupper($name),
            'abha_number' => $abha_number,
            'abha_address' => $abha_address,
            'gender' => $gender,
            'dob' => date('d/m/Y', strtotime($dob)),
            'mobile' => '+91-XXXXXX' . substr($phone, -4),
            'state' => $state,
            'address' => $address,
            'issued_date' => date('d/m/Y'),
            'qr_code' => generate_qr_data_uri("ABHA:{$abha_number}|NAME:{$name}|DOB:{$dob}|STATUS:VERIFIED"),
            'photo' => BASE_URL . '/images/tuser1.jpg'
        ];
    } elseif ($slug === 'apaar-card') {
        // APAAR 12 Digit Student ID: XXXX XXXX XXXX
        $rand_id = str_pad((string)mt_rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
        $apaar_id = substr($rand_id, 0, 4) . ' ' . substr($rand_id, 4, 4) . ' ' . substr($rand_id, 8, 4);
        $institution = trim($input_payload['institution'] ?? 'Central Board of Secondary Education / ABC Registered Institution');
        $course = trim($input_payload['course'] ?? 'Higher Secondary / Graduation');

        $card_data = [
            'type' => 'APAAR',
            'title' => 'One Nation One Student ID (APAAR)',
            'authority' => 'Ministry of Education & Academic Bank of Credits',
            'name' => strtoupper($name),
            'apaar_id' => $apaar_id,
            'pen_number' => 'PEN-' . mt_rand(10000000, 99999999),
            'institution' => $institution,
            'course' => $course,
            'gender' => $gender,
            'dob' => date('d/m/Y', strtotime($dob)),
            'state' => $state,
            'issued_date' => date('d/m/Y'),
            'qr_code' => generate_qr_data_uri("APAAR:{$apaar_id}|STUDENT:{$name}|INSTITUTION:{$institution}|VERIFIED"),
            'photo' => BASE_URL . '/images/tuser2.jpg'
        ];
    } elseif ($slug === 'eshram-card') {
        // e-Shram 12 Digit UAN: XXXX XXXX XXXX
        $rand_uan = str_pad((string)mt_rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
        $uan_number = substr($rand_uan, 0, 4) . ' ' . substr($rand_uan, 4, 4) . ' ' . substr($rand_uan, 8, 4);
        $occupation = trim($input_payload['occupation'] ?? 'Agriculture & Allied / Construction Worker');

        $card_data = [
            'type' => 'ESHRAM',
            'title' => 'e-Shram Universal Account Card',
            'authority' => 'Ministry of Labour & Employment, Govt of India',
            'name' => strtoupper($name),
            'uan_number' => $uan_number,
            'occupation' => $occupation,
            'gender' => $gender,
            'dob' => date('d/m/Y', strtotime($dob)),
            'father_name' => trim($input_payload['father_name'] ?? 'Father / Guardian Name'),
            'blood_group' => trim($input_payload['blood_group'] ?? 'B+'),
            'state' => $state,
            'address' => $address,
            'issued_date' => date('d/m/Y'),
            'qr_code' => generate_qr_data_uri("ESHRAM-UAN:{$uan_number}|NAME:{$name}|OCCUPATION:{$occupation}"),
            'photo' => BASE_URL . '/images/tuser3.jpg'
        ];
    } else {
        // Voter ID (EPIC) Verification Card
        $letters = "ABCDEFGHJKLMNPQRSTUVWXYZ";
        $epic_code = substr(str_shuffle($letters), 0, 3) . mt_rand(1000000, 9999999);
        $epic = !empty($input_payload['epic_number']) ? strtoupper($input_payload['epic_number']) : $epic_code;
        $constituency = trim($input_payload['constituency'] ?? 'New Delhi Parliamentary - AC 40');
        $polling_station = trim($input_payload['polling_station'] ?? 'Govt Sr Sec School, Room No. 3');

        $card_data = [
            'type' => 'VOTER',
            'title' => 'Election Photo Identity Verification Pass',
            'authority' => 'Election Commission of India Verification Record',
            'name' => strtoupper($name),
            'epic_number' => $epic,
            'relation_name' => trim($input_payload['relation_name'] ?? 'Guardian Name'),
            'gender' => $gender,
            'dob' => date('d/m/Y', strtotime($dob)),
            'constituency' => $constituency,
            'polling_station' => $polling_station,
            'part_number' => mt_rand(12, 180),
            'state' => $state,
            'address' => $address,
            'issued_date' => date('d/m/Y'),
            'qr_code' => generate_qr_data_uri("EPIC:{$epic}|NAME:{$name}|CONSTITUENCY:{$constituency}|VERIFIED"),
            'photo' => BASE_URL . '/images/user2.jpg'
        ];
    }

    // 4. Save into service_requests table
    $stmtReq = $pdo->prepare("INSERT INTO service_requests (order_id, user_id, service_id, input_payload, response_status, generated_file_path, card_data_json) VALUES (?, ?, ?, ?, 'success', ?, ?)");
    $file_path = 'downloads/' . $order_id . '.json';
    $stmtReq->execute([
        $order_id,
        $order['user_id'],
        $order['service_id'],
        json_encode($input_payload),
        $file_path,
        json_encode($card_data)
    ]);

    return [
        'success' => true,
        'order_id' => $order_id,
        'card_data' => $card_data,
        'redirect_url' => BASE_URL . '/download-card.php?order_id=' . $order_id
    ];
}

// If invoked directly via POST
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $order_id = trim($_POST['order_id'] ?? '');
    $payment_id = trim($_POST['payment_id'] ?? ('PAY-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8))));
    $payload = $_SESSION['current_order']['payload'] ?? $_POST;

    $res = process_card_generation($pdo, $order_id, $payment_id, $payload);
    echo json_encode($res);
    exit;
}
