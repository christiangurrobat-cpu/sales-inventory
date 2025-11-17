<?php
require_once '../config/auth.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: ../login.php?error=' . urlencode('Please enter both username and password.'));
    exit;
}

$conn = getDBConnection();

$query = "SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1";
$stmt = $conn->prepare($query);

if (!$stmt) {
    $err = 'Login prepare() failed: ' . $conn->error . ' -- Query: ' . $query;
    error_log($err);
    @file_put_contents(__DIR__ . '/../logs/login_debug.log', date('[Y-m-d H:i:s] ') . $err . PHP_EOL, FILE_APPEND);
    header('Location: ../login.php?error=' . urlencode('Server error: please make sure the database is set up (users table).'));
    exit;
}

if (!$stmt->bind_param("s", $username)) {
    $err = 'Login bind_param() failed: ' . $stmt->error;
    error_log($err);
    @file_put_contents(__DIR__ . '/../logs/login_debug.log', date('[Y-m-d H:i:s] ') . $err . PHP_EOL, FILE_APPEND);
    header('Location: ../login.php?error=' . urlencode('Server error during login.'));
    exit;
}

if (!$stmt->execute()) {
    $err = 'Login execute() failed: ' . $stmt->error;
    error_log($err);
    @file_put_contents(__DIR__ . '/../logs/login_debug.log', date('[Y-m-d H:i:s] ') . $err . PHP_EOL, FILE_APPEND);
    header('Location: ../login.php?error=' . urlencode('Server error during login.'));
    exit;
}

$user = null;
// Use bind_result/fetch to avoid reliance on mysqlnd/get_result
$stmt->bind_result($id, $db_username, $password_hash, $role);
if ($stmt->fetch()) {
    $user = [
        'id' => $id,
        'username' => $db_username,
        'password_hash' => $password_hash,
        'role' => $role,
    ];
}

$isValid = false;

if ($user) {
    $isValid = password_verify($password, $user['password_hash']);
}

if ($isValid) {
    ensure_session();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $user['role'];
    if ($user['role'] === 'admin') {
        header('Location: ../admin.php');
    } else {
        header('Location: ../customer.php');
    }
} else {
    header('Location: ../login.php?error=' . urlencode('Invalid username or password.'));
}

$stmt->close();
$conn->close();

