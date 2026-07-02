<?php


header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require_once __DIR__ . '/../controllers/WishlistController.php';

$controller = new WishlistController();

$route = $_GET['route'] ?? '';

switch ($route) {

    case 'addWishlist':
        $controller->addWishlist();
        break;

    case 'getWishlist':
        $controller->getWishlist();
        break;

    case 'removeWishlist':
        $controller->removeWishlist();
        break;

    case 'isWishlist':
        $controller->isWishlist();
        break;

    default:
        echo json_encode([
            "status" => false,
            "message" => "Invalid Route"
        ]);
        break;
}