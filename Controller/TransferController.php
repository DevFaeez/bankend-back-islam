<?php

require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../model/Account.php';
require_once __DIR__ . '/../model/Transaction.php';
require_once __DIR__ . '/../Repository/TransferRepository.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

use Config\Database;
use Model\Account;
use Model\Transaction;
use Repository\TransferRepositoryImpl;


$connection = Database::getConnection();
$userTransfer = new TransferRepositoryImpl($connection);

$action = $_GET['action'] ?? '';

switch($action) {
    case 'transfer':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        $senderAccountId = $data['senderAccountId'] ?? null;
        $receiverAccountNumber = $data['receiverAccountNumber'] ?? null;
        $type = $data['type'] ?? 'transfer'; 
        $amount = $data['amount'] ?? null;
        $descriptionText = $data['description'] ?? 'Transfer to another account';

        if ($senderAccountId && $receiverAccountNumber && $amount) {
            $sender = new Account();
            $sender->setAccountId((int)$senderAccountId);

            $transaction = new Transaction();
            $transaction->setDescription($descriptionText);
            $transaction->setType($type);

            $result = $userTransfer->transfer($sender, $receiverAccountNumber, (float)$amount, $transaction);
        } else {
            $result = ["result" => "fail", "message" => "Missing required fields."];
        }

        header('Content-Type: application/json');
        echo json_encode($result);
    }
    break;
}