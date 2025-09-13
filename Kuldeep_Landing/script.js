// Tribal Art Platform - Main JavaScript

// Global variables
let cart = [];
let currentFilter = 'all';
let searchTerm = '';

// Sample data for artisans
const artisansData = [
    {
        id: 1,
        name: "Rama Devi",
        tribe: "Warli Tribe",
        specialty: "Warli Paintings",
        experience: "25 years",
        products: 45,
        image: "fas fa-paint-brush",
        location: "Maharashtra",
        story: "Master of traditional Warli art, Rama Devi has been creating beautiful paintings for over 25 years, passing down ancient techniques to younger generations.",
        rating: 4.9
    },
    {
        id: 2,
        name: "Bhola Ram",
        tribe: "Gond Tribe",
        specialty: "Gond Art & Sculptures",
        experience: "30 years",
        products: 62,
        image: "fas fa-mountain",
        location: "Madhya Pradesh",
        story: "Renowned Gond artist known for intricate nature-inspired paintings and wooden sculptures that tell stories of tribal folklore.",
        rating: 4.8
    },
    {
        id: 3,
        name: "Sunita Hansda",
        tribe: "Santhal Tribe",
        specialty: "Bamboo Crafts",
        experience: "20 years",
        products: 38,
        image: "fas fa-feather",
        location: "Jharkhand",
        story: "Expert in traditional bamboo and cane work, creating beautiful baskets, furniture, and decorative items using age-old techniques.",
        rating: 4.7
    },
    {
        id: 4,
        name: "Kishan Meena",
        tribe: "Bhil Tribe",
        specialty: "Terracotta Pottery",
        experience: "35 years",
        products: 55,
        image: "fas fa-seedling",
        location: "Rajasthan",
        story: "Master potter creating stunning terracotta works, from everyday utensils to decorative pieces, using traditional firing techniques.",
        rating: 4.9
    },
    {
        id: 5,
        name: "Lakshmi Pradhan",
        tribe: "Kondh Tribe",
        specialty: "Textile Weaving",
        experience: "28 years",
        products: 41,
        image: "fas fa-cut",
        location: "Odisha",
        story: "Skilled weaver creating beautiful textiles with traditional motifs, using natural dyes and handloom techniques passed down through generations.",
        rating: 4.8
    },
    {
        id: 6,
        name: "Ravi Murmu",
        tribe: "Santal Tribe",
        specialty: "Wood Carving",
        experience: "22 years",
        products: 33,
        image: "fas fa-hammer",
        location: "West Bengal",
        story: "Expert wood carver creating intricate sculptures and furniture pieces, preserving ancient carving techniques and tribal symbolism.",
        rating: 4.6
    }
];

// Sample data for products
const productsData = [
    {
        id: 1,
        name: "Warli Tribal Painting",
        artisan: "Rama Devi",
        artisanId: 1,
        category: "paintings",
        price: 2500,
        description: "Traditional Warli painting depicting daily village life with geometric patterns and natural themes.",
        image: "fas fa-paint-brush",
        rating: 4.9,
        reviews: 23,
        inStock: true,
        materials: "Natural pigments on canvas",
        dimensions: "12x16 inches"
    },
    {
        id: 2,
        name: "Gond Art Canvas",
        artisan: "Bhola Ram",
        artisanId: 2,
        category: "paintings",
        price: 3200,
        description: "Vibrant Gond painting featuring intricate nature patterns and tribal folklore elements.",
        image: "fas fa-mountain",
        rating: 4.8,
        reviews: 18,
        inStock: true,
        materials: "Acrylic on canvas",
        dimensions: "14x18 inches"
    },
    {
        id: 3,
        name: "Bamboo Storage Basket",
        artisan: "Sunita Hansda",
        artisanId: 3,
        category: "woodwork",
        price: 800,
        description: "Handwoven bamboo basket perfect for storage, made using traditional Santhal techniques.",
        image: "fas fa-feather",
        rating: 4.7,
        reviews: 31,
        inStock: true,
        materials: "Natural bamboo",
        dimensions: "10x12x8 inches"
    },
    {
        id: 4,
        name: "Terracotta Water Pot",
        artisan: "Kishan Meena",
        artisanId: 4,
        category: "pottery",
        price: 1200,
        description: "Traditional terracotta water pot with beautiful tribal motifs, perfect for home decoration.",
        image: "fas fa-seedling",
        rating: 4.9,
        reviews: 27,
        inStock: true,
        materials: "Clay, natural glaze",
        dimensions: "8x10 inches"
    },
    {
        id: 5,
        name: "Tribal Textile Shawl",
        artisan: "Lakshmi Pradhan",
        artisanId: 5,
        category: "textiles",
        price: 1800,
        description: "Handwoven shawl with traditional Kondh tribe patterns, dyed with natural colors.",
        image: "fas fa-cut",
        rating: 4.8,
        reviews: 15,
        inStock: true,
        materials: "Cotton, natural dyes",
        dimensions: "60x40 inches"
    },
    {
        id: 6,
        name: "Wooden Tribal Mask",
        artisan: "Ravi Murmu",
        artisanId: 6,
        category: "woodwork",
        price: 2200,
        description: "Hand-carved wooden mask representing tribal deities, crafted using traditional Santal techniques.",
        image: "fas fa-hammer",
        rating: 4.6,
        reviews: 12,
        inStock: true,
        materials: "Teak wood, natural finish",
        dimensions: "8x6 inches"
    },
    {
        id: 7,
        name: "Tribal Beaded Necklace",
        artisan: "Rama Devi",
        artisanId: 1,
        category: "jewelry",
        price: 650,
        description: "Handcrafted beaded necklace with traditional Warli patterns and natural stones.",
        image: "fas fa-gem",
        rating: 4.7,
        reviews: 19,
        inStock: true,
        materials: "Beads, natural stones, cotton cord",
        dimensions: "16 inches length"
    },
    {
        id: 8,
        name: "Gond Art Sculpture",
        artisan: "Bhola Ram",
        artisanId: 2,
        category: "woodwork",
        price: 4500,
        description: "Intricate wooden sculpture featuring Gond art motifs and nature-inspired designs.",
        image: "fas fa-mountain",
        rating: 4.9,
        reviews: 8,
        inStock: true,
        materials: "Rosewood, natural finish",
        dimensions: "12x8x6 inches"
    },
    {
        id: 9,
        name: "Santhal Bamboo Lamp",
        artisan: "Sunita Hansda",
        artisanId: 3,
        category: "woodwork",
        price: 950,
        description: "Beautiful bamboo table lamp with traditional Santhal weaving patterns.",
        image: "fas fa-lightbulb",
        rating: 4.6,
        reviews: 22,
        inStock: true,
        materials: "Bamboo, LED bulb included",
        dimensions: "10x8 inches"
    },
    {
        id: 10,
        name: "Terracotta Decorative Bowl",
        artisan: "Kishan Meena",
        artisanId: 4,
        category: "pottery",
        price: 750,
        description: "Handcrafted terracotta bowl with tribal geometric patterns, perfect for home decoration.",
        image: "fas fa-seedling",
        rating: 4.8,
        reviews: 35,
        inStock: true,
        materials: "Clay, natural glaze",
        dimensions: "6x4 inches"
    }
];

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    setupEventListeners();
    displayArtisans();
    displayProducts();
    animateStats();
    setupScrollAnimations();
    setupMobileMenu();
}

// Event Listeners
function setupEventListeners() {
    // Navigation scroll effect
    window.addEventListener('scroll', handleScroll);
    
    // Filter tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            currentFilter = this.dataset.category;
            updateFilterTabs();
            displayProducts();
        });
    });
    
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        searchTerm = this.value.toLowerCase();
        displayProducts();
    });
    
    // Cart modal
    document.querySelector('.cart-icon').addEventListener('click', openCartModal);
    document.querySelector('#cartModal .close').addEventListener('click', closeCartModal);
    document.querySelector('#productModal .close').addEventListener('click', closeProductModal);
    
    // Newsletter form
    document.querySelector('.newsletter-form button').addEventListener('click', handleNewsletter);
    
    // Window resize
    window.addEventListener('resize', handleResize);
}

// Navigation scroll effect
function handleScroll() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 100) {
        navbar.style.background = 'rgba(255, 255, 255, 0.98)';
    } else {
        navbar.style.background = 'rgba(255, 255, 255, 0.95)';
    }
}

// Mobile menu functionality
function setupMobileMenu() {
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    navToggle.addEventListener('click', function() {
        navToggle.classList.toggle('active');
        navMenu.classList.toggle('active');
    });
    
    // Close menu when clicking on a link
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function() {
            navToggle.classList.remove('active');
            navMenu.classList.remove('active');
        });
    });
}

// Display artisans
function displayArtisans() {
    const artisanGrid = document.getElementById('artisanGrid');
    const featuredArtisans = artisansData.slice(0, 3); // Show only first 3 initially
    
    artisanGrid.innerHTML = featuredArtisans.map(artisan => `
        <div class="artisan-card fade-in">
            <div class="artisan-image">
                <i class="${artisan.image}"></i>
            </div>
            <div class="artisan-info">
                <h3 class="artisan-name">${artisan.name}</h3>
                <p class="artisan-tribe">${artisan.tribe}</p>
                <p class="artisan-specialty">${artisan.specialty}</p>
                <div class="artisan-stats">
                    <span>⭐ ${artisan.rating}</span>
                    <span>📍 ${artisan.location}</span>
                </div>
                <div class="artisan-stats">
                    <span>📅 ${artisan.experience}</span>
                    <span>🛍️ ${artisan.products} products</span>
                </div>
                <button class="btn-outline" onclick="viewArtisanProfile(${artisan.id})">View Profile</button>
            </div>
        </div>
    `).join('');
    
    // Trigger animations
    setTimeout(() => {
        document.querySelectorAll('.fade-in').forEach(el => {
            el.classList.add('visible');
        });
    }, 100);
}

// Display all artisans
function showAllArtisans() {
    const artisanGrid = document.getElementById('artisanGrid');
    
    artisanGrid.innerHTML = artisansData.map(artisan => `
        <div class="artisan-card fade-in">
            <div class="artisan-image">
                <i class="${artisan.image}"></i>
            </div>
            <div class="artisan-info">
                <h3 class="artisan-name">${artisan.name}</h3>
                <p class="artisan-tribe">${artisan.tribe}</p>
                <p class="artisan-specialty">${artisan.specialty}</p>
                <div class="artisan-stats">
                    <span>⭐ ${artisan.rating}</span>
                    <span>📍 ${artisan.location}</span>
                </div>
                <div class="artisan-stats">
                    <span>📅 ${artisan.experience}</span>
                    <span>🛍️ ${artisan.products} products</span>
                </div>
                <button class="btn-outline" onclick="viewArtisanProfile(${artisan.id})">View Profile</button>
            </div>
        </div>
    `).join('');
    
    // Trigger animations
    setTimeout(() => {
        document.querySelectorAll('.fade-in').forEach(el => {
            el.classList.add('visible');
        });
    }, 100);
    
    // Update button
    document.querySelector('.view-all button').textContent = 'Show Less';
    document.querySelector('.view-all button').onclick = () => {
        displayArtisans();
        document.querySelector('.view-all button').textContent = 'View All Artisans';
        document.querySelector('.view-all button').onclick = showAllArtisans;
    };
}

// Display products with filtering and search
function displayProducts() {
    const productsGrid = document.getElementById('productsGrid');
    let filteredProducts = productsData;
    
    // Apply category filter
    if (currentFilter !== 'all') {
        filteredProducts = filteredProducts.filter(product => product.category === currentFilter);
    }
    
    // Apply search filter
    if (searchTerm) {
        filteredProducts = filteredProducts.filter(product => 
            product.name.toLowerCase().includes(searchTerm) ||
            product.artisan.toLowerCase().includes(searchTerm) ||
            product.description.toLowerCase().includes(searchTerm)
        );
    }
    
    productsGrid.innerHTML = filteredProducts.map(product => `
        <div class="product-card fade-in">
            <div class="product-image">
                <i class="${product.image}"></i>
            </div>
            <div class="product-info">
                <h3 class="product-name">${product.name}</h3>
                <p class="product-artisan">by ${product.artisan}</p>
                <p class="product-description">${product.description}</p>
                <div class="product-rating">
                    ⭐ ${product.rating} (${product.reviews} reviews)
                </div>
                <div class="product-price">₹${product.price.toLocaleString()}</div>
                <div class="product-actions">
                    <button class="btn-small btn-view" onclick="viewProduct(${product.id})">View Details</button>
                    <button class="btn-small btn-add-cart" onclick="addToCart(${product.id})">Add to Cart</button>
                </div>
            </div>
        </div>
    `).join('');
    
    // Trigger animations
    setTimeout(() => {
        document.querySelectorAll('.fade-in').forEach(el => {
            el.classList.add('visible');
        });
    }, 100);
}

// Update filter tabs
function updateFilterTabs() {
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.classList.remove('active');
        if (tab.dataset.category === currentFilter) {
            tab.classList.add('active');
        }
    });
}

// View product details
function viewProduct(productId) {
    const product = productsData.find(p => p.id === productId);
    if (!product) return;
    
    const modal = document.getElementById('productModal');
    const title = document.getElementById('productModalTitle');
    const content = document.getElementById('productModalContent');
    
    title.textContent = product.name;
    content.innerHTML = `
        <div class="product-detail">
            <div class="product-detail-image">
                <i class="${product.image}"></i>
            </div>
            <div class="product-detail-info">
                <h3>${product.name}</h3>
                <p class="artisan-name">Crafted by ${product.artisan}</p>
                <div class="product-rating">
                    ⭐ ${product.rating} (${product.reviews} reviews)
                </div>
                <div class="product-price">₹${product.price.toLocaleString()}</div>
                <p class="product-description">${product.description}</p>
                
                <div class="product-specs">
                    <h4>Specifications:</h4>
                    <p><strong>Materials:</strong> ${product.materials}</p>
                    <p><strong>Dimensions:</strong> ${product.dimensions}</p>
                    <p><strong>Availability:</strong> ${product.inStock ? 'In Stock' : 'Out of Stock'}</p>
                </div>
                
                <div class="product-actions">
                    <button class="btn-primary" onclick="addToCart(${product.id}); closeProductModal();">Add to Cart</button>
                    <button class="btn-secondary" onclick="closeProductModal()">Close</button>
                </div>
            </div>
        </div>
    `;
    
    modal.style.display = 'block';
}

// Add to cart
function addToCart(productId) {
    const product = productsData.find(p => p.id === productId);
    if (!product) return;
    
    const existingItem = cart.find(item => item.id === productId);
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            ...product,
            quantity: 1
        });
    }
    
    updateCartDisplay();
    showCartNotification();
}

// Update cart display
function updateCartDisplay() {
    const cartCount = document.querySelector('.cart-count');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCount.textContent = totalItems;
    
    // Update cart modal if open
    const cartItems = document.getElementById('cartItems');
    const cartTotal = document.getElementById('cartTotal');
    
    if (cartItems) {
        cartItems.innerHTML = cart.map(item => `
            <div class="cart-item">
                <div class="cart-item-image">
                    <i class="${item.image}"></i>
                </div>
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">₹${item.price.toLocaleString()}</div>
                </div>
                <div class="cart-item-actions">
                    <button class="quantity-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                    <span class="quantity">${item.quantity}</span>
                    <button class="quantity-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                    <button class="quantity-btn" onclick="removeFromCart(${item.id})">×</button>
                </div>
            </div>
        `).join('');
        
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        cartTotal.textContent = total.toLocaleString();
    }
}

// Update quantity
function updateQuantity(productId, change) {
    const item = cart.find(item => item.id === productId);
    if (!item) return;
    
    item.quantity += change;
    if (item.quantity <= 0) {
        removeFromCart(productId);
    } else {
        updateCartDisplay();
    }
}

// Remove from cart
function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    updateCartDisplay();
}

// Show cart notification
function showCartNotification() {
    // Create notification element
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: #d4af37;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        z-index: 1500;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    notification.textContent = 'Item added to cart!';
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Cart modal functions
function openCartModal() {
    const modal = document.getElementById('cartModal');
    updateCartDisplay();
    modal.style.display = 'block';
}

function closeCartModal() {
    const modal = document.getElementById('cartModal');
    modal.style.display = 'none';
}

function closeProductModal() {
    const modal = document.getElementById('productModal');
    modal.style.display = 'none';
}

// Checkout function
function checkout() {
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }
    
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    alert(`Thank you for your purchase! Total: ₹${total.toLocaleString()}\n\nThis is a demo. In a real application, this would redirect to a payment gateway.`);
    
    // Clear cart
    cart = [];
    updateCartDisplay();
    closeCartModal();
}

// Animate stats
function animateStats() {
    const stats = document.querySelectorAll('.stat-number');
    
    const animateValue = (element, start, end, duration) => {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            element.textContent = value;
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    };
    
    // Intersection Observer for stats animation
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = parseInt(entry.target.dataset.target);
                animateValue(entry.target, 0, target, 2000);
                observer.unobserve(entry.target);
            }
        });
    });
    
    stats.forEach(stat => {
        observer.observe(stat);
    });
}

// Scroll animations
function setupScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right').forEach(el => {
        observer.observe(el);
    });
}

// Scroll to section
function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth' });
    }
}

// View artisan profile
function viewArtisanProfile(artisanId) {
    const artisan = artisansData.find(a => a.id === artisanId);
    if (!artisan) return;
    
    const modal = document.getElementById('productModal');
    const title = document.getElementById('productModalTitle');
    const content = document.getElementById('productModalContent');
    
    title.textContent = `${artisan.name} - Artisan Profile`;
    content.innerHTML = `
        <div class="artisan-profile">
            <div class="artisan-profile-image">
                <i class="${artisan.image}"></i>
            </div>
            <div class="artisan-profile-info">
                <h3>${artisan.name}</h3>
                <p class="tribe-name">${artisan.tribe}</p>
                <p class="specialty">Specializes in: ${artisan.specialty}</p>
                
                <div class="artisan-stats">
                    <div class="stat">
                        <strong>Experience:</strong> ${artisan.experience}
                    </div>
                    <div class="stat">
                        <strong>Location:</strong> ${artisan.location}
                    </div>
                    <div class="stat">
                        <strong>Products:</strong> ${artisan.products}
                    </div>
                    <div class="stat">
                        <strong>Rating:</strong> ⭐ ${artisan.rating}
                    </div>
                </div>
                
                <div class="artisan-story">
                    <h4>About the Artisan:</h4>
                    <p>${artisan.story}</p>
                </div>
                
                <div class="artisan-actions">
                    <button class="btn-primary" onclick="scrollToSection('products'); closeProductModal();">View Products</button>
                    <button class="btn-secondary" onclick="closeProductModal()">Close</button>
                </div>
            </div>
        </div>
    `;
    
    modal.style.display = 'block';
}

// Newsletter subscription
function handleNewsletter() {
    const email = document.querySelector('.newsletter-form input').value;
    if (!email) {
        alert('Please enter your email address');
        return;
    }
    
    if (!isValidEmail(email)) {
        alert('Please enter a valid email address');
        return;
    }
    
    alert('Thank you for subscribing! You will receive updates about new artisans and products.');
    document.querySelector('.newsletter-form input').value = '';
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Handle window resize
function handleResize() {
    // Close mobile menu on resize
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (window.innerWidth > 768) {
        navToggle.classList.remove('active');
        navMenu.classList.remove('active');
    }
}

// Close modals when clicking outside
window.addEventListener('click', function(event) {
    const cartModal = document.getElementById('cartModal');
    const productModal = document.getElementById('productModal');
    
    if (event.target === cartModal) {
        closeCartModal();
    }
    
    if (event.target === productModal) {
        closeProductModal();
    }
});

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
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

// Add CSS for product and artisan detail modals
const additionalStyles = `
<style>
.product-detail {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    align-items: start;
}

.product-detail-image {
    background: linear-gradient(135deg, #d4af37, #b8941f);
    border-radius: 15px;
    height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: white;
}

.product-detail-info h3 {
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
    color: #333;
}

.artisan-name {
    color: #d4af37;
    font-weight: 500;
    margin-bottom: 1rem;
}

.product-rating {
    margin-bottom: 1rem;
    color: #666;
}

.product-price {
    font-size: 1.8rem;
    font-weight: 700;
    color: #d4af37;
    margin-bottom: 1rem;
}

.product-description {
    color: #666;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.product-specs {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 10px;
    margin-bottom: 2rem;
}

.product-specs h4 {
    margin-bottom: 1rem;
    color: #333;
}

.product-specs p {
    margin-bottom: 0.5rem;
    color: #666;
}

.artisan-profile {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    align-items: start;
}

.artisan-profile-image {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 15px;
    height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: white;
}

.artisan-profile-info h3 {
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
    color: #333;
}

.tribe-name {
    color: #d4af37;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.specialty {
    color: #666;
    margin-bottom: 1.5rem;
}

.artisan-stats {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
}

.artisan-stats .stat {
    margin-bottom: 0.5rem;
    color: #666;
}

.artisan-story {
    margin-bottom: 2rem;
}

.artisan-story h4 {
    margin-bottom: 1rem;
    color: #333;
}

.artisan-story p {
    color: #666;
    line-height: 1.6;
}

.artisan-actions {
    display: flex;
    gap: 1rem;
}

@media (max-width: 768px) {
    .product-detail,
    .artisan-profile {
        grid-template-columns: 1fr;
    }
    
    .product-detail-image,
    .artisan-profile-image {
        height: 200px;
        font-size: 3rem;
    }
    
    .artisan-actions {
        flex-direction: column;
    }
}
</style>
`;

// Add the additional styles to the document
document.head.insertAdjacentHTML('beforeend', additionalStyles);
