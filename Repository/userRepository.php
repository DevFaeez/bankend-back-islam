<?php
namespace Repository;

use Model\User;
use Model\Account;

interface UserRepository {
    function register(User $user, Account $account);
    function login(string $email, string $password): array;

    function fetchUser(int $accountId);
    function fetchAllUser();

    function updateUserProfile(User $user, int $accountId);

    function updateUserPassword(string $currentPassword, string $newPassword, int $accountId): array;
}

class UserRepositoryImpl implements UserRepository {

    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

public function register(User $user, Account $account) {
    try {
        // Step 1: Check if email already exists
        $sqlCheck = "SELECT * FROM USERS WHERE email = :email";
        $stmtCheck = oci_parse($this->connection, $sqlCheck);
        $email = $user->getEmail();
        oci_bind_by_name($stmtCheck, ':email', $email);
        oci_execute($stmtCheck);

        if (oci_fetch_assoc($stmtCheck)) {
            return ["result" => "fail", "message" => "Email already registered"];
        }

        // Step 2: Generate account number
        $accountNumber = rand(1000000000000000, max: 9999999999999999); // 16-digit number
        $username = $account->getUsername();
        $employeeId = null;

        // Prepare user data
        $fullName = $user->getFullName();
        $nricNumber = $user->getNricNumber();
        $email = $user->getEmail();
        $userId = null;

        // Step 3: Insert into USERS table
        $sqlInsertUser = "INSERT INTO USERS (email, nricNumber, fullName, status)
                        VALUES (:email, :nricNumber, :fullName, 'active') 
                        RETURNING userId INTO :userId";

        $stmtUser = oci_parse($this->connection, $sqlInsertUser);
        oci_bind_by_name($stmtUser, ':email', $email);
        oci_bind_by_name($stmtUser, ':nricNumber', $nricNumber);
        oci_bind_by_name($stmtUser, ':fullName', $fullName);
        oci_bind_by_name($stmtUser, ':userId', $userId, 32); // Output variable

        if (!oci_execute($stmtUser, OCI_NO_AUTO_COMMIT)) {
            oci_rollback($this->connection);
            $e = oci_error($stmtUser);
            return ["result" => "fail", "message" => "Insert USERS failed: " . $e['message']];
        }

        // Step 4: Insert into ACCOUNT table
        $hashedPassword = password_hash($account->getPassword(), PASSWORD_DEFAULT);
        $accountId = null;

        $sqlInsertAcc = "INSERT INTO ACCOUNT (accountNumber, username, password, balance, status, openedAt, userId, employeeId)
                        VALUES (:accountNumber, :username, :password, 0.0, 'active', SYSDATE, :userId, :employeeId)
                        RETURNING accountId INTO :accountId";

        $stmtAcc = oci_parse($this->connection, $sqlInsertAcc);
        oci_bind_by_name($stmtAcc, ':accountNumber', $accountNumber);
        oci_bind_by_name($stmtAcc, ':username', $username);
        oci_bind_by_name($stmtAcc, ':password', $hashedPassword);
        oci_bind_by_name($stmtAcc, ':userId', $userId);
        oci_bind_by_name($stmtAcc, ':employeeId', $employeeId);
        oci_bind_by_name($stmtAcc, ':accountId', $accountId, 32); // Output variable

        if (!oci_execute($stmtAcc, OCI_NO_AUTO_COMMIT)) {
            oci_rollback($this->connection);
            $e = oci_error($stmtAcc);
            return ["result" => "fail", "message" => "Insert ACCOUNT failed: " . $e['message']];
        }   

        oci_commit($this->connection);

        return [
            "result" => "success",
            "message" => "User registered successfully.",
            "data" => [
                "userId" => $userId,
                "accountId" => $accountId,
                "accountNumber" => $accountNumber
            ]
        ];

    } catch (\Throwable $th) {
        oci_rollback($this->connection);
        return ["result" => "fail", "message" => $th->getMessage()];
    }
}

    public function login(string $username, string $password): array {
    try {
        $sql = "SELECT a.accountId, a.password 
                FROM USERS u
                JOIN ACCOUNT a ON u.userId = a.userId
                WHERE a.username = :username";
        
        $stmt = oci_parse($this->connection, $sql);
        oci_bind_by_name($stmt, ':username', $username);
        oci_execute($stmt);

        $user = oci_fetch_assoc($stmt);

        // Step 2: Verify user exists and password is correct
        if ($user && password_verify($password, $user['PASSWORD'])) {
            return [
                "result" => "success",
                "data" => [
                    "accountId" => $user['ACCOUNTID'],
                ]   
            ];
        } else {
            return [
                "result" => "fail",
                "message" => "Invalid username or password"
            ];
        }

    } catch (\Throwable $th) {
        return [
            "result" => "fail",
            "message" => $th->getMessage()
        ];
    }
}

public function fetchUser(int $accountId): array {
    try {
        $sql = "SELECT u.userId, u.email, u.nricNumber, u.fullName, u.phoneNumber, u.address
                FROM ACCOUNT a
                JOIN USERS u ON a.userId = u.userId
                WHERE a.accountId = :accountId";

        $stmt = oci_parse($this->connection, $sql);
        oci_bind_by_name($stmt, ':accountId', $accountId);
        oci_execute($stmt);

        $user = oci_fetch_assoc($stmt);

        if ($user) {
            return [
                "result" => "success",
                "data" => [
                    "fullName" => $user['FULLNAME'],
                    "email" => $user['EMAIL'],
                    "nricNumber" => $user['NRICNUMBER'],
                    "phoneNumber" => $user['PHONENUMBER'],
                    "address" => $user['ADDRESS'],
                ]
            ];
        } else {
            return [
                "result" => "fail",
                "message" => "User not found"
            ];
        }

    } catch (\Throwable $th) {
        return [
            "result" => "fail",
            "message" => $th->getMessage()
        ];
    }
}

public function fetchAllUser(): array {
        try {
            $sql = "SELECT * FROM USERS S JOIN ACCOUNT A ON S.userId = A.userId";
            $stmt = oci_parse($this->connection, $sql);
            oci_execute($stmt);

            $user = [];
            while (($row = oci_fetch_assoc($stmt)) !== false) {
                $user[] = $row;
            }

            return [
                "result" => "success",
                "data" => $user
            ];

        } catch (\Throwable $th) {
            return [
                "result" => "fail",
                "message" => $th->getMessage()
            ];
        }
    }

    public function updateUserProfile(User $user, int $accountId): array {
    try {
        // Step 1: Fetch related userId
        $stmtGetUserId = oci_parse($this->connection, 
            "SELECT userId FROM ACCOUNT WHERE accountId = :accountId"
        );
        oci_bind_by_name($stmtGetUserId, ":accountId", $accountId);
        oci_execute($stmtGetUserId);
        $row = oci_fetch_assoc($stmtGetUserId);

        if (!$row) {
            return ["result" => "fail", "message" => "Account not found"];
        }

        $userId = $row['USERID'];

        // Step 2: Update USERS table
        $sql = "UPDATE USERS 
                SET fullName = :fullName, email = :email, phoneNumber = :phoneNumber, address = :address
                WHERE userId = :userId";
        $stmt = oci_parse($this->connection, $sql);

        $fullName = $user->getFullName();
        $email = $user->getEmail();
        $phoneNumber = $user->getPhoneNumber();
        $address = $user->getAddress();

        oci_bind_by_name($stmt, ':fullName', $fullName);
        oci_bind_by_name($stmt, ':email', $email);
        oci_bind_by_name($stmt, ':phoneNumber', $phoneNumber);
        oci_bind_by_name($stmt, ':address', $address);
        oci_bind_by_name($stmt, ':userId', $userId);

        if (!oci_execute($stmt, OCI_NO_AUTO_COMMIT)) {
            $e = oci_error($stmt);
            oci_rollback($this->connection);
            return ["result" => "fail", "message" => $e['message']];
        }

        oci_commit($this->connection);
        return ["result" => "success", "message" => "User profile updated successfully"];

    } catch (\Throwable $th) {
        oci_rollback($this->connection);
        return ["result" => "fail", "message" => $th->getMessage()];
    }
}

public function updateUserPassword(string $currentPassword, string $newPassword, int $accountId): array {
    try {
        // Step 1: Fetch current password from DB
        $stmt = oci_parse($this->connection, 
            "SELECT password FROM ACCOUNT WHERE accountId = :accountId"
        );
        oci_bind_by_name($stmt, ':accountId', $accountId);
        oci_execute($stmt);
        $row = oci_fetch_assoc($stmt);

        if (!$row) {
            return ["result" => "fail", "message" => "Account not found"];
        }

        $hashedPassword = $row['PASSWORD'];

        // Step 2: Verify current password
        if (!password_verify($currentPassword, $hashedPassword)) {
            return ["result" => "fail", "message" => "Current password is incorrect"];
        }

        // Step 3: Hash new password and update
        $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = oci_parse($this->connection, 
            "UPDATE ACCOUNT SET password = :newPassword WHERE accountId = :accountId"
        );
        oci_bind_by_name($updateStmt, ':newPassword', $newHashedPassword);
        oci_bind_by_name($updateStmt, ':accountId', $accountId);

        if (!oci_execute($updateStmt, OCI_NO_AUTO_COMMIT)) {
            $e = oci_error($updateStmt);
            oci_rollback($this->connection);
            return ["result" => "fail", "message" => $e['message']];
        }

        oci_commit($this->connection);
        return ["result" => "success", "message" => "Password updated successfully"];

    } catch (\Throwable $th) {
        oci_rollback($this->connection);
        return ["result" => "fail", "message" => $th->getMessage()];
    }
}






}

