<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id'])) {
    $cartId = (int)$_POST['cart_id'];
    $userId = $_SESSION['user_id'];
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $stmt = $db->prepare("DELETE FROM reservation_carts WHERE id = :cart_id AND user_id = :user_id");
        $stmt->execute([':cart_id' => $cartId, ':user_id' => $userId]);
        
        $_SESSION['message'] = 'Item removed from cart.';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error removing item.';
    }
}

header('Location: cart.php');
exit;
?>