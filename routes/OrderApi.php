<?php
$allowed = ['http://127.0.0.1:5500', 'http://localhost', 'http://localhost:5500'];
$origin  = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: *");
}
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once __DIR__ . '/../controllers/OrderController.php';

$controller = new OrderController();

$route = $_GET['route'] ?? '';

switch ($route) {

    case 'checkout':
        $controller->checkout();
        break;
case 'placeOrder':
    $data = json_decode(file_get_contents("php://input"), true);
    $controller->placeOrder($data);
    break;
    case 'downloadInvoice':
        $controller->downloadInvoice();
        break;

    case 'getOrderDetails':
        $orderId = $_GET['order_id'] ?? '';
        $controller->getOrderDetails($orderId);
        break;

    case 'getAllOrders':
        $controller->getAllOrders();
        break;

    case 'updateOrderStatus':
        $controller->updateOrderStatus();
        break;

    case 'deleteOrder':
        $controller->deleteOrder();
        break;

    default:
        echo json_encode([
            "status" => false,
            "message" => "Invalid Route"
        ]);
        break;
}