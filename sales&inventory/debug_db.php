<?php
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $conn = getDBConnection();
} catch (Throwable $e) {
    echo "Connection exception: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

if ($conn->connect_error) {
    echo "Connect error: " . $conn->connect_error . PHP_EOL;
    exit(1);
}

echo "Connected to MySQL successfully\n";

// List databases
$databases = [];
$res = $conn->query("SHOW DATABASES");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $databases[] = $row['Database'];
    }
}
echo "Databases: " . implode(', ', $databases) . "\n";

// Check target DB
$dbName = 'fastfood_inventory';
$res = $conn->query("SHOW TABLES FROM `" . $conn->real_escape_string($dbName) . "`");
if ($res) {
    $tables = [];
    while ($row = $res->fetch_row()) {
        $tables[] = $row[0];
    }
    echo "Tables in $dbName: " . implode(', ', $tables) . "\n";
} else {
    echo "Could not list tables in $dbName: " . $conn->error . "\n";
}

// If users table exists, show count
$res = $conn->query("SELECT COUNT(*) AS cnt FROM `" . $conn->real_escape_string($dbName) . "`.users");
if ($res) {
    $r = $res->fetch_assoc();
    echo "Users table count: " . ($r['cnt'] ?? '0') . "\n";
} else {
    echo "Users table check failed: " . $conn->error . "\n";
}

$conn->close();

echo "Done.\n";
