<?php

use Config\Database;
use Model\Account;
use Model\AccountLoan;
use Model\Loan;
use Model\Transaction;
use Repository\LoanRepositoryImpl;

require_once __DIR__ . '/../Repository/LoanRepository.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/Account.php';
require_once __DIR__ . '/../model/Loan.php';
require_once __DIR__ . '/../model/AccountLoan.php';
require_once __DIR__ . '/../model/Transaction.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$connection = Database::getConnection();
$loanRepo = new LoanRepositoryImpl($connection);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'fetchLoan':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $result = $loanRepo->fetchLoan();
            header('Content-Type: application/json');
            echo json_encode($result);
        }
        break;
    case 'submitLoan':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data = json_decode(file_get_contents("php://input"), true);

            $account = new Account();
            $loan = new AccountLoan();
            $selectedLoan = new Loan();

            $account->setAccountId(accountId: (int) $data['accountId']);

            $loan->setIcSlip($data['icSlip']);
            $loan->setPurpose($data['loanPurpose']);
            $loan->setPaySlip($data['paySlip']);
            $loan->setAmount($data['loanAmount']);
            $loan->setTerm($data['loanTerm']);
            $loan->setPaymentMethod($data['paymentMethod']);

            $selectedLoan->setLoanId($data['loanId']);

            $result = $loanRepo->SubmitNewLoan($account, $loan, $selectedLoan);
            header('Content-Type: application/json');
            echo json_encode($result);
        }
        break;
    case 'fetchMyLoan':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $accountId = $_GET['accountId'];
            $result = $loanRepo->fetchMyLoan($accountId);
            header('Content-Type: application/json');
            echo json_encode($result);
        }
        break;
    case 'payMyLoan':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);

            $account = new Account();
            $loan = new AccountLoan();
            $transaction = new Transaction();

            $account->setAccountId(accountId: (int) $data['accountId']);

            $loan->setAccountLoanId($data["accountLoanId"]);
            $loan->setBalance($data["payAmount"]);

            $transaction->setDescription($data["description"]);

            $result = $loanRepo->payLoan($account, $loan, $transaction);
            header('Content-Type: application/json');
            echo json_encode($result);
        }
        break;
}