<?php


header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require_once __DIR__ . '/../controllers/ProductController.php';

$product = new ProductController();

$route = $_GET['route'] ?? '';

switch ($route) {

  case 'addProduct':
        $product->addProduct();
        break;

    case 'getProducts':
        $product->getProducts();
        break;

    case 'getProduct':
        $product->getProduct();
        break;

    case 'updateProduct':
        $product->updateProduct();
        break;

    case 'deleteProduct':
        $product->deleteProduct();
        break;

        case 'updateGalleryImage':
    $product->updateGalleryImage();
    break;

    case 'deleteGalleryImage':
        $product->deleteGalleryImage();
        break;

    default:
        echo json_encode([
            'status' => false,
            'message' => 'Invalid Route'
        ]);
}