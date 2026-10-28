<?php
// api/create_order.php - Initializes payment order for Direct API service
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$service_id = (int) ($_POST['service_id'] ?? 0);
$user_id = (int) ($_SESSION['user_id'] ?? ($_POST['user_id'] ?? 0));
$phone = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? ($_SESSION['user_phone'] ?? ''));
$name = trim($_POST['full_name'] ?? ($_SESSION['user_name'] ?? 'Citizen'));

// If user not set, lookup or create
if (!$user_id && strlen($phone) === 10) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
    $stmt->execute([$phone]);
    $u = $stmt->fetch();
    if ($u) {
        $user_id = $u['id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, phone, is_verified) VALUES (?, ?, 1)");
        $stmt->execute([$name, $phone]);
        $user_id = $pdo->lastInsertId();
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$service_id]);
    $service = $stmt->fetch();

    if (!$service) {
        echo json_encode(['success' => false, 'message' => 'Selected service is unavailable']);
        exit;
    }

    $fee = (float) $service['fee'];
    $order_id = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

    // Store in orders table
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, service_id, order_id, amount, status, payment_method) VALUES (?, ?, ?, ?, 'pending', 'razorpay')");
    $stmt->execute([$user_id ?: null, $service['id'], $order_id, $fee]);

    // Save payload data to session for card generation after payment
    $_SESSION['current_order'] = [
        'order_id' => $order_id,
        'service_id' => $service['id'],
        'service_slug' => $service['slug'],
        'service_name' => $service['service_name'],
        'amount' => $fee,
        'user_id' => $user_id,
        'payload' => $_POST
    ];

    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'amount' => $fee,
        'amount_paise' => (int) ($fee * 100),
        'currency' => 'INR',
        'key_id' => RAZORPAY_KEY_ID,
        'service_name' => $service['service_name'],
        'user_name' => $name,
        'user_phone' => $phone,
        'payment_mode' => PAYMENT_MODE
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Order creation failed: ' . $e->getMessage()]);
}
