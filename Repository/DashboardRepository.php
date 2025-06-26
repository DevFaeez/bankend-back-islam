<?php

namespace Repository;

interface DashboardRepository {
    public function fetchDashboard(string $accountId);
}

class DashboardRepositoryImpl implements DashboardRepository {

    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function fetchDashboard(string $accountId) {
        try {
            
            $sqlFetchDashboard = "SELECT a.BALANCE, a.ACCOUNTNUMBER, u.FULLNAME 
                                FROM ACCOUNT a JOIN USERS u ON a.userid = u.userid 
                                WHERE accountid = :accountId";
            $stmtSqlFetchDashboard = oci_parse($this->connection, $sqlFetchDashboard);
            oci_bind_by_name($stmtSqlFetchDashboard, ':accountId', $accountId);
            oci_execute($stmtSqlFetchDashboard);

            $data = oci_fetch_assoc($stmtSqlFetchDashboard);
            if ($data) {
                // var_dump(array_keys($data));
                return [
                    "result" => "success",
                    "data" => [
                        "balance" => (float) $data['BALANCE'],
                        "accountNumber" => $data['ACCOUNTNUMBER'],
                        "fullname" => $data['FULLNAME'],
                    ]
                    ];
            } else {
                return [
                    "result" => "fail",
                    "message" => "error fetching data for dashbaord"
                ];
            }

        } catch (\Throwable $th) {
            return [
            "result" => "fail",
            "message" => $th->getMessage()
        ];
        }
    }

}