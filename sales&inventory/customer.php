<?php
require_once 'config/auth.php';
ensure_session();

// If an admin accidentally hits the customer page, keep them on the admin experience
if (current_user_role() === 'admin') {
    header('Location: admin.php');
    exit;
}

$userRole = 'customer';
$username = $_SESSION['username'] ?? 'Guest Customer';

require 'views/app.php';

