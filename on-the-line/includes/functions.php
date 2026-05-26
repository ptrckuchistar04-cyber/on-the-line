<?php
require_once __DIR__ . '/../config/database.php';

// Get database connection
function getDB() {
    $database = new Database();
    return $database->getConnection();
}

// Password hashing
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check user role
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'administrator';
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Generate order number
function generateOrderNumber() {
    return 'OTL-' . strtoupper(uniqid() . bin2hex(random_bytes(2)));
}

// Get product details with specifications
function getProductDetails($productId) {
    $db = getDB();
    
    $sql = "SELECT p.*, 
                   rd.property_type, rd.square_meters, rd.bedrooms, rd.bathrooms, 
                   rd.amenities, rd.year_built,
                   vd.make, vd.model, vd.year, vd.mileage, vd.transmission,
                   vd.fuel_type, vd.modifications, vd.vin
            FROM products p
            LEFT JOIN real_estate_details rd ON p.id = rd.product_id AND p.product_type = 'real_estate'
            LEFT JOIN vehicle_details vd ON p.id = vd.product_id AND p.product_type = 'vehicle'
            WHERE p.id = :id";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $productId]);
    return $stmt->fetch();
}

// Get all products with filtering
function getProducts($category = null, $search = '', $limit = 50) {
    $db = getDB();
    
    $sql = "SELECT p.id, p.product_type, p.title, p.price, p.reservation_fee, 
                   p.main_image, p.status, p.is_bundle,
                   rd.square_meters, rd.bedrooms, rd.bathrooms,
                   vd.make, vd.model, vd.year, vd.mileage
            FROM products p
            LEFT JOIN real_estate_details rd ON p.id = rd.product_id AND p.product_type = 'real_estate'
            LEFT JOIN vehicle_details vd ON p.id = vd.product_id AND p.product_type = 'vehicle'
            WHERE p.status = 'available'";
    
    $params = [];
    
    if ($category && in_array($category, ['real_estate', 'vehicle'])) {
        $sql .= " AND p.product_type = :category";
        $params[':category'] = $category;
    }
    
    if ($search) {
        $sql .= " AND (p.title LIKE :search1 OR p.description LIKE :search2)";
        $params[':search1'] = "%{$search}%";
        $params[':search2'] = "%{$search}%";
    }
    
    $sql .= " ORDER BY p.created_at DESC LIMIT :limit";
    
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Get user's cart
function getCartItems($userId) {
    $db = getDB();
    
    $sql = "SELECT rc.id as cart_id, p.*, rd.square_meters, rd.bedrooms,
                   vd.make, vd.model, vd.year
            FROM reservation_carts rc
            JOIN products p ON rc.product_id = p.id
            LEFT JOIN real_estate_details rd ON p.id = rd.product_id
            LEFT JOIN vehicle_details vd ON p.id = vd.product_id
            WHERE rc.user_id = :user_id";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll();
}

// Get comparison items
function getComparisonItems($productIds) {
    if (empty($productIds)) return [];
    
    $db = getDB();
    $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
    
    $sql = "SELECT p.*, rd.*, vd.*
            FROM products p
            LEFT JOIN real_estate_details rd ON p.id = rd.product_id
            LEFT JOIN vehicle_details vd ON p.id = vd.product_id
            WHERE p.id IN ($placeholders)";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($productIds);
    return $stmt->fetchAll();
}