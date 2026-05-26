// Add to comparison list
function addToCompare(productId) {
    fetch(`add-to-compare.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Product added to comparison!');
                updateCompareBadge();
            } else {
                alert(data.message || 'Error adding to comparison');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to add to comparison');
        });
}

// Update comparison badge count
function updateCompareBadge() {
    const badge = document.querySelector('.compare-badge .badge');
    if (badge) {
        fetch('get-compare-count.php')
            .then(response => response.json())
            .then(data => {
                badge.textContent = data.count;
                if (data.count === 0) {
                    badge.style.display = 'none';
                }
            });
    }
}

// Image gallery preview
document.addEventListener('DOMContentLoaded', function() {
    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.querySelector('.main-image');
    
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            mainImage.src = this.src;
        });
    });
    
    // Search autocomplete
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.closest('form').submit();
            }
        });
    }
});

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('error');
            isValid = false;
        } else {
            input.classList.remove('error');
        }
    });
    
    return isValid;
}

// Confirm actions
function confirmAction(message) {
    return confirm(message || 'Are you sure you want to proceed?');
}

// Admin product form toggle
function toggleProductFields(type) {
    const realEstateFields = document.getElementById('real-estate-fields');
    const vehicleFields = document.getElementById('vehicle-fields');
    
    if (type === 'real_estate') {
        realEstateFields.style.display = 'block';
        vehicleFields.style.display = 'none';
    } else {
        realEstateFields.style.display = 'none';
        vehicleFields.style.display = 'block';
    }
}

// Initialize tooltips and popovers
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any Bootstrap tooltips if using Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// ============================================
// 3D Tilt Effect for Cards
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Add 3D tilt to product cards
    const cards = document.querySelectorAll('.product-card');
    
    cards.forEach(card => {
        card.addEventListener('mousemove', handleTilt);
        card.addEventListener('mouseleave', resetTilt);
    });
    
    function handleTilt(e) {
        const card = this;
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        
        const rotateX = (y - centerY) / 10;
        const rotateY = (centerX - x) / 10;
        
        card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px) translateZ(20px)`;
    }
    
    function resetTilt() {
        this.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0) translateZ(0)';
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add intersection observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0) translateZ(0)';
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.product-card, .stat-card, .cart-item').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
        observer.observe(el);
    });
    
    // Particle effect for hero section (optional)
    createParticles();
});

// ============================================
// Particle Effect
// ============================================
function createParticles() {
    const hero = document.querySelector('.hero-section');
    if (!hero) return;
    
    const particleContainer = document.createElement('div');
    particleContainer.className = 'particles';
    particleContainer.style.cssText = `
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 0;
    `;
    
    hero.appendChild(particleContainer);
    
    for (let i = 0; i < 50; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.cssText = `
            position: absolute;
            width: ${Math.random() * 4 + 2}px;
            height: ${Math.random() * 4 + 2}px;
            background: rgba(255, 255, 255, ${Math.random() * 0.3 + 0.1});
            border-radius: 50%;
            top: ${Math.random() * 100}%;
            left: ${Math.random() * 100}%;
            animation: float ${Math.random() * 3 + 2}s ease-in-out infinite;
            animation-delay: ${Math.random() * 2}s;
        `;
        
        particleContainer.appendChild(particle);
    }
}

// Add floating animation
const style = document.createElement('style');
style.textContent = `
    @keyframes float {
        0%, 100% { transform: translateY(0) translateX(0); opacity: 0; }
        50% { transform: translateY(-20px) translateX(10px); opacity: 1; }
    }
`;
document.head.appendChild(style);