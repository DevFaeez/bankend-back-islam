<?php

namespace Repository;

use Repository\BillRepository;

interface TransactionRepository
{
    function fetchAllTransaction(int $accountId);
    function fetchAllTrans();
}

class TransactionRepositoryImp implements TransactionRepository
{
    private $connection;
    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    function fetchAllTrans(): array { 
         try {
            $sql = "SELECT *
                    FROM TRANSACTION";

            $stmt = oci_parse($this->connection, $sql); 
            oci_execute($stmt);

            $billTransaction = [];
            while (($row = oci_fetch_assoc($stmt)) !== false) {
                $billTransaction[] = $row;
            }

            return [
                "result" => "success",
                "data" => $billTransaction
            ];

        } catch (\Throwable $th) {
            return [
                "result" => "fail",
                "message" => $th->getMessage()
            ];
        }
    }

function fetchAllTransaction(int $accountId)
{
    try {
        $results = [];

        // 1. Transfer - sender
        $sqlSenderTransfer = "
            SELECT *
            FROM TRANSACTION t
            JOIN TRANSFERTRANSACTION s ON t.transactionId = s.transactionId
            JOIN ACCOUNT a ON s.receiveraccount = a.accountId 
            WHERE t.accountId = :accountId
            AND t.transactionDate >= ADD_MONTHS(SYSDATE, -1)
        ";
        $stmt1 = oci_parse($this->connection, $sqlSenderTransfer);
        oci_bind_by_name($stmt1, ":accountId", $accountId);
        oci_execute($stmt1);

        $senderTransfers = [];
        while ($row = oci_fetch_assoc($stmt1)) {
            $row['TYPE'] = "transfer sender";
            $results[] = $row;
        }

        
        // 2. Transfer - receiver
        $sqlReceiverTransfer = "
            SELECT *
            FROM TRANSACTION t
            JOIN TRANSFERTRANSACTION s ON t.transactionId = s.transactionId
            JOIN ACCOUNT a ON s.receiveraccount = a.accountId 
            WHERE s.receiveraccount = :accountId
            AND t.transactionDate >= ADD_MONTHS(SYSDATE, -1)
        ";
        $stmt2 = oci_parse($this->connection, $sqlReceiverTransfer);
        oci_bind_by_name($stmt2, ":accountId", $accountId);
        oci_execute($stmt2);

        $receiverTransfers = [];
        while ($row = oci_fetch_assoc($stmt2)) {
            $row['TYPE'] = "transfer receiver";
            $results[] = $row;
        }

        
        // 3. Bill transactions
        $sqlBill = "
            SELECT *
            FROM TRANSACTION t
            JOIN BILLTRANSACTION b ON t.transactionId = b.transactionId
            JOIN BILL i ON b.billId = i.billId
            JOIN PROVIDERTYPE p ON i.providertypeid = p.providertypeid
            JOIN BILLTYPE q ON p.billtypeid = q.billtypeid
            WHERE t.accountId = :accountId
            AND t.transactionDate >= ADD_MONTHS(SYSDATE, -1)
        ";
        $stmt3 = oci_parse($this->connection, $sqlBill);
        oci_bind_by_name($stmt3, ":accountId", $accountId);
        oci_execute($stmt3);

        $billTransactions = [];
        while ($row = oci_fetch_assoc($stmt3)) {
            $results[] = $row;
        }

        
        // 4. loan transaction
        $sqlLoan = "
            SELECT *
            FROM TRANSACTION t
            JOIN LOANPAYMENTTRANSACTION l ON t.transactionId = l.transactionId
            JOIN ACCOUNTLOAN a ON l.loanId = a.accountloanid
            WHERE t.accountId = :accountId
            AND t.transactionDate >= ADD_MONTHS(SYSDATE, -1)
        ";
        $stmt4 = oci_parse($this->connection, $sqlLoan);
        oci_bind_by_name($stmt4, ":accountId", $accountId);
        oci_execute($stmt4);

        $loanTransactions = [];
        while ($row = oci_fetch_assoc($stmt4)) {
            $results[] = $row;
        }

        return [
            "result" => "success",
            "data" => $results
        ];
    } catch (\Throwable $th) {
        return [
            "result" => "error",
            "message" => $th->getMessage()
        ];
    }
}

}

