<?php
header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__.'/../controllers/CartController.php';

$controller=new CartController();

$route=$_GET['route'] ?? '';

switch($route){

    case 'addCart':
        $controller->addCart();
        break;

    case 'getCart':
        $controller->getCart();
        break;

    case 'updateQuantity':
        $controller->updateQuantity();
        break;

    case 'removeCart':
        $controller->removeCart();
        break;

    case 'clearCart':
        $controller->clearCart();
        break;

    default:
        echo json_encode([
            "status"=>false,
            "message"=>"Invalid Route"
        ]);
}