<?php
namespace Repository;

use Model\AccountLoan;
use Model\Loan;
use Model\Transaction;
use Model\User;
use Model\Account;

interface LoanRepository
{
    function fetchLoan();

}

class LoanRepositoryImpl implements LoanRepository
{

    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function fetchLoan()
    {
        try {
            $sqlLoan = "SELECT loanid, loantype, interestrate FROM LOAN";
            $stmtSqlSubmitLoanSqlLoan = oci_parse($this->connection, $sqlLoan);

            oci_execute($stmtSqlSubmitLoanSqlLoan);

            $result = [];
            while ($row = oci_fetch_assoc($stmtSqlSubmitLoanSqlLoan)) {
                $result[] = $row;
            }
            return [
                "result" => "success",
                "message" => "Fetch Loan Success",
                "data" => $result
            ];

        } catch (\Throwable $th) {
            oci_rollback($this->connection);
            return ["result" => "fail", "message" => $th->getMessage()];
        }
    }

    public function SubmitNewLoan(Account $account, AccountLoan $loan, Loan $selectedLoan)
    {
        try {

            $icSlip = $loan->getIcSlip();
            $paySlip = $loan->getPaySlip();
            $purpose = $loan->getPurpose();
            $amount = $loan->getAmount();
            $term = $loan->getTerm();
            $paymentMethod = $loan->getPaymentMethod();

            $loanId = $selectedLoan->getLoanId();
            $accountId = $account->getAccountId();

            $balance = 0;
            $status = 'Pending';

            $sqlSubmitLoan = "INSERT INTO ACCOUNTLOAN (
                                ICSLIP, PAYSLIP, PURPOSE, AMOUNT, BALANCE,
                                TERM, PAYMENTMETHOD, LOANID, ACCOUNTID, STATUS
                            ) VALUES (
                            :icslip, :payslip, :purpose, :amount, :balance,
                            :term, :paymentMethod, :loanId, :accountId, :status)";

            $stmtSqlSubmitLoan = oci_parse($this->connection, $sqlSubmitLoan);

            // Bind parameters using extracted variables
            oci_bind_by_name($stmtSqlSubmitLoan, ":icslip", $icSlip);
            oci_bind_by_name($stmtSqlSubmitLoan, ":payslip", $paySlip);
            oci_bind_by_name($stmtSqlSubmitLoan, ":purpose", $purpose);
            oci_bind_by_name($stmtSqlSubmitLoan, ":amount", $amount);
            oci_bind_by_name($stmtSqlSubmitLoan, ":balance", $balance);
            oci_bind_by_name($stmtSqlSubmitLoan, ":term", $term);
            oci_bind_by_name($stmtSqlSubmitLoan, ":paymentMethod", $paymentMethod);
            oci_bind_by_name($stmtSqlSubmitLoan, ":loanId", $loanId);
            oci_bind_by_name($stmtSqlSubmitLoan, ":accountId", $accountId);
            oci_bind_by_name($stmtSqlSubmitLoan, ":status", $status);


            if (!oci_execute($stmtSqlSubmitLoan, OCI_NO_AUTO_COMMIT)) {
                oci_rollback($this->connection);
                $e = oci_error($stmtSqlSubmitLoan);
                return ["result" => "fail", "message" => "submit new loan failed"];
            }

            oci_commit($this->connection);

            return [
                "result" => "success",
                "message" => "Submit Loan Success.",
            ];

        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function fetchMyLoan(int $accountId)
    {
        try {

            $sqlFetchMyLoan = "SELECT * FROM ACCOUNTLOAN WHERE ACCOUNTID = 41 AND STATUS = 'Pending'";
            $StmtSqlFetchMyLoan = oci_parse($this->connection, $sqlFetchMyLoan);
            oci_execute($StmtSqlFetchMyLoan);

            $results = [];

            while ($row = oci_fetch_assoc($StmtSqlFetchMyLoan)) {
                $results[] = $row;
            }


            if (!empty($results)) {
                return [
                    "result" => "success",
                    "data" => $results
                ];
            } else {
                return [
                    "result" => "Succes",
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

    public function payLoan(Account $account, AccountLoan $loan, Transaction $transaction)
    {
        try {
            $accountId = $account->getAccountId();

            // Lock account row
            $stmtCheck = oci_parse($this->connection, "SELECT balance FROM ACCOUNT WHERE accountId = :accountId FOR UPDATE");
            oci_bind_by_name($stmtCheck, ':accountId', $accountId);
            oci_execute($stmtCheck, OCI_NO_AUTO_COMMIT);
            $rowAccount = oci_fetch_assoc($stmtCheck);

            if (!$rowAccount) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "Account not found"];
            }

            $accountBalance = (float) $rowAccount['BALANCE'];
            $accountLoanId = $loan->getAccountLoanId();
            $currentPay = $loan->getBalance();
            $description = $transaction->getDescription();

            if ($currentPay > $accountBalance) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "insuffientBalance"];
            }

            // Deduct from account balance
            $stmtDeduct = oci_parse($this->connection, "UPDATE ACCOUNT SET BALANCE = BALANCE - :amountPay WHERE accountId = :accountId");
            oci_bind_by_name($stmtDeduct, ':amountPay', $currentPay);
            oci_bind_by_name($stmtDeduct, ':accountId', $accountId);

            if (!oci_execute($stmtDeduct, OCI_NO_AUTO_COMMIT)) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "failed deduct balance"];
            }

            // Update loan balance
            $stmtUpdateBalance = oci_parse($this->connection, "UPDATE ACCOUNTLOAN SET BALANCE = BALANCE + :amountPay WHERE ACCOUNTLOANID = :accountloanId");
            oci_bind_by_name($stmtUpdateBalance, ':amountPay', $currentPay);
            oci_bind_by_name($stmtUpdateBalance, ':accountloanId', $accountLoanId);

            if (!oci_execute($stmtUpdateBalance, OCI_NO_AUTO_COMMIT)) {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "failed update loan balance"];
            }

            //add transaction

            $referenceNumber = rand(1000000000000000, max: 9999999999999999);
            $transactionType = "LoanPayment";
            $sqlTransaction = "INSERT INTO TRANSACTION(TYPE, AMOUNT, DESCRIPTION, REFERENCENUMBER, ACCOUNTID)
                                VALUES(:type, :amount, :description, :referencenumber, :accountid)
                                RETURNING transactionId INTO :transactionId";

            $stmtSqlTransaction = oci_parse($this->connection, $sqlTransaction);
            oci_bind_by_name($stmtSqlTransaction, ':type', $transactionType);
            oci_bind_by_name($stmtSqlTransaction, ':amount', $currentPay);
            oci_bind_by_name($stmtSqlTransaction, ':description', $description);
            oci_bind_by_name($stmtSqlTransaction, ':referencenumber', $referenceNumber);
            oci_bind_by_name($stmtSqlTransaction, ':accountid', $accountId);

            $transactionId = null;
            oci_bind_by_name($stmtSqlTransaction, ':transactionId', $transactionId, 32);

            if (!oci_execute($stmtSqlTransaction, OCI_NO_AUTO_COMMIT)) {
                oci_rollback($this->connection);
                $e = oci_error($stmtSqlTransaction);
                return ["result" => "fail", "message" => "Insert transaction failed: " . $e['message']];
            }

            $sqlLoanTransaction = "INSERT INTO LOANPAYMENTTRANSACTION(TRANSACTIONID, LOANID)
                                VALUES(:transactionId, :loanid)";
            $stmtSqlLoanTransaction = oci_parse($this->connection, $sqlLoanTransaction);
            oci_bind_by_name($stmtSqlLoanTransaction, ':transactionId', $transactionId);
            oci_bind_by_name($stmtSqlLoanTransaction, ':loanid', $accountLoanId);

            if (!oci_execute($stmtSqlLoanTransaction, OCI_NO_AUTO_COMMIT)) {
                oci_rollback($this->connection);
                $e = oci_error($stmtSqlLoanTransaction);
                return ["result" => "fail", "message" => "Insert loan transaction failed: " . $e['message']];
            }

            //get final data
            $stmtGetNewBalance = oci_parse($this->connection, "SELECT BALANCE FROM ACCOUNTLOAN WHERE ACCOUNTLOANID = :accountloanId");
            oci_bind_by_name($stmtGetNewBalance, ':accountloanId', $accountLoanId);
            oci_execute($stmtGetNewBalance);
            $rowNewBalance = oci_fetch_assoc($stmtGetNewBalance);
            $newLoanBalance = (float) $rowNewBalance['BALANCE'];

            oci_commit($this->connection);

            return [
                "result" => "success",
                "message" => "Loan payment successful",
                "accountLoanId" => $accountLoanId,
                "currentPay" => $newLoanBalance
            ];

        } catch (\Throwable $th) {
            oci_rollback($this->connection);
            return ["result" => "fail", "message" => $th->getMessage()];
        }
    }

}