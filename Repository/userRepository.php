<?php
namespace Repository;

use Model\User;

interface UserRepository {
    function register(User $user): array;
    function login(string $email, string $password): array;
}

class UserRepositoryImpl implements UserRepository {

    private $connection; // no longer typed as PDO

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function register(User $user): array {
        try {
            // Check if user already exists by email
            $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = oci_parse($this->connection, $sql);
            oci_bind_by_name($stmt, ':email', $email = $user->getEmail());
            oci_execute($stmt);

            if (oci_fetch($stmt)) {
                return ["result" => "fail", "message" => "Email already registered"];
            }

            // Hash the password
            $hashedPassword = password_hash($user->getPassword(), PASSWORD_DEFAULT);

            // Insert data
            $insertSql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
            $insertStmt = oci_parse($this->connection, $insertSql);
            oci_bind_by_name($insertStmt, ':username', $username = $user->getUsername());
            oci_bind_by_name($insertStmt, ':email', $email = $user->getEmail());
            oci_bind_by_name($insertStmt, ':password', $hashedPassword);
            oci_execute($insertStmt);

            return ["result" => "success"];
        } catch (\Throwable $th) {
            return ["result" => "fail", "message" => $th->getMessage()];
        }
    }

    public function login(string $email, string $password): array {
        try {
            $sql = "SELECT user_id, username, email, password FROM users WHERE email = :email";
            $stmt = oci_parse($this->connection, $sql);
            oci_bind_by_name($stmt, ':email', $email);
            oci_execute($stmt);

            $row = oci_fetch_assoc($stmt);

            if ($row && password_verify($password, $row['PASSWORD'])) {
                return [
                    "result" => "success",
                    "user" => [
                        "user_id" => $row['USER_ID'],
                        "username" => $row['USERNAME'],
                        "email" => $row['EMAIL']
                    ]
                ];
            } else {
                return ["result" => "fail", "message" => "Invalid email or password"];
            }
        } catch (\Throwable $th) {
            return ["result" => "fail", "message" => $th->getMessage()];
        }
    }

public function find(string $id): array {
    try {
        $sql = "SELECT employee_id, first_name, last_name, email, job_id, department_id 
                FROM employees 
                WHERE employee_id = 100";
                
        $stmt = oci_parse($this->connection, $sql);
        oci_execute($stmt);

        $row = oci_fetch_assoc($stmt);

        if ($row) {
            return [
                "result" => "success",
                "employee" => [
                    "employee_id"   => $row['EMPLOYEE_ID'],
                    "last_name"     => $row['LAST_NAME'],
                    "email"         => $row['EMAIL'],
                    "job_id"        => $row['JOB_ID'],
                    "department_id" => $row['DEPARTMENT_ID']
                ]
            ];
        } else {
            return ["result" => "fail", "message" => "Employee not found"];
        }
    } catch (\Throwable $th) {
        return ["result" => "fail", "message" => $th->getMessage()];
    }
}

}
