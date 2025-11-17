<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fast Food Sales & Inventory System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body data-user-role="<?php echo htmlspecialchars($userRole); ?>" <?php if ($userRole === 'admin') echo 'class="sidebar-collapsed"'; ?> >
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-hamburger"></i>
                <h2>Fast Food POS</h2>
            </div>
            <div class="user-panel" style="margin-bottom: 20px;">
                <p id="user-name-label" style="font-weight: 600; margin-bottom: 4px;"><?php echo htmlspecialchars($username); ?></p>
                <span id="user-role-label" style="background: #1d4ed8; color: #fff; padding: 4px 10px; border-radius: 999px; font-size: 12px;">
                    <?php echo $userRole === 'admin' ? 'Administrator' : 'Customer'; ?>
                </span>
            </div>
            <nav class="nav-menu">
                <a href="#" class="nav-item <?php echo $userRole === 'admin' ? 'active' : ''; ?>" data-page="dashboard" data-role="admin-only">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
                <a href="#" class="nav-item <?php echo $userRole !== 'admin' ? 'active' : ''; ?>" data-page="pos" <?php echo $userRole === 'admin' ? 'data-role="admin-only" style="display:none;"' : ''; ?>>
                    <i class="fas fa-cash-register"></i> Point of Sale
                </a>
                <a href="#" class="nav-item" data-page="products" data-role="admin-only">
                    <i class="fas fa-box"></i> Products
                </a>
                <a href="#" class="nav-item" data-page="inventory" data-role="admin-only">
                    <i class="fas fa-warehouse"></i> Inventory
                </a>
                <a href="#" class="nav-item" data-page="sales" data-role="admin-only">
                    <i class="fas fa-shopping-cart"></i> Sales History
                </a>
                <a href="#" class="nav-item" data-page="reports" data-role="admin-only">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </nav>
            <div style="margin-top: auto;">
                <?php if ($userRole === 'admin'): ?>
                    <a href="auth/logout.php" class="btn btn-secondary" style="display: block; text-align: center;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary" style="display: block; text-align: center;">
                        <i class="fas fa-sign-in-alt"></i> Admin Login
                    </a>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Dashboard Page -->
            <div id="dashboard" class="page <?php echo $userRole === 'admin' ? 'active' : ''; ?>" data-role="admin-only">
                <h1>Dashboard</h1>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #4CAF50;">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="today-orders">0</h3>
                            <p>Today's Orders</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #2196F3;">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="today-revenue">&#8369;0.00</h3>
                            <p>Today's Revenue</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #FF9800;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="low-stock" style="cursor: pointer;" title="Click to view low stock items">0</h3>
                            <p>Low Stock Items</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #9C27B0;">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="total-products">0</h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                </div>
                <div class="dashboard-section">
                    <h2>Top Selling Products (Last 7 Days)</h2>
                    <div id="top-products-list"></div>
                </div>
            </div>

            <!-- POS Page -->
            <div id="pos" class="page<?php echo $userRole !== 'admin' ? ' active' : ''; ?>">
                <h1>Point of Sale</h1>
                <div class="pos-container">
                    <div class="pos-left">
                        <div class="product-grid" id="product-grid"></div>
                    </div>
                    <div class="pos-right">
                        <div class="cart-section">
                            <h3>Order Cart</h3>
                            <div class="cart-items" id="cart-items"></div>
                            <div class="cart-summary">
                                <div class="summary-row">
                                    <span>Subtotal:</span>
                                    <span id="cart-subtotal">&#8369;0.00</span>
                                </div>
                                <div class="summary-row">
                                    <span>Discount:</span>
                                    <input type="number" id="discount-input" value="0" min="0" step="0.01" style="width: 100px;">
                                </div>
                                <div class="summary-row total">
                                    <span>Total:</span>
                                    <span id="cart-total">&#8369;0.00</span>
                                </div>
                                <div class="payment-method">
                                    <label>Payment Method:</label>
                                    <select id="payment-method">
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                        <option value="mobile">Mobile Payment</option>
                                    </select>
                                </div>
                                <button class="btn btn-primary btn-large" id="checkout-btn">
                                    <i class="fas fa-check"></i> Complete Order
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Page -->
            <div id="products" class="page" data-role="admin-only">
                <div class="page-header">
                    <h1>Products</h1>
                    <button class="btn btn-primary admin-only" id="add-product-btn">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Cost</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="products-table-body"></tbody>
                    </table>
                </div>
            </div>

            <!-- Inventory Page -->
            <div id="inventory" class="page" data-role="admin-only">
                <h1>Inventory Management <button id="clear-inventory-filter" class="btn btn-secondary" style="display:none; margin-left:12px; font-size:14px;">Show All</button></h1>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="sortable" data-sort-field="id">ID <span class="sort-indicator">⇅</span></th>
                                <th class="sortable" data-sort-field="name">Product Name <span class="sort-indicator">⇅</span></th>
                                <th class="sortable" data-sort-field="stock_quantity">Current Stock <span class="sort-indicator">⇅</span></th>
                                <th class="sortable" data-sort-field="unit">Unit <span class="sort-indicator">⇅</span></th>
                                <th class="sortable" data-sort-field="status">Status <span class="sort-indicator">⇅</span></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="inventory-table-body"></tbody>
                    </table>
                </div>
            </div>

            <!-- Sales History Page -->
            <div id="sales" class="page" data-role="admin-only">
                <h1>Sales History</h1>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Discount</th>
                                <th>Final Amount</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sales-table-body"></tbody>
                    </table>
                </div>
            </div>

            <!-- Reports Page -->
            <div id="reports" class="page" data-role="admin-only">
                <h1>Reports & Analytics</h1>
                <div class="reports-section">
                    <div class="report-filters">
                        <label>Start Date:</label>
                        <input type="date" id="start-date" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
                        <label>End Date:</label>
                        <input type="date" id="end-date" value="<?php echo date('Y-m-d'); ?>">
                        <button class="btn btn-primary" id="generate-report-btn">Generate Report</button>
                    </div>
                    <div id="report-results" id="report-results"></div>
                </div>
            </div>
        </main>
    </div>

    <!-- Product Modal -->
    <div id="product-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modal-title">Add Product</h2>
            <form id="product-form">
                <div class="form-group" id="product-id-group" style="display: none;">
                    <label>Product ID</label>
                    <input type="number" id="product-id" readonly style="background: #f0f0f0; cursor: not-allowed;">
                    <small style="color: #64748b; font-size: 12px;">ID cannot be changed (primary key)</small>
                </div>
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" id="product-name" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select id="product-category"></select>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="product-description"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Price *</label>
                        <input type="number" id="product-price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Cost *</label>
                        <input type="number" id="product-cost" step="0.01" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Stock Quantity</label>
                        <input type="number" id="product-stock" value="0">
                    </div>
                    <div class="form-group">
                        <label>Unit</label>
                        <input type="text" id="product-unit" value="piece">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancel-product-btn">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        window.appUserRole = '<?php echo htmlspecialchars($userRole, ENT_QUOTES); ?>';
        window.appUsername = '<?php echo htmlspecialchars($username, ENT_QUOTES); ?>';
    </script>
    <script src="assets/js/app.js"></script>
</body>
</html>

