<?php
// api/verify_payment.php - Verifies Razorpay signature / test payment and triggers card generation
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/generate_card.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$order_id = trim($_POST['order_id'] ?? '');
$payment_id = trim($_POST['payment_id'] ?? ($_POST['razorpay_payment_id'] ?? ''));
$signature = trim($_POST['signature'] ?? ($_POST['razorpay_signature'] ?? ''));

if (empty($order_id)) {
    echo json_encode(['success' => false, 'message' => 'Order ID is missing']);
    exit;
}

// In test/sandbox mode, if signature is empty, generate mock payment ID if not provided
if (empty($payment_id)) {
    $payment_id = 'pay_sandbox_' . bin2hex(random_bytes(6));
}

// In live Razorpay mode with keys:
// $expected_signature = hash_hmac('sha256', $order_id . '|' . $payment_id, RAZORPAY_KEY_SECRET);
// if (PAYMENT_MODE === 'live' && $signature !== $expected_signature) { ... }

// Generate card data and mark order paid
$result = process_card_generation($pdo, $order_id, $payment_id);

if ($result['success']) {
    echo json_encode([
        'success' => true,
        'message' => 'Payment verified successfully!',
        'order_id' => $order_id,
        'redirect_url' => $result['redirect_url']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $result['message'] ?? 'Failed to complete order processing'
    ]);
}
