<?php

header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require_once __DIR__ . '/../controllers/SliderController.php';

$controller = new SliderController();

$route = $_GET['route'] ?? '';

switch ($route) {

    case 'addSlider':
        $controller->addSlider();
        break;

    case 'getSliders':
        $controller->getSliders();
        break;

    case 'updateSlider':
        $controller->updateSlider();
        break;

    case 'deleteSlider':
        $controller->deleteSlider();
        break;

    default:
        echo json_encode([
            "status" => false,
            "message" => "Invalid Route"
        ]);
}