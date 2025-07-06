<?php

namespace Repository;

use Model\Employee;


interface AdminRepository {
    function fetchAdmin(int $employeeId): array;
    function adminLogin(string $username, string $password): array;

    function updateAdminProfile(Employee $user, int $employeeId): array;

    
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
public function adminLogin(string $username, string $password): array {
    try {
        $sql = "SELECT employeeId, username, password
                FROM EMPLOYEE
                WHERE username = :username";

        $stmt = oci_parse($this->connection, $sql);
        oci_bind_by_name($stmt, ':username', $username);
        oci_execute($stmt);

        $user = oci_fetch_assoc($stmt);

        if ($user) {
            return [
                "result" => "success",
                "data" => [
                    "employeeId" => $user['EMPLOYEEID'],
                ]
            ];
        } else {
            return [
                "result" => "fail",
                "message" => "Login Fail"
            ];
        }

    } catch (\Throwable $th) {
        return ["result" => "fail", "message" => $th->getMessage()];
    }
}

public function updateAdminProfile(Employee $user, int $employeeId): array {
    try {
        // Fetch related employeeId
        $stmtGetEmployeeId = oci_parse($this->connection, "SELECT employeeId FROM EMPLOYEE WHERE employeeId = :employeeId");
        oci_bind_by_name($stmtGetEmployeeId, ":employeeId", $employeeId);
        oci_execute($stmtGetEmployeeId);
        $row = oci_fetch_assoc($stmtGetEmployeeId);

        if (!$row) {
            return ["result" => "fail", "message" => "Account not found"];
        }

        $employeeId = $row['EMPLOYEEID'];

        // Now update user profile
        $sql = "UPDATE EMPLOYEE 
                SET fullName = :fullName, username = :username, email = :email, role = :role, status = :status,  password = :password 
                WHERE employeeId = :employeeId";
        $stmt = oci_parse($this->connection, $sql);

        $fullName = $user->getFullName();
        $email = $user->getEmail();
        $username = $user->getUsername();
        $role = $user->getRole();
        $status = $user->getStatus();

        $hashedPassword = password_hash($user->getPassword(), PASSWORD_DEFAULT);


        oci_bind_by_name($stmt, ':fullName', $fullName);
        oci_bind_by_name($stmt, ':username', $username);
        oci_bind_by_name($stmt, ':email', $email);
        oci_bind_by_name($stmt, ':role', $role);
        oci_bind_by_name($stmt, ':status', $status);
        oci_bind_by_name($stmt, ':password', $hashedPassword);
        oci_bind_by_name($stmt, ':employeeId', $employeeId);

        if (!oci_execute($stmt, OCI_NO_AUTO_COMMIT)) {
            $e = oci_error($stmt);
            oci_rollback($this->connection);
            return ["result" => "fail", "message" => $e['message']];
        }

        oci_commit($this->connection);

        return ["result" => "success", "message" => "Admin profile updated successfully"];

    } catch (\Throwable $th) {
        oci_rollback($this->connection);
        return ["result" => "fail", "message" => $th->getMessage()];
    }
}


}