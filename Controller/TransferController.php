<?php

use Model\TransferTransaction;
use Config\Database;
use Model\Account;
use Model\Transaction;
use Repository\TransferRepositoryImpl;

require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../model/Account.php';
require_once __DIR__ . '/../model/Transaction.php';
require_once __DIR__ . '/../model/TransferTransaction.php';
require_once __DIR__ . '/../Repository/TransferRepository.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$connection = Database::getConnection();
$userTransfer = new TransferRepositoryImpl($connection);

$action = $_GET['action'] ?? '';

switch($action) {
    case 'transfer':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        $senderAccount = new Account();
        $receiverAccount = new Account();
        $transaction = new Transaction();
        $transferTransaction = new TransferTransaction();

        $senderAccount->setAccountId($data['senderAccountId'] ?? null);
        $senderAccount->setBalance($data['balance'] ?? null);

        $receiverAccount->setAccountNumber($data['receiverAccountNumber'] ?? null);

        $transaction->setAmount($data['amount'] ?? null);
        $transaction->setDescription($data['description'] ?? 'Transfer to another account');

        $transferTransaction->setTransferMode($data['transferMode'] ?? null);
        $transferTransaction->setTransferType($data['transferType'] ?? null);

        $result = $userTransfer->transfer($senderAccount, $receiverAccount, $transaction, $transferTransaction);
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    break;

    case 'fetchAllTransfer':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $result = $userTransfer->fetchAllTransfer();

            header('Content-Type: application/json');
            echo json_encode($result);
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode(["result" => "fail", "message" => "Method not allowed"]);
        }
        break;
}   