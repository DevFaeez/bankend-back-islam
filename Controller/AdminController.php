<?php

use Model\Employee;

require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/Employee.php';
require_once __DIR__ . '/../Repository/AdminRepository.php';
require_once __DIR__ . '/../Config/Database.php';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

use Repository\AdminRepositoryImpl;
use Config\Database;

$connection = Database::getConnection();
$adminRepo = new AdminRepositoryImpl($connection);
 
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'fetchAdmin':
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $employeeId = $_GET['employeeId'] ?? 0;
        $result = $adminRepo->fetchAdmin((int)$employeeId);
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    break;

    case 'adminLogin':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $result = $adminRepo->adminLogin($username, $password);
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    break;
    case 'updateAdmin':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['employeeId'])) {
            echo json_encode(["result" => "fail", "message" => "employeeId is required"]);
            exit;
        }

        $user = new Employee();
        $user->setFullName($data['fullName'] ?? '');
        $user->setUsername($data['username'] ?? '');
        $user->setEmail($data['email'] ?? '');
        $user->setRole($data['role'] ?? '');
        $user->setStatus($data['status'] ?? '');
        $user->setPassword($data['password'] ?? '');

        $result = $adminRepo->updateAdminProfile($user, (int)$data['employeeId']);

        header('Content-Type: application/json');
        echo json_encode($result);
    } else {
        http_response_code(405); // Method Not Allowed
        echo json_encode(["result" => "fail", "message" => "Method not allowed"]);
    }
    break;



    default:
        header('Content-Type: application/json');
        echo json_encode(["result" => "fail", "message" => "Invalid action"]);
        break;
}
    