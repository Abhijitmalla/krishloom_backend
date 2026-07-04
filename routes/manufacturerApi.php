<?php

header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/ManufacturerController.php';

$database = new Database();
$db = $database->connect();

$controller = new ManufacturerController($db);

$route = $_GET['route'] ?? '';

switch ($route) {

    case 'getManufacturer':
        $controller->getManufacturer();
        break;

    case 'addManufacturer':
        $controller->addManufacturer();
        break;

    case 'updateManufacturer':
        $controller->updateManufacturer();
        break;

    case 'deleteManufacturer':
        $controller->deleteManufacturer();
        break;

    default:
        echo json_encode([
            "status" => false,
            "message" => "Invalid route."
        ]);
}