// Global state
const userRole = window.appUserRole || 'customer';
const isAdmin = () => userRole === 'admin';
let currentPage = isAdmin() ? 'dashboard' : 'pos';
let cart = [];
let products = [];
let categories = [];
let inventorySortConfig = { field: 'stock_quantity', ascending: false };
let inventoryFilterLowStock = false; // when true, inventory table shows only low-stock items (<10)

// Currency helpers
const CURRENCY_SYMBOL = '\u20B1';
function formatCurrency(value) {
    const amount = parseFloat(value);
    return `${CURRENCY_SYMBOL}${(Number.isFinite(amount) ? amount : 0).toFixed(2)}`;
}

// Helpers
function normalizeId(value) {
    if (value === null || value === undefined) return null;
    const numericValue = Number(value);
    return Number.isNaN(numericValue) ? null : numericValue;
}

function findProductById(productId) {
    const normalizedId = normalizeId(productId);
    if (normalizedId === null) return null;
    return products.find(p => normalizeId(p.id) === normalizedId);
}

function findCartItem(productId) {
    const normalizedId = normalizeId(productId);
    if (normalizedId === null) return null;
    return cart.find(item => normalizeId(item.product_id) === normalizedId);
}

// Initialize app
document.addEventListener('DOMContentLoaded', async function() {
    configureRoleBasedUI();
    initializeNavigation();
    await Promise.all([loadCategories(), loadProducts()]);
    switchPage(currentPage);
    setupEventListeners();
    setupSidebarHover();
});

// Sidebar hover behavior: collapse by default, expand on hover
function setupSidebarHover() {
    try {
        const body = document.body;
        const sidebar = document.querySelector('.sidebar');

        // Start collapsed by default
        if (!body.classList.contains('sidebar-collapsed')) {
            body.classList.add('sidebar-collapsed');
        }

        if (!sidebar) return;

        let expandTimer = null;
        sidebar.addEventListener('mouseenter', () => {
            // Cancel collapse and expand immediately
            clearTimeout(expandTimer);
            body.classList.remove('sidebar-collapsed');
            body.classList.add('sidebar-expanded');
        });

        sidebar.addEventListener('mouseleave', () => {
            // Delay collapsing slightly for smoother UX
            body.classList.remove('sidebar-expanded');
            expandTimer = setTimeout(() => {
                body.classList.add('sidebar-collapsed');
            }, 220);
        });

        // Also allow clicking the small handle area to toggle (for keyboard users)
        sidebar.addEventListener('click', (e) => {
            // If clicked near the right edge when collapsed, expand
            if (body.classList.contains('sidebar-collapsed')) {
                const rect = sidebar.getBoundingClientRect();
                if (e.clientX > rect.right - 36) {
                    body.classList.remove('sidebar-collapsed');
                    body.classList.add('sidebar-expanded');
                }
            }
        });

        // Fallback: listen to document mousemove near left edge so expansion works
        // even on pages where the sidebar element may not be responsive to hover.
        let docCollapseTimer = null;
        document.addEventListener('mousemove', (ev) => {
            const x = ev.clientX;
            // If cursor is within 36px of left edge, expand
            if (x <= 36) {
                clearTimeout(docCollapseTimer);
                if (body.classList.contains('sidebar-collapsed')) {
                    body.classList.remove('sidebar-collapsed');
                    body.classList.add('sidebar-expanded');
                }
            } else {
                // If cursor moves away and sidebar is expanded, schedule collapse
                if (body.classList.contains('sidebar-expanded')) {
                    clearTimeout(docCollapseTimer);
                    docCollapseTimer = setTimeout(() => {
                        body.classList.remove('sidebar-expanded');
                        body.classList.add('sidebar-collapsed');
                    }, 500);
                }
            }
        });
    } catch (err) {
        console.error('Sidebar hover setup error:', err);
    }
}

function configureRoleBasedUI() {
    const roleLabel = document.getElementById('user-role-label');
    if (roleLabel) {
        roleLabel.textContent = isAdmin() ? 'Administrator' : 'Customer';
    }

    const usernameLabel = document.getElementById('user-name-label');
    if (usernameLabel && window.appUsername) {
        usernameLabel.textContent = window.appUsername;
    }

    if (!isAdmin()) {
        document.querySelectorAll('[data-role="admin-only"]').forEach(el => {
            el.style.display = 'none';
        });
        document.querySelectorAll('.admin-only').forEach(el => {
            el.style.display = 'none';
        });
    }
}

// Navigation
function initializeNavigation() {
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const page = this.dataset.page;
            switchPage(page);
        });
    });
}

function switchPage(page) {
    if (!isPageAllowed(page)) {
        alert('You do not have access to this section.');
        return;
    }

    // Update nav
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
        if (item.dataset.page === page) {
            item.classList.add('active');
        }
    });
    
    // Update pages
    document.querySelectorAll('.page').forEach(p => {
        p.classList.remove('active');
    });
    document.getElementById(page).classList.add('active');
    
    currentPage = page;
    
    // Load page-specific data
    switch(page) {
        case 'dashboard':
            if (isAdmin()) loadDashboard();
            break;
        case 'pos':
            loadPOS();
            break;
        case 'products':
            if (isAdmin()) loadProductsTable();
            break;
        case 'inventory':
            if (isAdmin()) loadInventoryTable();
            break;
        case 'sales':
            if (isAdmin()) loadSalesHistory();
            break;
    }
}

function isPageAllowed(page) {
    if (isAdmin()) return true;
    return page === 'pos';
}

// Dashboard
async function loadDashboard() {
    if (!isAdmin()) return;
    try {
        const response = await fetch('api/reports.php?type=dashboard');
        const data = await response.json();
        
        document.getElementById('today-orders').textContent = data.today_orders || 0;
        document.getElementById('today-revenue').textContent = formatCurrency(data.today_revenue || 0);
        document.getElementById('low-stock').textContent = data.low_stock_count || 0;
        // Make the low-stock number open the inventory filtered to low-stock items
        const lowStockEl = document.getElementById('low-stock');
        if (lowStockEl) {
            lowStockEl.onclick = function() {
                inventoryFilterLowStock = true;
                switchPage('inventory');
                // loadInventoryTable will pick up the filter and show the clear button
                loadInventoryTable();
                updateSortIndicators();
            };
        }
        
        // Load total products
        const productsResponse = await fetch('api/products.php');
        const productsData = await productsResponse.json();
        document.getElementById('total-products').textContent = productsData.length || 0;
        
        // Top products
        const topProductsList = document.getElementById('top-products-list');
        if (data.top_products && data.top_products.length > 0) {
            topProductsList.innerHTML = data.top_products.map(p => `
                <div style="display: flex; justify-content: space-between; padding: 12px; background: #f8fafc; border-radius: 8px; margin-bottom: 10px;">
                    <span><strong>${p.name}</strong></span>
                    <span>Sold: ${p.total_sold} | Revenue: ${formatCurrency(p.revenue)}</span>
                </div>
            `).join('');
        } else {
            topProductsList.innerHTML = '<p style="color: #64748b;">No sales data available</p>';
        }
    } catch (error) {
        console.error('Error loading dashboard:', error);
    }
}

// POS
async function loadPOS() {
    await loadProducts();
    renderProductGrid();
    updateCart();
}

function renderProductGrid() {
    const grid = document.getElementById('product-grid');
    
    if (!grid) {
        console.error('Product grid element not found');
        return;
    }
    
    const activeProducts = products.filter(p => p.status === 'active' || p.status === undefined);
    
    if (activeProducts.length === 0) {
        grid.innerHTML = '<p style="text-align: center; padding: 20px; color: #64748b;">No products available</p>';
        return;
    }
    
    grid.innerHTML = activeProducts.map(product => `
        <div class="product-card" data-product-id="${product.id}" onclick="addToCart(${product.id})" style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;">
            <h4>${product.name}</h4>
            <div class="price">${formatCurrency(product.price)}</div>
        </div>
    `).join('');
    
    // Add hover effect
    const cards = grid.querySelectorAll('.product-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.02)';
            this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
            this.style.boxShadow = '';
        });
    });
}

function addToCart(productId) {
    const normalizedId = normalizeId(productId);
    const product = findProductById(productId);
    
    if (!product || normalizedId === null) {
        console.error('Product not found:', productId);
        alert('Product not found!');
        return;
    }
    
    // Check if product is active
    if (product.status && product.status !== 'active') {
        alert('This product is not available for sale!');
        return;
    }
    
    // Check stock availability
    if (product.stock_quantity <= 0) {
        alert('Product is out of stock!');
        return;
    }
    
    // Find existing item in cart
    const existingItem = findCartItem(productId);
    
    if (existingItem) {
        // Check if we can add more
        if (existingItem.quantity >= product.stock_quantity) {
            alert(`Cannot add more. Only ${product.stock_quantity} items available in stock!`);
            return;
        }
        // Increment quantity
        existingItem.quantity++;
        existingItem.subtotal = existingItem.quantity * existingItem.unit_price;
    } else {
        // Add new item to cart
        cart.push({
            product_id: normalizedId,
            product_name: product.name,
            quantity: 1,
            unit_price: parseFloat(product.price),
            subtotal: parseFloat(product.price)
        });
    }
    
    // Visual feedback - highlight the product card briefly
    const productCard = document.querySelector(`.product-card[data-product-id="${product.id}"]`);
    if (productCard) {
        productCard.style.backgroundColor = '#d1fae5';
        productCard.style.transform = 'scale(0.95)';
        setTimeout(() => {
            productCard.style.backgroundColor = '';
            productCard.style.transform = '';
        }, 300);
    }
    
    // Update cart display
    updateCart();
    
    // Scroll cart into view if needed
    const cartSection = document.getElementById('cart-items');
    if (cartSection) {
        cartSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    // Show success notification (optional - can be removed if too intrusive)
    showCartNotification(`${product.name} added to cart!`);
}

// Helper function to show cart notification
function showCartNotification(message) {
    // Remove existing notification if any
    const existingNotification = document.getElementById('cart-notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.id = 'cart-notification';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #10b981;
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
        font-weight: 500;
    `;
    
    // Add animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(notification);
    
    // Remove notification after 2 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
            notification.remove();
            style.remove();
        }, 300);
    }, 2000);
}

function removeFromCart(productId) {
    const normalizedId = normalizeId(productId);
    cart = cart.filter(item => normalizeId(item.product_id) !== normalizedId);
    updateCart();
}

function updateCartQuantity(productId, quantity) {
    const item = findCartItem(productId);
    if (item) {
        const product = findProductById(productId);
        if (!product) {
            alert('Product details not found.');
            return;
        }
        const parsedQuantity = Math.max(1, parseInt(quantity));
        if (parsedQuantity > product.stock_quantity) {
            alert('Not enough stock available!');
            item.quantity = product.stock_quantity;
        } else {
            item.quantity = parsedQuantity;
        }
        item.subtotal = item.quantity * item.unit_price;
        updateCart();
    }
}

function updateCart() {
    const cartItems = document.getElementById('cart-items');
    
    if (!cartItems) {
        console.error('Cart items element not found');
        return;
    }
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<p style="text-align: center; color: #64748b; padding: 20px;">Cart is empty</p>';
    } else {
        cartItems.innerHTML = cart.map((item, index) => `
            <div class="cart-item" data-product-id="${item.product_id}" style="animation: slideInCart 0.3s ease-out ${index * 0.05}s both;">
                <div class="cart-item-info">
                    <h4>${item.product_name}</h4>
                    <p>${formatCurrency(item.unit_price)} each</p>
                </div>
                <div class="cart-item-controls">
                    <button onclick="updateCartQuantity(${item.product_id}, ${item.quantity - 1})" title="Decrease quantity">-</button>
                    <input type="number" value="${item.quantity}" min="1" 
                           onchange="updateCartQuantity(${item.product_id}, this.value)"
                           style="text-align: center; width: 50px;">
                    <button onclick="updateCartQuantity(${item.product_id}, ${item.quantity + 1})" title="Increase quantity">+</button>
                    <button onclick="removeFromCart(${item.product_id})" style="background: #ef4444; margin-left: 10px;" title="Remove from cart">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div style="margin-left: 15px; font-weight: 600; color: #1e40af;">
                    ${formatCurrency(item.subtotal)}
                </div>
            </div>
        `).join('');
        
        // Add cart animation styles if not already added
        if (!document.getElementById('cart-animation-style')) {
            const style = document.createElement('style');
            style.id = 'cart-animation-style';
            style.textContent = `
                @keyframes slideInCart {
                    from {
                        opacity: 0;
                        transform: translateY(-10px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    const subtotal = cart.reduce((sum, item) => sum + item.subtotal, 0);
    const discount = parseFloat(document.getElementById('discount-input').value) || 0;
    const total = subtotal - discount;
    
    document.getElementById('cart-subtotal').textContent = formatCurrency(subtotal);
    document.getElementById('cart-total').textContent = formatCurrency(total);
    
    // Update discount on change
    document.getElementById('discount-input').addEventListener('input', function() {
        const newDiscount = parseFloat(this.value) || 0;
        const newTotal = subtotal - newDiscount;
        document.getElementById('cart-total').textContent = formatCurrency(newTotal);
    });
}

async function checkout() {
    if (cart.length === 0) {
        alert('Cart is empty!');
        return;
    }
    
    const subtotal = cart.reduce((sum, item) => sum + item.subtotal, 0);
    const discount = parseFloat(document.getElementById('discount-input').value) || 0;
    const total = subtotal - discount;
    const paymentMethod = document.getElementById('payment-method').value;
    
    const saleData = {
        items: cart,
        total_amount: subtotal,
        discount: discount,
        final_amount: total,
        payment_method: paymentMethod
    };
    
    try {
        const response = await fetch('api/sales.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(saleData)
        });
        
        const result = await response.json();
        if (result.success) {
            alert(`Order completed! Order #: ${result.order_number}`);
            cart = [];
            updateCart();
            loadProducts(); // Refresh product stock
            loadDashboard(); // Update dashboard stats
        } else {
            alert('Error processing order: ' + result.error);
        }
    } catch (error) {
        alert('Error processing order: ' + error.message);
    }
}

// Products
async function loadProducts() {
    try {
        const response = await fetch('api/products.php');
        products = await response.json();
    } catch (error) {
        console.error('Error loading products:', error);
    }
}

async function loadCategories() {
    try {
        const response = await fetch('api/categories.php');
        categories = await response.json();
        
        const categorySelect = document.getElementById('product-category');
        categorySelect.innerHTML = '<option value="">Select Category</option>' +
            categories.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('');
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

async function loadProductsTable() {
    if (!isAdmin()) return;
    await loadProducts();
    const tbody = document.getElementById('products-table-body');
    tbody.innerHTML = products.map(product => `
        <tr>
            <td>${product.id}</td>
            <td>${product.name}</td>
            <td>${product.category_name || 'N/A'}</td>
            <td>${formatCurrency(product.price)}</td>
            <td>${formatCurrency(product.cost)}</td>
            <td>
                <button class="btn btn-primary" onclick="editProduct(${product.id})">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-danger" onclick="deleteProduct(${product.id})">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </td>
        </tr>
    `).join('');
}

async function loadInventoryTable() {
    if (!isAdmin()) return;
    await loadProducts();
    const tbody = document.getElementById('inventory-table-body');
    const lowStockProducts = products.filter(p => p.stock_quantity < 10);
    
    // Sort based on current config
    let sortedProducts = [...products];
    sortedProducts.sort((a, b) => {
        let aVal = a[inventorySortConfig.field];
        let bVal = b[inventorySortConfig.field];
        
        // Handle numeric fields
        if (typeof aVal === 'string' && !isNaN(aVal)) aVal = parseFloat(aVal);
        if (typeof bVal === 'string' && !isNaN(bVal)) bVal = parseFloat(bVal);
        
        if (aVal < bVal) return inventorySortConfig.ascending ? -1 : 1;
        if (aVal > bVal) return inventorySortConfig.ascending ? 1 : -1;
        return 0;
    });
    
    // If filtering for low-stock items, apply the filter
    let displayedProducts = sortedProducts;
    if (inventoryFilterLowStock) {
        displayedProducts = sortedProducts.filter(p => Number(p.stock_quantity) < 10);
    }

    // Show or hide clear filter button
    const clearBtn = document.getElementById('clear-inventory-filter');
    if (clearBtn) {
        clearBtn.style.display = inventoryFilterLowStock ? 'inline-block' : 'none';
    }

    tbody.innerHTML = displayedProducts.map(product => `
        <tr style="${product.stock_quantity < 10 ? 'background: #fef2f2;' : ''}">
            <td>${product.id}</td>
            <td>${product.name}</td>
            <td><strong>${product.stock_quantity}</strong></td>
            <td>${product.unit}</td>
            <td>
                <span style="color: ${product.stock_quantity < 10 ? '#ef4444' : '#10b981'};">
                    ${product.stock_quantity < 10 ? 'Low Stock' : 'In Stock'}
                </span>
            </td>
            <td>
                <button class="btn btn-primary" onclick="adjustStock(${product.id})">
                    <i class="fas fa-edit"></i> Adjust
                </button>
            </td>
        </tr>
    `).join('');
}

async function loadSalesHistory() {
    if (!isAdmin()) return;
    try {
        const response = await fetch('api/sales.php');
        const sales = await response.json();
        const tbody = document.getElementById('sales-table-body');
        tbody.innerHTML = sales.map(sale => `
            <tr>
                <td>${sale.order_number}</td>
                <td>${new Date(sale.created_at).toLocaleString()}</td>
                <td>${formatCurrency(sale.total_amount)}</td>
                <td>${formatCurrency(sale.discount)}</td>
                <td><strong>${formatCurrency(sale.final_amount)}</strong></td>
                <td>${sale.payment_method}</td>
                <td>
                    <button class="btn btn-primary" onclick="viewSaleDetails(${sale.id})">
                        <i class="fas fa-eye"></i> View
                    </button>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Error loading sales:', error);
    }
}

// Product Modal
async function openProductModal(productId = null) {
    if (!isAdmin()) {
        alert('Only administrators can manage products.');
        return;
    }
    const modal = document.getElementById('product-modal');
    const form = document.getElementById('product-form');
    const title = document.getElementById('modal-title');
    const idGroup = document.getElementById('product-id-group');
    
    if (productId) {
        title.textContent = 'Edit Product';
        idGroup.style.display = 'block';
        
        // Show modal first with loading state
        modal.style.display = 'block';
        
        try {
            // Fetch fresh product data from API to ensure we have latest info
            const response = await fetch(`api/products.php?id=${productId}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const product = await response.json();
            
            // Check if product exists and has an id
            if (!product || !product.id) {
                throw new Error('Product not found');
            }
            
            // Populate all fields
            document.getElementById('product-id').value = product.id || '';
            document.getElementById('product-name').value = product.name || '';
            document.getElementById('product-category').value = product.category_id || '';
            document.getElementById('product-description').value = product.description || '';
            document.getElementById('product-price').value = product.price || '0.00';
            document.getElementById('product-cost').value = product.cost || '0.00';
            document.getElementById('product-stock').value = product.stock_quantity || 0;
            document.getElementById('product-unit').value = product.unit || 'piece';
            
        } catch (error) {
            console.error('Error loading product:', error);
            
            // Fallback to local data
            const product = findProductById(productId);
            if (product && product.id) {
                document.getElementById('product-id').value = product.id;
                document.getElementById('product-name').value = product.name || '';
                document.getElementById('product-category').value = product.category_id || '';
                document.getElementById('product-description').value = product.description || '';
                document.getElementById('product-price').value = product.price || '0.00';
                document.getElementById('product-cost').value = product.cost || '0.00';
                document.getElementById('product-stock').value = product.stock_quantity || 0;
                document.getElementById('product-unit').value = product.unit || 'piece';
            } else {
                alert('Error loading product data. Please try again.');
                modal.style.display = 'none';
                return;
            }
        }
    } else {
        title.textContent = 'Add Product';
        idGroup.style.display = 'none';
        form.reset();
        document.getElementById('product-id').value = '';
        modal.style.display = 'block';
    }
}

function closeProductModal() {
    document.getElementById('product-modal').style.display = 'none';
}

// Enhanced Edit Product Function
async function editProduct(id) {
    if (!isAdmin()) {
        alert('Only administrators can edit products.');
        return;
    }
    if (!id) {
        alert('Product ID is required');
        return;
    }
    
    // Ensure categories are loaded for the dropdown
    if (categories.length === 0) {
        await loadCategories();
    }
    
    // Open modal with product data (it will fetch the product internally)
    await openProductModal(id);
}

// Save Product Function - Handles all fields: id, name, category, price, cost, stock
async function saveProduct(e) {
    if (!isAdmin()) {
        alert('Only administrators can save products.');
        return;
    }
    e.preventDefault();
    
    // Get all form values
    const productIdInput = document.getElementById('product-id');
    const productId = productIdInput ? productIdInput.value : '';
    const name = document.getElementById('product-name').value.trim();
    const categoryId = document.getElementById('product-category').value;
    const description = document.getElementById('product-description').value.trim();
    const price = parseFloat(document.getElementById('product-price').value);
    const cost = parseFloat(document.getElementById('product-cost').value);
    const stockQuantity = parseInt(document.getElementById('product-stock').value) || 0;
    const unit = document.getElementById('product-unit').value.trim() || 'piece';
    
    // Debug logging
    console.log('Saving product:', { productId, name, isEdit: !!productId });
    
    // Validation
    if (!name) {
        alert('Product name is required!');
        return;
    }
    
    if (isNaN(price) || price < 0) {
        alert('Please enter a valid price!');
        return;
    }
    
    if (isNaN(cost) || cost < 0) {
        alert('Please enter a valid cost!');
        return;
    }
    
    if (isNaN(stockQuantity) || stockQuantity < 0) {
        alert('Please enter a valid stock quantity!');
        return;
    }
    
    // Prepare product data with all fields
    const productData = {
        id: productId ? parseInt(productId) : null,
        name: name,
        category_id: categoryId ? parseInt(categoryId) : null,
        description: description,
        price: price.toFixed(2),
        cost: cost.toFixed(2),
        stock_quantity: stockQuantity,
        unit: unit
    };
    
    const url = 'api/products.php';
    const isEdit = productData.id !== null && productData.id > 0;
    
    // Use POST for both create and update (API handles both)
    // This works better with XAMPP and servers that don't support PUT properly
    const method = 'POST';
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(productData)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            // Show success message
            alert(`Product ${isEdit ? 'updated' : 'created'} successfully!`);
            closeProductModal();
            
            // Reload data
            await loadProducts();
            if (currentPage === 'products') loadProductsTable();
            if (currentPage === 'inventory') loadInventoryTable();
            if (currentPage === 'pos') loadPOS();
            if (currentPage === 'dashboard') loadDashboard();
        } else {
            const errorMsg = result.error || result.message || 'Unknown error';
            alert('Error saving product: ' + errorMsg);
            console.error('Save product error:', result);
        }
    } catch (error) {
        alert('Error saving product: ' + error.message);
        console.error('Save product error:', error);
    }
}

async function deleteProduct(id) {
    if (!isAdmin()) {
        alert('Only administrators can delete products.');
        return;
    }
    if (!confirm('Are you sure you want to delete this product?')) return;
    
    try {
        const response = await fetch(`api/products.php?id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        if (result.success) {
            loadProductsTable();
        } else {
            alert('Error deleting product: ' + result.error);
        }
    } catch (error) {
        alert('Error deleting product: ' + error.message);
    }
}

function adjustStock(id) {
    if (!isAdmin()) {
        alert('Only administrators can adjust stock.');
        return;
    }
    const product = findProductById(id);
    const newStock = prompt(`Current stock: ${product.stock_quantity}\nEnter new stock quantity:`, product.stock_quantity);
    
    if (newStock !== null && !isNaN(newStock)) {
        const productData = {
            id: product.id,
            name: product.name,
            category_id: product.category_id,
            description: product.description || '',
            price: product.price,
            cost: product.cost,
            stock_quantity: parseInt(newStock),
            unit: product.unit || 'piece'
        };
        
        fetch('api/products.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(productData)
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                alert('Stock updated successfully!');
                loadProducts();
                loadInventoryTable();
            } else {
                alert('Error updating stock: ' + (result.error || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error updating stock: ' + error.message);
            console.error('Adjust stock error:', error);
        });
    }
}

function viewSaleDetails(id) {
    fetch(`api/sales.php?id=${id}`)
        .then(res => res.json())
        .then(sale => {
            let itemsList = sale.items.map(item => 
                `${item.product_name} x${item.quantity} - ${formatCurrency(item.subtotal)}`
            ).join('\n');
            
            alert(`Order #: ${sale.order_number}\nDate: ${new Date(sale.created_at).toLocaleString()}\n\nItems:\n${itemsList}\n\nTotal: ${formatCurrency(sale.final_amount)}`);
        });
}

// Event Listeners
function setupEventListeners() {
    // Product modal
    const addProductBtn = document.getElementById('add-product-btn');
    const cancelProductBtn = document.getElementById('cancel-product-btn');
    const productForm = document.getElementById('product-form');
    const modalCloseBtn = document.querySelector('.close');

    if (addProductBtn && isAdmin()) {
        addProductBtn.addEventListener('click', () => openProductModal());
    }
    if (cancelProductBtn) {
        cancelProductBtn.addEventListener('click', closeProductModal);
    }
    if (productForm && isAdmin()) {
        productForm.addEventListener('submit', saveProduct);
    }
    if (modalCloseBtn) {
        modalCloseBtn.addEventListener('click', closeProductModal);
    }
    
    // Checkout
    document.getElementById('checkout-btn').addEventListener('click', checkout);
    
    // Sortable inventory headers
    if (isAdmin()) {
        document.querySelectorAll('#inventory th.sortable').forEach(th => {
            th.addEventListener('click', function() {
                const field = this.dataset.sortField;
                if (inventorySortConfig.field === field) {
                    // Toggle ascending/descending
                    inventorySortConfig.ascending = !inventorySortConfig.ascending;
                } else {
                    // New field, default to ascending
                    inventorySortConfig.field = field;
                    inventorySortConfig.ascending = true;
                }
                loadInventoryTable();
                updateSortIndicators();
            });
        });
        // Clear filter button
        const clearFilterBtn = document.getElementById('clear-inventory-filter');
        if (clearFilterBtn) {
            clearFilterBtn.addEventListener('click', function() {
                inventoryFilterLowStock = false;
                loadInventoryTable();
                updateSortIndicators();
            });
        }
    }
    
    // Close modal on outside click
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('product-modal');
        if (e.target === modal && isAdmin()) {
            closeProductModal();
        }
    });
}

// Update sort indicators on inventory headers
function updateSortIndicators() {
    document.querySelectorAll('#inventory th.sortable').forEach(th => {
        const indicator = th.querySelector('.sort-indicator');
        if (th.dataset.sortField === inventorySortConfig.field) {
            indicator.textContent = inventorySortConfig.ascending ? '↑' : '↓';
            th.style.color = '#2563eb';
            th.style.fontWeight = 'bold';
        } else {
            indicator.textContent = '⇅';
            th.style.color = '';
            th.style.fontWeight = '';
        }
    });
}

// Make functions globally available
window.addToCart = addToCart;
window.removeFromCart = removeFromCart;
window.updateCartQuantity = updateCartQuantity;
window.editProduct = editProduct;
window.deleteProduct = deleteProduct;
window.adjustStock = adjustStock;
window.viewSaleDetails = viewSaleDetails;


