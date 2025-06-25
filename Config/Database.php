<?php 
namespace Config {
class Database{
static function getConnection() {
    $user = "Sedun"; //oracle username
    $pass = "Sedun"; //Oracle password
    $host = "localhost:1521/FREEPDB1/"; //server name or ip address

    $dbconn = oci_connect($user, $pass, $host);

    if (!$dbconn) {
        $e = oci_error();
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
    } else {
    }
    return $dbconn;

        }
    }
}
 

