<?php

require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/Account.php';
require_once __DIR__ . '/../Repository/UserRepository.php';
require_once __DIR__ . '/../Config/Database.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

use Model\User;
use Model\Account;
use Repository\UserRepositoryImpl;
use Config\Database;

$connection = Database::getConnection();
$userRepo = new UserRepositoryImpl($connection);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);

            $user = new User();

            $account = new Account();

            $user -> setEmail($data['email']);
            $user -> setNricNumber($data['nricNumber']);
            $user -> setFullName($data['fullName']);

            $account -> setPassword($data['password']);
            $account -> setUsername($data['username']);

            $result = $userRepo->register($user, $account);
            header('Content-Type: application/json');
            echo json_encode($result);
        }
        break;

    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
            $data = json_decode(file_get_contents("php://input"), true);
            $result = $userRepo->login($data['username'] ?? '', $data['password'] ?? '');
            header('Content-Type: application/json');
            echo json_encode($result);
        }
        break;

    default:
        header('Content-Type: application/json');
        echo json_encode(["result" => "fail", "message" => "Invalid action"]);
        break;
}
    