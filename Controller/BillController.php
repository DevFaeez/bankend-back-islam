<?php

use Config\Database;
use Model\Account;
use Model\Bill;
use Repository\BillRepositoryImpl;

require_once __DIR__ . '/../Repository/BillRepository.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/Bill.php';
require_once __DIR__ . '/../model/Account.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$connection = Database::getConnection();
$billRepo = new BillRepositoryImpl($connection);

$action = $_GET['action'] ?? '';

switch($action) {
    case 'fetchBill': 
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $result = $billRepo->fetchBillProvider();
            header('Content-Type: application/json');
            echo json_encode($result);
        }
    break;
    case 'billPayment': 
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);

            $account = new Account();
            $bill = new Bill();

            $account->setAccountId($data['accountId']);
            $account->setBalance($data['balance']);

            $bill->setBillAccountNumber($data['balance']);
            $bill->setBillAmount($data['billAmount']);
            $bill->setBillDesc($data['billDesc']);
            $bill->setProviderTypeId($data['providerType']);


            $result = $billRepo->billPayment($bill, $account);
            header('Content-Type: application/json');
            echo json_encode($result);
        }
}