<?php
namespace Repository;

use Model\Account;
use Model\Transaction;

interface TransferRepository {
    function transfer(Account $senderAccount, string $receiverAccountNumber, float $amount, Transaction $transaction): array;
}

class TransferRepositoryImpl implements TransferRepository {

    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

     public function transfer(Account $senderAccount, string $receiverAccountNumber, float $amount, Transaction $transaction): array {
        try {
            // Step 1: Start transaction
            $senderAccountId = $senderAccount->getAccountId();

            // Step 2: Check sender's balance
           // Step 2: Check sender's balance
            $stmtBalance = oci_parse($this->connection, "SELECT balance FROM ACCOUNT WHERE accountId = :accountId FOR UPDATE");
            oci_bind_by_name($stmtBalance, ':accountId', $senderAccountId);
            oci_execute($stmtBalance, OCI_NO_AUTO_COMMIT); // Add this flag here
            $row = oci_fetch_assoc($stmtBalance);

            if (!$row) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "Sender account not found"];
            }

            if ($row['BALANCE'] < $amount) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "Insufficient balance"];
            }

            // Step 3: Deduct from sender
            $stmtDeduct = oci_parse($this->connection,
                "UPDATE ACCOUNT SET balance = balance - :amount WHERE accountId = :accountId"
            );
            oci_bind_by_name($stmtDeduct, ':amount', $amount);
            oci_bind_by_name($stmtDeduct, ':accountId', $senderAccountId);
            oci_execute($stmtDeduct, OCI_NO_AUTO_COMMIT);

            // Step 4: Add to receiver
            $stmtAdd = oci_parse($this->connection,
                "UPDATE ACCOUNT SET balance = balance + :amount WHERE accountNumber = :accountNumber"
            );
            oci_bind_by_name($stmtAdd, ':amount', $amount);
            oci_bind_by_name($stmtAdd, ':accountNumber', $receiverAccountNumber);
            $execAdd = oci_execute($stmtAdd, OCI_NO_AUTO_COMMIT);

            if (!$execAdd || oci_num_rows($stmtAdd) == 0) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "Receiver account not found"];
            }

            // Step 5: (Optional) Log transaction in TRANSACTION table
            // $transactionId = rand(100000, 999999);
            $transactionId = null;
            $transactionDate = date('Ymd'); //format yyyymmdd
            $type = $transaction->getType();
            $description = $transaction->getDescription();
            $tempRef = 'TEMP';


            $stmtTrans = oci_parse($this->connection,
                "INSERT INTO TRANSACTION (type, amount, description, referenceNumber, transactionDate, accountId)
                 VALUES ( :type, :amount, :description, :referenceNumber, SYSDATE, :accountId)
                 RETURNING transactionId INTO :transactionId"
            );
            oci_bind_by_name($stmtTrans, ':type', $type);
            oci_bind_by_name($stmtTrans, ':amount', $amount);
            oci_bind_by_name($stmtTrans, ':description', $description);
            oci_bind_by_name($stmtTrans, ':referenceNumber', $tempRef);
            oci_bind_by_name($stmtTrans, ':accountId', $senderAccountId);
            oci_bind_by_name($stmtTrans, ':transactionId', $transactionId, 32);

            $execTrans = oci_execute($stmtTrans, OCI_NO_AUTO_COMMIT);
            if (!$execTrans) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "Failed to insert into TRANSACTION table"];
            }

            $referenceNumber = "REF-" . $transactionDate . "-" . $transactionId;

            $sqlUpdateRef = "UPDATE TRANSACTION SET referenceNumber = :referenceNumber WHERE transactionId = :transactionId";
            $stmtUpdateRef = oci_parse($this->connection, $sqlUpdateRef);
            oci_bind_by_name($stmtUpdateRef, ':referenceNumber', $referenceNumber);
            oci_bind_by_name($stmtUpdateRef, ':transactionId', $transactionId);
            oci_execute($stmtUpdateRef, OCI_NO_AUTO_COMMIT);

            $stmtTransferTrans = oci_parse($this->connection,
                "INSERT INTO TRANSFERTRANSACTION ( transactionId, reference, transferType, accountId)
                VALUES ( :transactionId, :reference, :transferType, :accountId)"
            );
            oci_bind_by_name($stmtTransferTrans, ':reference', $referenceNumber);
            oci_bind_by_name($stmtTransferTrans, ':transferType', $type);
            oci_bind_by_name($stmtTransferTrans, ':accountId', $senderAccountId);
            oci_bind_by_name($stmtTransferTrans, ':transactionId', $transactionId, 32);
            
            $execTransfer = oci_execute($stmtTransferTrans, OCI_NO_AUTO_COMMIT);
            if (!$execTransfer) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "Failed to insert into TRANSFERTRANSACTION table"];
            }

            // Step 6: Commit
            oci_commit($this->connection);

            return ["result" => "success", "message" => "Transfer successful"];

        } catch (\Throwable $th) {
            oci_rollback($this->connection);
            return ["result" => "fail", "message" => $th->getMessage()];
        }
    }
}