<?php
require_once 'config/auth.php';
require_page_auth();

if (current_user_role() !== 'admin') {
    header('Location: customer.php');
    exit;
}

$userRole = 'admin';
$username = $_SESSION['username'] ?? 'Administrator';

require 'views/app.php';

