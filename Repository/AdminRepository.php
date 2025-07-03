<?php
namespace Repository;

interface AdminRepository {
    function fetchAdmin(int $employeeId): array;
    function adminLogin(string $username, string $password): array;
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


}