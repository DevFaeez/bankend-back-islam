<?php

use Config\Database;
use Repository\TransactionRepositoryImp;

require_once __DIR__ . '/../Repository/TransactionRepository.php';
require_once __DIR__ . '/../config/Database.php';


header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$connection = Database::getConnection();
$transactionRepo = new TransactionRepositoryImp($connection);

$action = $_GET['action'] ?? '';

switch($action) {
    case 'fetchTransaction': 
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            $accountId = $_GET['accountId'];
            $result = $transactionRepo->fetchAllTransaction($accountId);
            header('Content-Type: application/json');
            echo json_encode($result);
        }
    break;
    
}