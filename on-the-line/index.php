<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

$category = $_GET['category'] ?? null;
$search = $_GET['search'] ?? '';

$products = getProducts($category, $search);
$pageTitle = 'On The Line - Browse Properties & Vehicles';

include 'includes/header.php';
?>

<div class="hero-section">
    <div class="container">
        <h1>Find Your Perfect Match</h1>
        <p>Browse premium real estate and certified pre-owned vehicles</p>
        
        <form class="search-form" method="GET" action="index.php">
            <div class="search-container">
                <input type="text" name="search" class="search-input" 
                       placeholder="Search by title or description..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary search-btn">
                    🔍 Search
                </button>
            </div>
        </form>
    </div>
</div>

<div class="container">
    <div class="category-filters">
        <a href="index.php" class="filter-btn <?php echo !$category ? 'active' : ''; ?>">
            All Listings
        </a>
        <a href="index.php?category=real_estate" class="filter-btn <?php echo $category === 'real_estate' ? 'active' : ''; ?>">
            🏠 Real Estate
        </a>
        <a href="index.php?category=vehicle" class="filter-btn <?php echo $category === 'vehicle' ? 'active' : ''; ?>">
            🚗 Vehicles
        </a>
    </div>
    
    <?php if ($search): ?>
        <div class="search-results-info">
            <p>Showing results for: <strong>"<?php echo htmlspecialchars($search); ?>"</strong></p>
        </div>
    <?php endif; ?>
    
    <div class="product-grid">
        <?php if (empty($products)): ?>
            <div class="no-results">
                <h3>No listings found</h3>
                <p>Try adjusting your search criteria or browse all available listings.</p>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo $product['main_image'] ? $product['main_image'] : 'assets/images/no-image.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($product['title']); ?>">
                        <span class="product-type-badge">
                            <?php echo $product['product_type'] === 'real_estate' ? '🏠 Property' : '🚗 Vehicle'; ?>
                        </span>
                        <?php if ($product['is_bundle']): ?>
                            <span class="bundle-badge">Bundle Deal</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-info">
                        <h3 class="product-title">
                            <?php echo htmlspecialchars($product['title']); ?>
                        </h3>
                        
                        <div class="product-specs">
                            <?php if ($product['product_type'] === 'real_estate'): ?>
                                <span>📐 <?php echo $product['square_meters']; ?> sqm</span>
                                <span>🛏 <?php echo $product['bedrooms']; ?> beds</span>
                                <span>🛁 <?php echo $product['bathrooms']; ?> baths</span>
                            <?php else: ?>
                                <span>🚗 <?php echo $product['make'] . ' ' . $product['model']; ?></span>
                                <span>📅 <?php echo $product['year']; ?></span>
                                <span>📍 <?php echo number_format($product['mileage']); ?> km</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-pricing">
                            <div class="price">
                                ₱<?php echo number_format($product['price'], 2); ?>
                            </div>
                            <div class="reservation-fee">
                                Reserve for: ₱<?php echo number_format($product['reservation_fee'], 2); ?>
                            </div>
                        </div>
                        
                        <div class="product-actions">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-outline btn-sm">
                                View Details
                            </a>
                            
                            <?php if (isset($_SESSION['user_id']) && (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'administrator')): ?>
                                <!-- Logged in as customer - Show Reserve button -->
                                <form method="POST" action="customer/add-to-cart.php" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        Reserve Now
                                    </button>
                                </form>
                                
                                <!-- Compare button -->
                                <button onclick="addToCompare(<?php echo $product['id']; ?>)" 
                                        class="btn btn-compare btn-sm">
                                    ⇆ Compare
                                </button>
                            <?php elseif (!isset($_SESSION['user_id'])): ?>
                                <!-- Not logged in - Show Login to Reserve button -->
                                <a href="login.php" class="btn btn-primary btn-sm">
                                    Login to Reserve
                                </a>
                            <?php elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'administrator'): ?>
                                <!-- Admin logged in - Show edit link -->
                                <a href="admin/edit-product.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-outline btn-sm">
                                    ✏️ Edit
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Add to comparison list function
function addToCompare(productId) {
    fetch('add-to-compare.php?product_id=' + productId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showNotification('Product added to comparison!', 'success');
                updateCompareBadge();
            } else {
                showNotification(data.message || 'Error adding to comparison', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to add to comparison', 'error');
        });
}

// Update comparison badge count
function updateCompareBadge() {
    fetch('get-compare-count.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.compare-badge .badge');
            if (badge) {
                badge.textContent = data.count;
                badge.style.display = data.count > 0 ? 'inline-block' : 'none';
            }
        });
}

// Show notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 24px;
        border-radius: 10px;
        color: white;
        font-weight: 600;
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
        box-shadow: 0 8px 32px rgba(0,0,0,0.2);
    `;
    
    if (type === 'success') {
        notification.style.background = 'linear-gradient(135deg, #28a745, #20c997)';
    } else {
        notification.style.background = 'linear-gradient(135deg, #dc3545, #c82333)';
    }
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);
</script>

<?php include 'includes/footer.php'; ?>