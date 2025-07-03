<?php
namespace Repository;

use DateTime;
use Model\Account;
use Model\GoalAccount;

interface GoalAccountRepository
{
    function createGoalAccount(GoalAccount $goalAccount, Account $account): array;
    function fetchGoalAccount(int $accountId);
    function addAmount(GoalAccount $goalAccount, Account $account): array;
    function deleteGoalAccount(GoalAccount $goalAccount): array;
}

class GoalAccountRepositoryImpl implements GoalAccountRepository
{

    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function createGoalAccount(GoalAccount $goalAccount, Account $account): array
    {
        try {
            $accountId = $account->getAccountId();

            $title = $goalAccount->getTitle();
            $description = $goalAccount->getDescription();
            $goalamount = $goalAccount->getGoalAmount();
            $status = "Active";
            $goalImages = $goalAccount->getGoalImage();
            $inputDate = DateTime::createFromFormat('d/m/Y', $goalAccount->getGoalDate());
            $goalDate = $inputDate->format('d/m/Y');

            $sqlCreateGoalAccount = "INSERT INTO GOALACCOUNT(TITLE, DESCRIPTION, GOALAMOUNT, STATUS, GOALIMAGES, ACCOUNTID, GOALDATE)
                                VALUES(:title, :description, :goalamount, :status, :goalimages, :accountid, TO_DATE(:goaldate, 'DD/MM/YYYY'))";
            $StmtSqlCreateGoalAccount = oci_parse($this->connection, $sqlCreateGoalAccount);
            oci_bind_by_name($StmtSqlCreateGoalAccount, ':title', $title);
            oci_bind_by_name($StmtSqlCreateGoalAccount, ':description', $description);
            oci_bind_by_name($StmtSqlCreateGoalAccount, ':goalamount', $goalamount);
            oci_bind_by_name($StmtSqlCreateGoalAccount, ':status', $status);
            oci_bind_by_name($StmtSqlCreateGoalAccount, ':goalimages', $goalImages);
            oci_bind_by_name($StmtSqlCreateGoalAccount, ':accountid', $accountId);
            oci_bind_by_name($StmtSqlCreateGoalAccount, ':goaldate', $goalDate);

            if (!oci_execute($StmtSqlCreateGoalAccount, OCI_NO_AUTO_COMMIT)) {
                oci_rollback($this->connection);
                $e = oci_error($StmtSqlCreateGoalAccount);
                return ["result" => "fail", "message" => "create goal account failed: " . $e['message']];
            }

            oci_commit($this->connection);

            return [
                "result" => "success",
                "message" => "Create goal account successfully.",
            ];

        } catch (\Throwable $th) {
            oci_rollback($this->connection);
            return ["result" => "fail", "message" => $th->getMessage()];
        }

    }

    function fetchGoalAccount(int $accountId)
    {
        try {
            $sql = "SELECT goalaccountid, title, balance, goalamount, description, goaldate, goalimages
                FROM GOALACCOUNT
                WHERE accountId = :accountId";

            $stmt = oci_parse($this->connection, $sql);
            oci_bind_by_name($stmt, ':accountId', $accountId);
            oci_execute($stmt);

            $result = [];

            while ($row = oci_fetch_assoc($stmt)) {
                $result[] = array_change_key_case($row, CASE_LOWER);
            }

            if (!empty($result)) {
                return [
                    "result" => "success",
                    "data" => $result
                ];
            } else {
                return [
                    "result" => "success",
                    "message" => $result
                ];
            }

        } catch (\Throwable $th) {
            return [
                "result" => "error",
                "message" => $th->getMessage()
            ];
        }
    }


    public function addAmount(GoalAccount $goalAccount, Account $account): array
    {
        try {

            $accountId = $account->getAccountId();
            $goalAccountId = $goalAccount->getGoalAccountId();
            // $transferAmount = $goalAccount->getBalance();

            // get balance from ACCOUNT
            $stmtCheck = oci_parse($this->connection, "SELECT balance FROM ACCOUNT WHERE accountId = :accountId FOR UPDATE");
            oci_bind_by_name($stmtCheck, ':accountId', $accountId);
            oci_execute($stmtCheck, OCI_NO_AUTO_COMMIT);
            $row = oci_fetch_assoc($stmtCheck);



            if (!$row) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "Account not found"];
            }

            // get GOALACCOUNT current balance
            $accountBalance = (float) $row['BALANCE']; // get balance
            $stmtGoalData = oci_parse($this->connection, "SELECT balance, goalAmount, accountId  FROM GOALACCOUNT WHERE goalAccountId = :goalAccountId FOR UPDATE");
            oci_bind_by_name($stmtGoalData, ':goalAccountId', $goalAccountId);
            oci_execute($stmtGoalData, OCI_NO_AUTO_COMMIT);
            $goalRow = oci_fetch_assoc($stmtGoalData);


            if (!$goalRow) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "Goal Account not found"];
            }

            $currentGoalBalance = (float) $goalRow['BALANCE'];
            $goalAmountTarget = (float) $goalRow['GOALAMOUNT'];
            $accountIdGoalAccount = (int) $goalRow['ACCOUNTID'];

            $requestedAmount = $goalAccount->getBalance();
            $remainingToGoal = $goalAmountTarget - $currentGoalBalance;

            // Prevent over-transfer
            if ($remainingToGoal <= 0) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "full"];
            }

            if ($requestedAmount > $remainingToGoal) {
                $requestedAmount = $remainingToGoal; // Cap to max allowed
            }

            if ($accountBalance < $requestedAmount) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "insuffientBalance"];
            }

            // match FK accountId
            if ($accountIdGoalAccount != $accountId) {
                oci_rollback($this->connection);
                return [
                    "result" => "fail",
                    "message" => "Failed to deduct, accountId mismatch",
                    "accountId" => "Expected accountId:" . $accountIdGoalAccount . " received accountId:" . $accountId
                ];
            }


            // deduct balance from to ACCOUNT to GOALACCOUNT
            $stmtDeduct = oci_parse($this->connection, "UPDATE ACCOUNT SET balance = balance - :amount WHERE accountId = :accountId");
            oci_bind_by_name($stmtDeduct, ':amount', $requestedAmount);
            oci_bind_by_name($stmtDeduct, ':accountId', $accountId);

            $execDeduct = oci_execute($stmtDeduct, OCI_NO_AUTO_COMMIT);
            if (!$execDeduct) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "Failed to deduct balance from ACCOUNT"];
            }

            //add amount back to GOALACCOUNT
            $stmtAdd = oci_parse($this->connection, "UPDATE GOALACCOUNT SET balance = balance + :amount WHERE goalAccountId = :goalAccountId");
            oci_bind_by_name($stmtAdd, ':amount', $requestedAmount);
            oci_bind_by_name($stmtAdd, ':goalAccountId', $goalAccountId);

            $execAdd = oci_execute($stmtAdd, OCI_NO_AUTO_COMMIT);
            if (!$execAdd) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "Failed to add balance to GOALACCOUNT"];
            }

            oci_commit($this->connection);

            return [
                "result" => "success",
                "message" => "Transferred RM" . number_format($requestedAmount, 2) . " to goal account",
                "balance" => $currentGoalBalance + $requestedAmount,
                "goalAccountId" => $goalAccountId
            ];

        } catch (\Throwable $th) {
            oci_rollback($this->connection);
            return ["result" => "fail", "message" => $th->getMessage()];
        }

    }

    public function deleteGoalAccount(GoalAccount $goalAccount): array
    {
        try {

            $goalAccountId = $goalAccount->getGoalAccountId();

            // get balance from GOALACCOUNT
            $stmtGoalAcc = oci_parse($this->connection, "SELECT balance, accountId FROM GOALACCOUNT WHERE goalAccountId = :goalAccountId FOR UPDATE");
            oci_bind_by_name($stmtGoalAcc, ':goalAccountId', $goalAccountId);
            oci_execute($stmtGoalAcc, OCI_NO_AUTO_COMMIT);
            $row = oci_fetch_assoc($stmtGoalAcc);

            if (!$row) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "goalAccountId not found"];
            }

            $balanceReturn = (float) $row['BALANCE']; // get balance
            $accountId = (int) $row['ACCOUNTID'];

            // delete GOALACCOUNT
            $stmtDeleteGoalAcc = oci_parse($this->connection, "DELETE FROM GOALACCOUNT WHERE goalAccountId = :goalAccountId");
            oci_bind_by_name($stmtDeleteGoalAcc, ':goalAccountId', $goalAccountId);
            oci_execute($stmtDeleteGoalAcc, OCI_NO_AUTO_COMMIT);

            if (oci_num_rows($stmtDeleteGoalAcc) === 0) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "goalAccountId not found"];
            }

            //add balance back to ACCOUNT
            $stmtAdd = oci_parse($this->connection, "UPDATE ACCOUNT SET balance = balance + :amount WHERE accountId = :accountId");
            oci_bind_by_name($stmtAdd, ':amount', $balanceReturn);
            oci_bind_by_name($stmtAdd, ':accountId', $accountId);

            $execAdd = oci_execute($stmtAdd, OCI_NO_AUTO_COMMIT);
            if (!$execAdd) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "Failed to add balance to ACCOUNT"];
            }

            oci_commit($this->connection);

            return ["result" => "success", "message" => "Goal account has been deleted", "goalAccountId" => $goalAccountId];

        } catch (\Throwable $th) {
            oci_rollback($this->connection);
            return ["result" => "fail", "message" => $th->getMessage()];
        }
    }



}