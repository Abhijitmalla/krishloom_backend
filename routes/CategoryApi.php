<?php

header("Content-Type: application/json");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../controllers/CategoryController.php';

$controller = new CategoryController();

$route = $_GET['route'] ?? '';

switch ($route) {

    case 'addCategory':
        $controller->addCategory();
        break;

    case 'getCategory':
        $controller->getCategory();
        break;

    case 'updateCategory':
        $controller->updateCategory();
        break;

    case 'deleteCategory':
        $controller->deleteCategory();
        break;

    default:
        echo json_encode([
            'status' => false,
            'message' => 'Invalid Route'
        ]);
}