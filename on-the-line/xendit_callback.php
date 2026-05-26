<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

// Xendit Callback Verification Token
$callbackToken = 'your_callback_verification_token_here';

// Get headers
$headers = getallheaders();
$receivedToken = $headers['X-Callback-Token'] ?? $headers['x-callback-token'] ?? '';

// Verify callback token
if ($receivedToken !== $callbackToken) {
    http_response_code(403);
    die('Unauthorized');
}

// Get callback data
$rawInput = file_get_contents('php://input');
$callbackData = json_decode($rawInput, true);

if (!$callbackData) {
    http_response_code(400);
    die('Invalid callback data');
}

$db = getDB();

try {
    $db->beginTransaction();
    
    $externalId = $callbackData['external_id'];
    $status = $callbackData['status'];
    $invoiceId = $callbackData['id'];
    
    // Find the order
    $stmt = $db->prepare("SELECT id FROM orders WHERE order_number = :order_number AND status = 'pending'");
    $stmt->execute([':order_number' => $externalId]);
    $order = $stmt->fetch();
    
    if ($order) {
        if ($status === 'PAID') {
            // Update order status
            $stmt = $db->prepare("UPDATE orders SET status = 'paid', updated_at = NOW() WHERE id = :order_id");
            $stmt->execute([':order_id' => $order['id']]);
            
            // Update product statuses to 'reserved'
            $stmt = $db->prepare("
                UPDATE products p 
                JOIN order_items oi ON p.id = oi.product_id 
                SET p.status = 'reserved', p.updated_at = NOW()
                WHERE oi.order_id = :order_id
            ");
            $stmt->execute([':order_id' => $order['id']]);
            
            // Log the transaction
            $stmt = $db->prepare("INSERT INTO payment_logs (order_id, invoice_id, status, payload) 
                                 VALUES (:order_id, :invoice_id, :status, :payload)");
            $stmt->execute([
                ':order_id' => $order['id'],
                ':invoice_id' => $invoiceId,
                ':status' => $status,
                ':payload' => $rawInput
            ]);
            
        } elseif ($status === 'EXPIRED') {
            $stmt = $db->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = :order_id");
            $stmt->execute([':order_id' => $order['id']]);
        }
    }
    
    $db->commit();
    http_response_code(200);
    echo 'OK';
    
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    error_log('Xendit Callback Error: ' . $e->getMessage());
    echo 'Error processing callback';
}