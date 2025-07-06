<?php
namespace Repository;

use Model\Account;
use Model\Bill;

interface BillRepository
{
    function fetchBillProvider();
    function billPayment(Bill $bill, Account $account);

    function fetchAllBillTrans();
}

class BillRepositoryImpl implements BillRepository
{
    private $connection;
    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    function fetchBillProvider()
    {
        try {
            $sqlBill = "SELECT b.billtypeid, b.name As billname, p.providertypeid, p.name AS providername FROM BILLTYPE b JOIN PROVIDERTYPE p ON b.billTypeId = p.billTypeId";
            $sqlBillCheck = oci_parse($this->connection, $sqlBill);
            oci_execute($sqlBillCheck);

            $results = [];

            while ($row = oci_fetch_assoc($sqlBillCheck)) {
                $results[] = $row;
            }

            if (!empty($results)) {
                return [
                    "result" => "success",
                    "data" => $results
                ];
            } else {
                return [
                    "result" => "fail",
                    "message" => "No found"
                ];
            }
        } catch (\Throwable $th) {
            return [
                "result" => "error",
                "message" => $th->getMessage()
            ];
        }
    }

    function fetchAllBillTrans(): array { 
         try {
            $sql = "SELECT *
                    FROM TRANSACTION t
                    JOIN BILLTRANSACTION b ON t.transactionId = b.transactionId
                    JOIN BILL i ON b.billId = i.billId
                    JOIN PROVIDERTYPE p ON i.providertypeid =  p.providertypeid
                    JOIN BILLTYPE q ON p.billtypeid = q.billtypeid";

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

    function billPayment(Bill $bill, Account $account)
    {
        try {

            $accountId = $account->getAccountId();
            $balance = $account->getBalance();

            //check if balance account enough
            if ($bill->getBillAmount() > $balance) {
                return [
                    "result" => "failed",
                    "message" => "account balance not enough"
                ];
            }

            //insert bill into bill and get the id

            $accountNumber = $bill->getBillAccountNumber();
            $billAmount = $bill->getBillAmount();
            $billDesc = $bill->getBillDesc();
            $providerTypeId = $bill->getProviderTypeId();

            $sqlInsertBill = "INSERT INTO BILL(billAccountNumber, accountId, providerTypeId) 
                            VALUES (:billAccountNumber, :accountId, :providerTypeId) 
                            RETURNING billId INTO :billId";

            $stmtSqlInsertBill = oci_parse($this->connection, $sqlInsertBill);
            oci_bind_by_name($stmtSqlInsertBill, ':billAccountNumber', $accountNumber);
            oci_bind_by_name($stmtSqlInsertBill, ':accountId', $accountId);
            oci_bind_by_name($stmtSqlInsertBill, ':providerTypeId', $providerTypeId);

            //return value
            $billId = null;
            oci_bind_by_name($stmtSqlInsertBill, ':billId', $billId, 32);


            if (!oci_execute($stmtSqlInsertBill, OCI_NO_AUTO_COMMIT)) {
                oci_rollback($this->connection);
                $e = oci_error($stmtSqlInsertBill);
                return ["result" => "fail", "message" => "Insert bill failed: " . $e['message']];
            }

            //deduct the balace
            $sqlUpdateBalance = "UPDATE ACCOUNT
                                SET balance = balance - :billAmount
                                WHERE accountID = :accountId";

            $stmtSqlUpdateBalance = oci_parse($this->connection, $sqlUpdateBalance);
            oci_bind_by_name($stmtSqlUpdateBalance, ':billAmount', $billAmount);
            oci_bind_by_name($stmtSqlUpdateBalance, ':accountId', $accountId);

            if (!oci_execute($stmtSqlUpdateBalance, OCI_NO_AUTO_COMMIT)) {
                oci_rollback($this->connection);
                $e = oci_error($stmtSqlUpdateBalance);
                return ["result" => "fail", "message" => "update balance failed: " . $e['message']];
            }

            //insert transaction and child
            $referenceNumber = rand(1000000000000000, max: 9999999999999999);
            $transactionType = "BillPayment";
            $sqlTransaction = "INSERT INTO TRANSACTION(TYPE, AMOUNT, DESCRIPTION, REFERENCENUMBER, ACCOUNTID)
                                VALUES(:type, :amount, :description, :referencenumber, :accountid)
                                RETURNING transactionId INTO :transactionId";
            
            $stmtSqlTransaction = oci_parse($this->connection, $sqlTransaction);
            oci_bind_by_name($stmtSqlTransaction, ':type', $transactionType);
            oci_bind_by_name($stmtSqlTransaction, ':amount', $billAmount);
            oci_bind_by_name($stmtSqlTransaction, ':description', $billDesc);
            oci_bind_by_name($stmtSqlTransaction, ':referencenumber', $referenceNumber);
            oci_bind_by_name($stmtSqlTransaction, ':accountid', $accountId);

            //return value
            $transactionId = null;
            oci_bind_by_name($stmtSqlTransaction, ':transactionId', $transactionId, 32);

            if (!oci_execute($stmtSqlTransaction, OCI_NO_AUTO_COMMIT)) {
                oci_rollback($this->connection);
                $e = oci_error($stmtSqlTransaction);
                return ["result" => "fail", "message" => "Insert transaction failed: " . $e['message']];
            }

        $sqlBillTransaction = "INSERT INTO BILLTRANSACTION(TRANSACTIONID, BILLID)
                                VALUES(:transactionId, :billId)";
        $stmtSqlBillTransaction = oci_parse($this->connection, $sqlBillTransaction);
        oci_bind_by_name($stmtSqlBillTransaction, ':transactionId', $transactionId);
        oci_bind_by_name($stmtSqlBillTransaction, ':billId', $billId);
            
        if (!oci_execute($stmtSqlBillTransaction, OCI_NO_AUTO_COMMIT)) {
            oci_rollback($this->connection);
            $e = oci_error($stmtSqlBillTransaction);
            return ["result" => "fail", "message" => "Insert bill transaction failed: " . $e['message']];
        }   
        oci_commit($this->connection);

        return [
            "result" => "success",
            "message" => "bill payment successfully.",
        ];


        } catch (\Throwable $th) {
            oci_rollback($this->connection);
            return ["result" => "fail at catch", "message" => $th->getMessage()];
        }
    }

}
