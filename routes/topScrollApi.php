<?php

header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");
require_once __DIR__ . '/../controllers/TopScrollController.php';

$controller = new TopScrollController();

$route = $_GET['route'] ?? '';

switch ($route) {

    case 'getTopScroll':
        $controller->getTopScroll();
        break;

    case 'addTopScroll':
        $controller->addTopScroll();
        break;

    case 'updateTopScroll':
        $controller->updateTopScroll();
        break;

    case 'deleteTopScroll':
        $controller->deleteTopScroll();
        break;

    default:
        echo json_encode([
            "status" => false,
            "message" => "Invalid Route"
        ]);
}