<?php

require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/Account.php';
require_once __DIR__ . '/../Repository/TransactionRepository.php';
require_once __DIR__ . '/../Config/Database.php';

use Repository\TransactionRepositoryImpl;
use Config\Database;

// Set CORS headers
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Setup repository
$connection = Database::getConnection();
$transactionRepo = new TransactionRepositoryImpl($connection);

// Determine action
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'fetchAllTrans':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $result = $transactionRepo->fetchAllTrans();

            header('Content-Type: application/json');
            echo json_encode($result);
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode(["result" => "fail", "message" => "Method not allowed"]);
        }
        break;

    default:
        http_response_code(400); // Bad Request
        header('Content-Type: application/json');
        echo json_encode(["result" => "fail", "message" => "Invalid action"]);
        break;
}
