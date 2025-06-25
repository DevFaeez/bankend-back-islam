<?php
namespace Repository;

use Model\User;
use Model\Account;

interface UserRepository {
    function register(User $user, Account $account);
    function login(string $email, string $password): array;
}

class UserRepositoryImpl implements UserRepository {

    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function register(User $user, Account $account) {
    try {
        // Step 1: Check if email already exists in USERS
        $sqlCheck = "SELECT * FROM USERS WHERE email = :email";
        $stmtCheck = oci_parse($this->connection, $sqlCheck);
        $email = $user->getEmail();
        oci_bind_by_name($stmtCheck, ':email', $email);
        oci_execute($stmtCheck);

        if (oci_fetch_assoc($stmtCheck)) {
            return ["result" => "fail", "message" => "Email already registered"];
        }

        // Step 2: Generate userId and accountId
        $userId = rand(1000, 9999);     // Or use sequence if you have one
        $accountId = rand(1000, 9999);
        $accountNumber = rand(1000000000000000, 9999999999999999);
        $employeeId = 3001; // Static for now, or can be parameterized

        // Step 3: Insert into USERS table
        $email = $user->getEmail();
        $nricNumber = $user->getNricNumber();
        $fullName = $user->getFullName();
        $phone = $user->getPhoneNumber();
        // $status = $user->getStatus(); // Not used since 'active' is hardcoded

        $sqlInsertUser = "INSERT INTO USERS (email, nricNumber, fullName, phoneNumber, status)
                        VALUES (:email, :nricNumber, :fullName, :phoneNumber, 'active')";

        $stmtUser = oci_parse($this->connection, $sqlInsertUser);

        // oci_bind_by_name($stmtUser, ':userId', $userId);
        oci_bind_by_name($stmtUser, ':email', $email);
        oci_bind_by_name($stmtUser, ':nricNumber', $nricNumber);
        oci_bind_by_name($stmtUser, ':fullName', $fullName);
        oci_bind_by_name($stmtUser, ':phoneNumber', $phone);

        
        if (!oci_execute($stmtUser, OCI_NO_AUTO_COMMIT)) {
            oci_rollback($this->connection);
            $e = oci_error($stmtUser);
            return ["result" => "fail", "message" => "Insert USERS failed: " . $e['message']];
        }

        // Step 3.5: Get the generated userId
        $seqSql = "SELECT user_seq.CURRVAL AS userId FROM dual";
        $seqStmt = oci_parse($this->connection, $seqSql);
        oci_execute($seqStmt);
        $row = oci_fetch_assoc($seqStmt);
        $userId = $row['USERID']; // Now you have the correct generated userId

        // Step 4: Insert into ACCOUNT table
        $hashedPassword = password_hash($account-> getPassword(), PASSWORD_DEFAULT);

        $sqlInsertAcc = "INSERT INTO ACCOUNT (accountId, accountNumber,password, balance, status, openedAt, userId, employeeId)
                         VALUES (:accountId, :accountNumber, :password, 0.0, 'active', SYSDATE, :userId, :employeeId)";
        $stmtAcc = oci_parse($this->connection, $sqlInsertAcc);
        
        oci_bind_by_name($stmtAcc, ':accountId', $accountId);
        oci_bind_by_name($stmtAcc, ':accountNumber', $accountNumber);
        oci_bind_by_name($stmtAcc, ':password', $hashedPassword);
        oci_bind_by_name($stmtAcc, ':userId', $userId);
        oci_bind_by_name($stmtAcc, ':employeeId', $employeeId);

        if (!oci_execute($stmtAcc, OCI_COMMIT_ON_SUCCESS)) {
            oci_rollback($this->connection);
            $e = oci_error($stmtAcc);
            return ["result" => "fail", "message" => "Insert ACCOUNT failed: " . $e['message']];
        }

        return ["result" => "success", "message" => "User registered successfully."];

    } catch (\Throwable $th) {
        oci_rollback($this->connection);
        return ["result" => "fail", "message" => $th->getMessage()];
    }
}

    public function login(string $email, string $password): array {
    try {
        $sql = "SELECT a.accountid, u.userId, u.email, a.password
                FROM USERS u
                JOIN ACCOUNT a
                ON u.userId = a.userId
                WHERE u.email = :email";
        $stmt = oci_parse($this->connection, $sql);

        oci_bind_by_name($stmt, ':email', $email);

        oci_execute($stmt);

        $user = oci_fetch_assoc($stmt);

        if ($user && password_verify($password, $user['PASSWORD'])) {
            return [
                "result" => "success",
                "user" => [
                    "user_id" => $user['USERID'],
                    "email" => $user['EMAIL'],
                    "accountId" => $user['ACCOUNTID']
                ]
            ];
        } else {
            return ["result" => "fail here", "message" => "Invalid email or password"];
        }
    } catch (\Throwable $th) {
        return ["result" => "fail", "message" => $th->getMessage()];
    }
}
}
