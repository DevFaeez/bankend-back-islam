<?php
namespace Dashboard;

use Model\User;
use Model\Account;

interface UserDashboard {
    function dashboard(string $account);
}

class UserDashboardImpl implements UserDashboard { 

private $connection; // ?

public function __construct($connection) {
    $this->connection = $connection;
}

public function dashboard(string $accountId) {
    $sqlstmnt = "SELECT * FROM ACCOUNT A JOIN USERS U 
                ON A.userId = U.userId
                 WHERE accountId = :accountId";
    $stmtCheck = oci_parse($this->connection, $sqlstmnt);
    // $accountId = $account->getAccountId();
    oci_bind_by_name($stmtCheck,  ':accountId', $accountId);
    oci_execute($stmtCheck);

    $data = oci_fetch_assoc($stmtCheck);

    if ($data) {
       return [
                "result" => "success",
                "user" => [
                    "user_id" => $data['USERID'],
                    "email" => $data['EMAIL'],
                    "accountId" => $data['ACCOUNTID'],
                    "balance" => $data['BALANCE'],
                ]
            ];
    }
    
}
    
} 
