<?php

require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../model/Account.php';
require_once __DIR__ . '/../model/GoalAccount.php';
require_once __DIR__ . '/../Repository/GoalAccountRepository.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

use Config\Database;
use Model\Account;
use Model\GoalAccount;
use Repository\GoalAccountRepositoryImpl;


$connection = Database::getConnection();
$userGoalAccount = new GoalAccountRepositoryImpl($connection);

$action = $_GET['action'] ?? '';

switch($action) {
    case 'goalAccount':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        $accountId = $data['accountId'] ?? null;
        $title = $data['title'] ?? null;
        $goalAmount = $data['goalAmount'] ?? null;
        $balance = $data['balance'] ?? 0;
        $status = $data['status'] ?? 'active';
        $description = $data['description'] ?? null;

        if ($accountId) {
            $accountIdValue = (int)$accountId;

            $account = new Account;
            $account->setAccountId($accountIdValue);

            $goalAccount = new GoalAccount();
            $goalAccount->setTitle($title);
            $goalAccount->setBalance($balance);
            $goalAccount->setGoalAmount($goalAmount);
            $goalAccount->setStatus($status);
            $goalAccount->setDescription($description);

            $result = $userGoalAccount->createGoalAccount($goalAccount, $account);
        } else {
            $result = ["result" => "fail", "message" => "Missing required fields."];
        }

        header('Content-Type: application/json');
        echo json_encode($result);
    }
    break;

      case 'addAmount':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
        $data = json_decode(file_get_contents("php://input"), true);

        $accountId = $data['accountId'] ?? null;
        $goalAccountId = $data['goalAccountId'] ?? null;
        $amount = $data['balance'] ?? null;

        if ($accountId !== null && $goalAccountId !== null && $amount !== null) {
            $account = new Account();
            $account->setAccountId((int)$accountId);

            $goalAccount = new GoalAccount();
            $goalAccount->setGoalAccountId((int)$goalAccountId);
            $goalAccount->setBalance((float)$amount);
        }

        $result = $userGoalAccount->addAmount($goalAccount, $account);
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    break;

      case 'deleteGoalAccount':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
        $data = json_decode(file_get_contents("php://input"), true);

        $goalAccountId = $data['goalAccountId'] ?? null;

        $goalAccount = new GoalAccount();
        $goalAccount->setGoalAccountId((int)$goalAccountId);

        $result = $userGoalAccount->deleteGoalAccount($goalAccount);
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    break;


    
}