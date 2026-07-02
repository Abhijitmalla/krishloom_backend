<?php
$allowed = ['http://127.0.0.1:5500', 'http://localhost', 'http://localhost:5500', 'http://127.0.0.1'];
$origin  = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: *");
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require_once __DIR__ . '/../controllers/AddressController.php';

$controller = new AddressController();

$route = $_GET['route'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

switch ($route) {
    case 'addAddress':
        $controller->addAddress();
        break;

    case 'getAddresses':
        $controller->getAddresses();
        break;

    case 'updateAddress':
        $controller->updateAddress();
        break;

    case 'setDefaultAddress':
        $controller->updateAddress(); // same handler: sends { id, user_id, is_default:1 }
        break;

    case 'deleteAddress':
        $controller->deleteAddress();
        break;

    default:
        echo json_encode([
            "status" => false,
            "message" => "Invalid route"
        ]);
}