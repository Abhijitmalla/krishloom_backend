<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/ReviewController.php';

$database = new Database();
$db = $database->connect();

$controller = new ReviewController($db);

$route = $_GET['route'] ?? '';

switch ($route) {

    case 'addReview':
        $controller->addReview();
        break;

    case 'getReviews':
        $controller->getReviews();
        break;

    case 'getAllReviews':
        $controller->getAllReviews();
        break;

    case 'deleteReview':
        $controller->deleteReview();
        break;

        case 'updateReviewStatus':
    $controller->updateReviewStatus();
    break;

    default:
        echo json_encode([
            "success" => false,
            "message" => "Invalid Route"
        ]);
}