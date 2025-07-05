<?php
namespace Repository;

use Model\Account;
use Model\Transaction;
use Model\TransferTransaction;

interface TransferRepository
{
    function transfer(Account $senderAccount, Account $receiverAccount, Transaction $transaction, TransferTransaction $transferTrasaction): array;
    function fetchAllTransfer(): array;
}

class TransferRepositoryImpl implements TransferRepository
{

    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function transfer(Account $senderAccount, Account $receiverAccount, Transaction $transaction, TransferTransaction $transferTrasaction): array
    {
        try {
            // Step 1: Start transaction
            $senderAccountId = $senderAccount->getAccountId();
            $senderBalance = $senderAccount->getBalance();

            // Step 2: Check sender's balance
            if ($transaction->getAmount() > $senderBalance) {
                return [
                    "result" => "failed",
                    "message" => "account balance not enough"
                ];
            }

            //get the receiverId and check if user exist
            $receiverAccountNumber = $receiverAccount->getAccountNumber();
            $sqlSelectReceiverAccount = "SELECT accountId FROM ACCOUNT WHERE accountNumber = :accountNumber";
            $stmtSqlSelectReceiverAccount = oci_parse($this->connection, $sqlSelectReceiverAccount);
            oci_bind_by_name($stmtSqlSelectReceiverAccount, ':accountNumber', $receiverAccountNumber);

            oci_execute($stmtSqlSelectReceiverAccount);
            $receiverAccountFetch = oci_fetch_assoc($stmtSqlSelectReceiverAccount);

            if ($receiverAccountFetch === false) {
                return [
                    "result" => "fail",
                    "message" => "Account number not exist"
                ];
            }
            $receiverAccountId = $receiverAccountFetch["ACCOUNTID"];


            //Deduct from sender
            $transferAmount = $transaction->getAmount();
            $stmtDeduct = oci_parse(
                $this->connection,
                "UPDATE ACCOUNT SET balance = balance - :amount WHERE accountId = :accountId"
            );
            oci_bind_by_name($stmtDeduct, ':amount', $transferAmount);
            oci_bind_by_name($stmtDeduct, ':accountId', $senderAccountId);

            if (!oci_execute($stmtDeduct, OCI_NO_AUTO_COMMIT)) {
                oci_rollback($this->connection);
                $e = oci_error($stmtDeduct);
                return ["result" => "fail", "message" => "Deduct balance failed: " . $e['message']];
            }

            // Add to receiver
            $stmtAdd = oci_parse(
                $this->connection,
                "UPDATE ACCOUNT SET balance = balance + :amount WHERE accountId = :accountIdReceiver"
            );
            oci_bind_by_name($stmtAdd, ':amount', $transferAmount);
            oci_bind_by_name($stmtAdd, ':accountIdReceiver', $receiverAccountId);

            if (!oci_execute($stmtAdd, OCI_NO_AUTO_COMMIT)) {
                oci_rollback($this->connection);
                $e = oci_error($stmtAdd);
                return ["result" => "fail", "message" => "Add balce to receiver account failed: " . $e['message']];
            }

            // Step 5: (Optional) Log transaction in TRANSACTION table
            $type = "transfer";
            $description = $transaction->getDescription();
            $referenceNumber = rand(1000000000000000, max: 9999999999999999);

            $stmtTrans = oci_parse(
                $this->connection,
                "INSERT INTO TRANSACTION (type, amount, description, referenceNumber, accountId)
                VALUES ( :type, :amount, :description, :referenceNumber, :accountId)
                RETURNING transactionId INTO :transactionId"
            );
            oci_bind_by_name($stmtTrans, ':type', $type);
            oci_bind_by_name($stmtTrans, ':amount', $transferAmount);
            oci_bind_by_name($stmtTrans, ':description', $description);
            oci_bind_by_name($stmtTrans, ':referenceNumber', $referenceNumber);
            oci_bind_by_name($stmtTrans, ':accountId', $senderAccountId);

            $transactionId = null;
            oci_bind_by_name($stmtTrans, ':transactionId', $transactionId, 32);

            if (!oci_execute($stmtTrans, OCI_NO_AUTO_COMMIT)) {
                oci_rollback($this->connection);
                $e = oci_error($stmtTrans);
                return ["result" => "fail", "message" => "Insert transaction failed: " . $e['message']];
            }

            //add child transaction
            $transferMode = $transferTrasaction->getTransferMode();
            $transferType = $transferTrasaction->getTransferType();
            $sqlTransferTransaction = "INSERT INTO TRANSFERTRANSACTION(TRANSACTIONID, TRANSFERMODE, TRANSFERTYPE, RECEIVERACCOUNT)
                                    VALUES(:transactionId, :transfermode, :transfertype, :accountIdReceiver)";
            $stmtSqlTransferTransaction = oci_parse($this->connection, $sqlTransferTransaction);
            oci_bind_by_name($stmtSqlTransferTransaction, ':transactionId', $transactionId);
            oci_bind_by_name($stmtSqlTransferTransaction, ':transfermode', $transferMode);
            oci_bind_by_name($stmtSqlTransferTransaction, ':transfertype', $transferType);
            oci_bind_by_name($stmtSqlTransferTransaction, ':accountIdReceiver', $receiverAccountId);

            if (!oci_execute($stmtSqlTransferTransaction, OCI_NO_AUTO_COMMIT)) {
                oci_rollback($this->connection);
                $e = oci_error($stmtSqlTransferTransaction);
                return ["result" => "fail", "message" => "Insert transfer transaction failed: " . $e['message']];
            }
            oci_commit($this->connection);

            return [
                "result" => "success",
                "message" => "transfer payment successfully.",
            ];

        } catch (\Throwable $th) {
            oci_rollback($this->connection);
            return ["result" => "fail at catch", "message" => $th->getMessage()];
        }
    }

    public function fetchAllTransfer(): array {
        try {
            $sql = "SELECT *
                    FROM TRANSACTION T
                    JOIN TRANSFERTRANSACTION S ON T.transactionId = S.transactionId
                    JOIN ACCOUNT A ON S.receiverAccount = A.accountId";

            $stmt = oci_parse($this->connection, $sql); 
            oci_execute($stmt);

            $transfTransaction = [];
            while (($row = oci_fetch_assoc($stmt)) !== false) {
                $transfTransaction[] = $row;
            }

            return [
                "result" => "success",
                "data" => $transfTransaction
            ];

        } catch (\Throwable $th) {
            return [
                "result" => "fail",
                "message" => $th->getMessage()
            ];
        }
    }
}