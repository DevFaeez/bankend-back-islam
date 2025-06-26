<?php

require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Repository/DashboardRepository.php';

use Config\Database;
use Model\Account;
use Repository\DashboardRepositoryImpl;

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$connection = Database::getConnection();
$dashRepo = new DashboardRepositoryImpl($connection);

$action = $_GET['action'] ?? '';

switch($action) {
    case 'fetchDashboard':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $accountId = $_GET['accountId'] ?? null;

            if ($accountId === null) {
                echo json_encode([
                    "result" => "fail",
                    "message" => "Missing accountId"
                ]);
                exit;
            }

            $result = $dashRepo->fetchDashboard($accountId);
            header('Content-Type: application/json');
            echo json_encode($result);
        }
        break;
}