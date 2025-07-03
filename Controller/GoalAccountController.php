<?php

require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../model/Account.php';
require_once __DIR__ . '/../model/GoalAccount.php';
require_once __DIR__ . '/../Repository/GoalAccountRepository.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PATCH, DELETE");

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

switch ($action) {
    case 'createGoalAccount':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);

            $accountId = $data['accountId'] ?? null;

            $title = $data['title'] ?? null;
            $goalAmount = $data['goalAmount'] ?? null;
            $description = $data['description'] ?? null;
            $goalImages = $data['goalImages'] ?? null;
            $goalDate = $data['goalDate'] ?? null;

            if ($accountId) {
                $accountIdValue = (int) $accountId;

                $account = new Account;
                $account->setAccountId($accountIdValue);

                $goalAccount = new GoalAccount();
                $goalAccount->setTitle($title);
                $goalAccount->setGoalAmount($goalAmount);
                $goalAccount->setDescription($description);
                $goalAccount->setgoalImage($goalImages);
                $goalAccount->setGoalDate($goalDate);

                $result = $userGoalAccount->createGoalAccount($goalAccount, $account);
            } else {
                $result = ["result" => "fail", "message" => "Missing required fields."];
            }

            header('Content-Type: application/json');
            echo json_encode($result);
        }
        break;

    case 'fetchGoalAccount':
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $accountId = (int) $_GET["id"] ?? '';
            if ($accountId) {
                $result = $userGoalAccount->fetchGoalAccount($accountId);
            } else {
                $result = [
                    "result" => "error",
                    "message" => "Missing account ID"
                ];
            }

            header('Content-Type: application/json');
            echo json_encode($result);

        }

case 'addAmount':
    if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
        $data = json_decode(file_get_contents("php://input"), true);

        $accountId = $data['accountId'] ?? null;
        $goalAccountId = $data['goalAccountId'] ?? null;
        $amount = $data['amount'] ?? null;

        header('Content-Type: application/json');

        if ($accountId !== null && $goalAccountId !== null && $amount !== null) {
            $account = new Account();
            $account->setAccountId((int) $accountId);

            $goalAccount = new GoalAccount();
            $goalAccount->setGoalAccountId((int) $goalAccountId);
            $goalAccount->setBalance((float) $amount);

            $result = $userGoalAccount->addAmount($goalAccount, $account);
            echo json_encode($result);
        } else {
            echo json_encode([
                "result" => "fail",
                "message" => "Missing required fields: accountId, goalAccountId, or amount"
            ]);
        }
<<<<<<< HEAD

        $result = $userGoalAccount->addAmount($goalAccount, $account);
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    break;

      case 'deleteGoalAccount':
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {  
        $data = json_decode(file_get_contents("php://input"), true);

        $goalAccountId = $data['goalAccountId'] ?? null;

        $goalAccount = new GoalAccount();
        $goalAccount->setGoalAccountId((int)$goalAccountId);

        $result = $userGoalAccount->deleteGoalAccount($goalAccount);
        header('Content-Type: application/json');
        echo json_encode($result);
=======
>>>>>>> 666d5e3ce8c7227cad9366c5f115e49f352ca76a
    }
    break;


    case 'deleteGoalAccount':
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $data = json_decode(file_get_contents("php://input"), true);

            $goalAccountId = $_GET['goalAccountId'];

            $goalAccount = new GoalAccount();
            $goalAccount->setGoalAccountId((int) $goalAccountId);

            $result = $userGoalAccount->deleteGoalAccount($goalAccount);
            header('Content-Type: application/json');
            echo json_encode($result);
        }
        break;



}