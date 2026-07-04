<?php

header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require_once __DIR__ . '/../controllers/DeliveryChargeController.php';

$controller = new DeliveryChargeController();

$route = $_GET['route'] ?? '';

switch ($route) {

    case 'addDeliveryCharge':
        $controller->addDeliveryCharge();
        break;

    case 'getDeliveryCharge':
        $controller->getDeliveryCharge();
        break;

    case 'updateDeliveryCharge':
        $controller->updateDeliveryCharge();
        break;

    case 'deleteDeliveryCharge':
        $controller->deleteDeliveryCharge();
        break;

    default:
        echo json_encode([
            "status" => false,
            "message" => "Invalid Route"
        ]);
}
?>