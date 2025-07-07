<?php

namespace Repository;

interface AdminLoanApprovalRepository
{
    function fetchAllLoan();
    function updateLoanStatus(string $status, int $loanId, int $employeeId);
    function downloadLoanData(int $loanId);
}

class AdminLoanApprovalRepositoryImp implements AdminLoanApprovalRepository
{

    private $connection;
    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    function fetchAllLoan()
    {
        $SqlLoanSelect = "SELECT * FROM ACCOUNT A
                        JOIN USERS u ON a.userId = u.userId
                        JOIN ACCOUNTLOAN c ON A.accountId = c.accountid
                        JOIN LOAN l ON c.loanId = l.loanId
                        ORDER BY 
                        CASE C.STATUS
                            WHEN 'Pending' THEN 1
                            WHEN 'Approve' THEN 2
                            WHEN 'Reject' THEN 3
                        ELSE 4
                        END";

        $stmt = oci_parse($this->connection, $SqlLoanSelect);
        oci_execute($stmt);

        $billTransaction = [];
        while (($row = oci_fetch_assoc($stmt)) !== false) {
            $billTransaction[] = $row;
        }

        return [
            "result" => "success",
            "data" => $billTransaction
        ];

    }
    function updateLoanStatus(string $status, int $loanId, int $employeeId)
    {
        $stmtUpdateStatus = oci_parse(
            $this->connection,
            "UPDATE ACCOUNTLOAN SET status = :status, APROVEBY = :employeeId WHERE ACCOUNTLOANID = :accountloanid"
        );
        oci_bind_by_name($stmtUpdateStatus, ':status', $status);
        oci_bind_by_name($stmtUpdateStatus, ':employeeId', $employeeId);
        oci_bind_by_name($stmtUpdateStatus, ':accountloanid', $loanId);

        if (!oci_execute($stmtUpdateStatus, OCI_NO_AUTO_COMMIT)) {
            oci_rollback($this->connection);
            $e = oci_error($stmtUpdateStatus);
            return ["result" => "fail", "message" => "change status failed failed: " . $e['message']];
        }

        if ($status === "Approve") {
            $stmtFetchLoan = oci_parse(
                $this->connection,
                "SELECT * FROM ACCOUNTLOAN AL JOIN LOAN L ON AL.LOANID = L.LOANID WHERE ACCOUNTLOANID = :loanId"
            );
            oci_bind_by_name($stmtFetchLoan, ':loanId', $loanId);

            if (!oci_execute($stmtFetchLoan)) {
                oci_rollback($this->connection);
                $e = oci_error($stmtFetchLoan);
                return ["result" => "fail", "message" => "Fetch loan info failed: " . $e['message']];
            }

            $loanAmount = 0;
            $accountId = 0;
            $interestrate = 0;

            if ($row = oci_fetch_assoc($stmtFetchLoan)) {
                $loanAmount = $row['AMOUNT'];
                $accountId = $row['ACCOUNTID'];
                $interestrate = $row['INTERESTRATE'];
            } else {
                oci_rollback($this->connection);
                return ["result" => "fail", "message" => "Loan not found"];
            }

            $loanAmount = $loanAmount / (1 + $interestrate / 100);

            $stmtUpdateBalance = oci_parse(
                $this->connection,
                "UPDATE ACCOUNT SET BALANCE = BALANCE + :loanAmount WHERE ACCOUNTID = :accountId"
            );
            oci_bind_by_name($stmtUpdateBalance, ':loanAmount', $loanAmount);
            oci_bind_by_name($stmtUpdateBalance, ':accountId', $accountId);

            if (!oci_execute($stmtUpdateBalance, OCI_NO_AUTO_COMMIT)) {
                oci_rollback($this->connection);
                $e = oci_error($stmtUpdateBalance);
                return ["result" => "fail", "message" => "Update balance failed: " . $e['message']];
            }
        }

        oci_commit($this->connection);

        return [
            "result" => "success",
            "message" => "change Status success",
            "status" => $status,
            "accountLoanId" => $loanId
        ];

    }

function downloadLoanData(int $loanId)
{
    // Assume $this->connection is a valid OCI connection

    $sql = "SELECT * FROM ACCOUNTLOAN WHERE ACCOUNTLOANID = :loanId";
    $stmt = oci_parse($this->connection, $sql);

    // Bind loan ID
    oci_bind_by_name($stmt, ":loanId", $loanId);

    // Execute
    if (!oci_execute($stmt)) {
        $error = oci_error($stmt);
        return [
            "result" => "fail",
            "message" => "Error from fetch data"
        ];
    }

    $row = oci_fetch_assoc($stmt);

    return [
        "result" => "success",
        "data" => $row
    ];
}

}