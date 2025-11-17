<?php
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/plain; charset=utf-8');

$conn = getDBConnection();
if ($conn->connect_error) {
    echo "Connection error: " . $conn->connect_error . PHP_EOL;
    exit(1);
}

$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "Users table ready.\n";
} else {
    echo "Failed to create users table: " . $conn->error . "\n";
    exit(1);
}

// Seed default users (passwords already hashed in schema.sql)
$users = [
    ['admin', '$2y$10$51BgZhwZVAGtfdtKYJHn8ObyPPLFfasv3yKmalf4/J5Sp2lp8OTYG', 'admin'],
    ['customer', '$2y$10$q/vD/3pLUlnGayOl83FBRuH.4tynE/qpIDHEBOyiKHOwj6GWDhvCC', 'customer'],
];

$insert = $conn->prepare("INSERT IGNORE INTO users (username, password_hash, role) VALUES (?, ?, ?)");
if (!$insert) {
    echo "Prepare for insert failed: " . $conn->error . "\n";
    exit(1);
}

foreach ($users as $u) {
    [$un, $ph, $role] = $u;
    $insert->bind_param('sss', $un, $ph, $role);
    if ($insert->execute()) {
        echo "Inserted/ignored user: $un\n";
    } else {
        echo "Failed inserting $un: " . $insert->error . "\n";
    }
}

$insert->close();
$conn->close();

echo "Done.\n";
