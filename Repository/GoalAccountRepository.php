<?php
namespace Repository;

use Model\Account;
use Model\GoalAccount;

interface GoalAccountRepository {
    function createGoalAccount(GoalAccount $goalAccount, Account $accountId): array;
    function addAmount(GoalAccount $goalAccount, Account $account): array;
    function deleteGoalAccount(GoalAccount $goalAccount): array;
}

class GoalAccountRepositoryImpl implements GoalAccountRepository {

    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

     public function createGoalAccount(GoalAccount $goalAccount, Account $accountId): array {
       try {
            $accountId = $accountId->getAccountId();

            // get balance from ACCOUNT
            $stmtGoalAcc = oci_parse($this->connection, "SELECT balance FROM ACCOUNT WHERE accountId = :accountId FOR UPDATE");
            oci_bind_by_name($stmtGoalAcc, ':accountId', $accountId);
            oci_execute($stmtGoalAcc, OCI_NO_AUTO_COMMIT);  
            $row = oci_fetch_assoc($stmtGoalAcc);

            if (!$row) { 
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "accountId not found"];
            }
            
            $accountBalance = (float)$row['BALANCE']; // get balance
            $goalAmount = $goalAccount->getGoalAmount();

            $transferAmount = min($accountBalance, $goalAmount);

            //insert balance into goal account
            $title = $goalAccount->getTitle();
            $status = $goalAccount->getStatus();
            $description = $goalAccount->getDescription();

            $goalAccountId = null;

            // insert value into goalAccount
            $stmtInsert = oci_parse($this->connection, "INSERT INTO GOALACCOUNT (title, balance, goalAmount, createdAt, status, description, accountId)
            VALUES (:title, :balance, :goalAmount, SYSDATE, :status, :description, :accountId)
            RETURNING goalAccountId INTO :goalAccountId");

            oci_bind_by_name($stmtInsert, ':title', $title);
            oci_bind_by_name($stmtInsert, ':balance', $transferAmount);
            oci_bind_by_name($stmtInsert, ':goalAmount', $goalAmount);
            oci_bind_by_name($stmtInsert, ':status', $status);
            oci_bind_by_name($stmtInsert, ':description', $description);
            oci_bind_by_name($stmtInsert, ':accountId', $accountId);
            oci_bind_by_name($stmtInsert, ':goalAccountId', $goalAccountId, 32);

            $execTrans = oci_execute($stmtInsert, OCI_NO_AUTO_COMMIT);
            if (!$execTrans) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "Failed to insert into GOALACCOUNT table"];
            }
            
            // deduct balance from to ACCOUNT to GOALACCOUNT
            $stmtDeduct = oci_parse($this->connection, "UPDATE ACCOUNT SET balance = balance - :amount WHERE accountId = :accountId");
            oci_bind_by_name($stmtDeduct, ':amount', $transferAmount);
            oci_bind_by_name($stmtDeduct, ':accountId', $accountId);
            
            $execDeduct = oci_execute($stmtDeduct, OCI_NO_AUTO_COMMIT);
            if (!$execDeduct) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "Failed to deduct balance from ACCOUNT"];
            }

            oci_commit($this->connection);
            
            return [
            "result" => "success",
            "message" => "Goal Account created. Transferred RM" . number_format($transferAmount, 2) . " toward RM" . number_format($goalAmount, 2) . " goal.",
            "goalAccountId" => $goalAccountId
        ];

       } catch (\Throwable $th) {
            oci_rollback($this->connection);
            return ["result" => "fail", "message" => $th->getMessage()];
        }

    }

    public function addAmount(GoalAccount $goalAccount, Account $account): array { 
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
            $accountBalance = (float)$row['BALANCE']; // get balance
            $stmtGoalData = oci_parse($this->connection, "SELECT balance, goalAmount, accountId  FROM GOALACCOUNT WHERE goalAccountId = :goalAccountId FOR UPDATE");
            oci_bind_by_name($stmtGoalData, ':goalAccountId', $goalAccountId);
            oci_execute($stmtGoalData, OCI_NO_AUTO_COMMIT);
            $goalRow = oci_fetch_assoc($stmtGoalData);
            

            if (!$goalRow) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "Goal Account not found"];
            }

            $currentGoalBalance = (float)$goalRow['BALANCE'];
            $goalAmountTarget = (float)$goalRow['GOALAMOUNT'];
            $accountIdGoalAccount = (int)$goalRow['ACCOUNTID'];

            $requestedAmount = $goalAccount->getBalance();
            $remainingToGoal = $goalAmountTarget - $currentGoalBalance;

            // Prevent over-transfer
            if ($remainingToGoal <= 0) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "Goal already fully funded"];
            }
            
            if ($requestedAmount > $remainingToGoal) {
                $requestedAmount = $remainingToGoal; // Cap to max allowed
            }
            
            if ($accountBalance < $requestedAmount) {
                 oci_rollback($this->connection);
                 return ["result" => "fail", "message" => "Insufficient balance, Request balance: RM". $requestedAmount . " Account balance: RM" . $accountBalance];
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
            "goalRemaining" => $goalAmountTarget - ($currentGoalBalance + $requestedAmount)
             ];

    } catch (\Throwable $th) {
        oci_rollback($this->connection);
        return ["result" => "fail", "message" => $th->getMessage()];
    }

    }

    public function deleteGoalAccount(GoalAccount $goalAccount): array {
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
            
        $balanceReturn = (float)$row['BALANCE']; // get balance
        $accountId = (int)$row['ACCOUNTID'];

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

        return ["result" => "success", "message" => "Goal account has been deleted, RM" . number_format($balanceReturn) . " has been returned to " . "accountId:" . $accountId];

    } catch (\Throwable $th) {
        oci_rollback($this->connection);
        return ["result" => "fail", "message" => $th->getMessage()];
    }
    }



}