<?php
namespace Repository;

interface TransactionRepository {
    function fetchAllTrans(): array;
}

class TransactionRepositoryImpl implements TransactionRepository {

    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function fetchAllTrans(): array {
        try {
            $sql = "SELECT * FROM TRANSACTION";
            $stmt = oci_parse($this->connection, $sql);
            oci_execute($stmt);

            $transactions = [];
            while (($row = oci_fetch_assoc($stmt)) !== false) {
                $transactions[] = $row;
            }

            return [
                "result" => "success",
                "data" => $transactions
            ];

        } catch (\Throwable $th) {
            return [
                "result" => "fail",
                "message" => $th->getMessage()
            ];
        }
    }
}
