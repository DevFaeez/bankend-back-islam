<?php

namespace Repository;

use Model\Employee;


interface AdminRepository {
    
    function registerAdmin(Employee $employee);
    function fetchAdmin(int $employeeId): array;
    function fetchAllAdmin(): array;
    function adminLogin(string $username, string $password): array;

    function updateAdminProfile(Employee $user, int $employeeId): array;

    function changeAdminPassword(int $employeeId, string $currentPassword, string $newPassword): array;

    
}

class AdminRepositoryImpl implements AdminRepository {

    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

public function fetchAdmin(int $employeeId): array {
    try {
        $sql = "SELECT *
                FROM EMPLOYEE
                WHERE employeeId = :employeeId";

        $stmt = oci_parse($this->connection, $sql);
        oci_bind_by_name($stmt, ':employeeId', $employeeId);
        oci_execute($stmt);

        $user = oci_fetch_assoc($stmt);

        if ($user) {
            return [
                "result" => "success",
                "data" => [
                    "employeeId" => $user['EMPLOYEEID'],
                    "username" => $user['USERNAME'],
                    "fullName" => $user['FULLNAME'],
                    "email" => $user['EMAIL'],
                    "role" => $user['ROLE'],
                    "status" => $user['STATUS'],
                    "password" => $user['PASSWORD']
                ]
            ];
        } else {
            return [
                "result" => "fail",
                "message" => "Admin not found"
            ];
        }

    } catch (\Throwable $th) {
        return ["result" => "fail", "message" => $th->getMessage()];
    }
}

public function fetchAllAdmin(): array {
     try {
            $sql = "SELECT E.employeeId AS EMPLOYEE_ID, E.username, E.email, E.fullName, E.role, E.status, E.managerId, M.fullName as MANAGER_NAME
                    FROM EMPLOYEE E LEFT JOIN EMPLOYEE M ON E.managerId = M.employeeId";
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

public function registerAdmin(Employee $employee): array {
    try {
        // Step 1: Check if email or username already exists
        $sqlCheck = "SELECT 1 FROM EMPLOYEE WHERE email = :email OR username = :username";
        $stmtCheck = oci_parse($this->connection, $sqlCheck);
        $email = $employee->getEmail();
        $username = $employee->getUsername();
        oci_bind_by_name($stmtCheck, ':email', $email);
        oci_bind_by_name($stmtCheck, ':username', $username);
        oci_execute($stmtCheck);

        if (oci_fetch_assoc($stmtCheck)) {
            return ["result" => "fail", "message" => "Email or username already registered"];
        }

        // Step 2: Prepare data for insert
        $fullName = $employee->getFullName();
        $role = $employee->getRole();
        $status = $employee->getStatus();
        $managerId = $employee->getManagerId(); // Can be null
        $password = $employee->getPassword();

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Step 3: Insert into EMPLOYEE table
        $employeeId = null;
        $sqlInsert = "INSERT INTO EMPLOYEE (username, email, password, fullName, role, status, managerId)
                        VALUES (:username, :email, :password, :fullName, :role, :status, :managerId)
                        RETURNING employeeId INTO :employeeId";

        $stmtInsert = oci_parse($this->connection, $sqlInsert);
        oci_bind_by_name($stmtInsert, ':username', $username);
        oci_bind_by_name($stmtInsert, ':email', $email);
        oci_bind_by_name($stmtInsert, ':password', $hashedPassword);
        oci_bind_by_name($stmtInsert, ':fullName', $fullName);
        oci_bind_by_name($stmtInsert, ':role', $role);
        oci_bind_by_name($stmtInsert, ':status', $status);
        oci_bind_by_name($stmtInsert, ':managerId', $managerId);
        oci_bind_by_name($stmtInsert, ':employeeId', $employeeId, 32);

        if (!oci_execute($stmtInsert, OCI_NO_AUTO_COMMIT)) {
            oci_rollback($this->connection);
            $e = oci_error($stmtInsert);
            return ["result" => "fail", "message" => "Insert EMPLOYEE failed: " . $e['message']];
        }

        oci_commit($this->connection);

        return [
            "result" => "success",
            "message" => "Admin registered successfully.",
            "data" => [
                "employeeId" => $employeeId,
                "username" => $username,
                "email" => $email
            ]
        ];

    } catch (\Throwable $th) {
        oci_rollback($this->connection);
        return ["result" => "fail", "message" => $th->getMessage()];
    }
}



public function adminLogin(string $username, string $password): array {
    try {
        $sql = "SELECT employeeId, username, password
                FROM EMPLOYEE
                WHERE username = :username";

        $stmt = oci_parse($this->connection, $sql);
        oci_bind_by_name($stmt, ':username', $username);
        oci_execute($stmt);

        $user = oci_fetch_assoc($stmt);

        if ($user && password_verify($password, $user['PASSWORD'])) {
            return [
                "result" => "success",
                "data" => [
                    "employeeId" => $user['EMPLOYEEID'],
                    "username" => $user['USERNAME']
                ]
            ];
        } else {
            return [
                "result" => "fail",
                "message" => "Invalid username or password"
            ];
        }

    } catch (\Throwable $th) {
        return ["result" => "fail", "message" => $th->getMessage()];
    }
}


public function updateAdminProfile(Employee $user, int $employeeId): array {
     try {
        $stmtGetEmployeeId = oci_parse($this->connection, "SELECT employeeId FROM EMPLOYEE WHERE employeeId = :employeeId");
        oci_bind_by_name($stmtGetEmployeeId, ":employeeId", $employeeId);
        oci_execute($stmtGetEmployeeId);
        $row = oci_fetch_assoc($stmtGetEmployeeId);

        if (!$row) {
            return ["result" => "fail", "message" => "Account not found"];
        }

        $sql = "UPDATE EMPLOYEE 
                SET fullName = :fullName, username = :username, email = :email, role = :role, status = :status
                WHERE employeeId = :employeeId";
        $stmt = oci_parse($this->connection, $sql);

        $fullName = $user->getFullName();
        $email = $user->getEmail();
        $username = $user->getUsername();
        $role = $user->getRole();
        $status = $user->getStatus();

        oci_bind_by_name($stmt, ':fullName', $fullName);
        oci_bind_by_name($stmt, ':username', $username);
        oci_bind_by_name($stmt, ':email', $email);
        oci_bind_by_name($stmt, ':role', $role);
        oci_bind_by_name($stmt, ':status', $status);
        oci_bind_by_name($stmt, ':employeeId', $employeeId);

        if (!oci_execute($stmt, OCI_NO_AUTO_COMMIT)) {
            $e = oci_error($stmt);
            oci_rollback($this->connection);
            return ["result" => "fail", "message" => $e['message']];
        }

        oci_commit($this->connection);
        return ["result" => "success", "message" => "Profile info updated"];
    } catch (\Throwable $th) {
        oci_rollback($this->connection);
        return ["result" => "fail", "message" => $th->getMessage()];
    }
}

public function changeAdminPassword(int $employeeId, string $currentPassword, string $newPassword): array {
    try {
        $stmt = oci_parse($this->connection, "SELECT password FROM EMPLOYEE WHERE employeeId = :employeeId");
        oci_bind_by_name($stmt, ':employeeId', $employeeId);
        oci_execute($stmt);
        $row = oci_fetch_assoc($stmt);

        if (!$row) {
            return ["result" => "fail", "message" => "Account not found"];
        }

        $currentHash = $row['PASSWORD'];

        if (!password_verify($currentPassword, $currentHash)) {
            return ["result" => "fail", "message" => "Current password is incorrect"];
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $updateStmt = oci_parse($this->connection, "UPDATE EMPLOYEE SET password = :newPassword WHERE employeeId = :employeeId");
        oci_bind_by_name($updateStmt, ':newPassword', $newHash);
        oci_bind_by_name($updateStmt, ':employeeId', $employeeId);

        if (!oci_execute($updateStmt, OCI_NO_AUTO_COMMIT)) {
            $e = oci_error($updateStmt);
            oci_rollback($this->connection);
            return ["result" => "fail", "message" => $e['message']];
        }

        oci_commit($this->connection);
        return ["result" => "success", "message" => "Password changed successfully"];
    } catch (\Throwable $th) {
        oci_rollback($this->connection);
        return ["result" => "fail", "message" => $th->getMessage()];
    }
}



}