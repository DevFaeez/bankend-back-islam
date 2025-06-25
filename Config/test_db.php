<?php
require_once __DIR__ . '/Database.php';

use Config\Database;

try {
    $conn = Database::getConnection();
    echo "<br>Database connection test: ✅ SUCCESS";
} catch (Exception $e) {
    echo "<br>Database connection test: ❌ FAILED - " . $e->getMessage();
}
