<?php

use Config\Database;
use Repository\AdminLoanApprovalRepositoryImp;

require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/Account.php';
require_once __DIR__ . '/../Repository/AdminLoanApprovalRepository.php';
require_once __DIR__ . '/../Config/Database.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$connection = Database::getConnection();
$adminLoan = new AdminLoanApprovalRepositoryImp($connection);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'fetchAllLoan': 
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $result = $adminLoan->fetchAllLoan();
            header('Content-Type: application/json');
            echo json_encode($result);
        }
        break;
        case 'updateLoanStatus': 
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $status = $_GET['status'];
                $employeeId = $_GET['employeeId'];
                $loanId = $_GET['loanId'];
                $result = $adminLoan->updateLoanStatus($status, $loanId, $employeeId);
                header('Content-Type: application/json');
                echo json_encode($result);
            }
        break;
        case 'downloadLoanData': 
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $loanId = $_GET['loanId'];
                $result = $adminLoan->downloadLoanData($loanId);
                header('Content-Type: application/json');
                echo json_encode($result);
            }
        break;
    
}