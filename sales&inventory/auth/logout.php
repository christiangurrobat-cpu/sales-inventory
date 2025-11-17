<?php
require_once '../config/auth.php';
logout_user();
header('Location: ../login.php?success=' . urlencode('You have been logged out.'));
exit;

