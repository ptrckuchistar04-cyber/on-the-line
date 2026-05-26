<?php
session_start();
require_once 'config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to compare products']);
    exit;
}

$productId = (int)$_GET['product_id'];

if (!isset($_SESSION['compare_list'])) {
    $_SESSION['compare_list'] = [];
}

// Check if already in list
if (in_array($productId, $_SESSION['compare_list'])) {
    echo json_encode(['success' => false, 'message' => 'Product already in comparison list']);
    exit;
}

// Check limit
if (count($_SESSION['compare_list']) >= MAX_COMPARE_ITEMS) {
    echo json_encode(['success' => false, 'message' => 'Maximum 2 items can be compared']);
    exit;
}

// Add to list
$_SESSION['compare_list'][] = $productId;

echo json_encode(['success' => true, 'message' => 'Product added to comparison']);