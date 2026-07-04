<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../controllers/MasterTypeController.php';

$controller = new MasterTypeController();

$route = $_GET['route'] ?? '';

switch ($route) {

    case 'getMasterTypes':
        $controller->getMasterTypes();
        break;

    case 'getMasterType':
        $controller->getMasterType();
        break;

    case 'addMasterType':
        $controller->addMasterType();
        break;

    case 'updateMasterType':
        $controller->updateMasterType();
        break;

    case 'deleteMasterType':
        $controller->deleteMasterType();
        break;

    default:
        echo json_encode([
            "status" => false,
            "message" => "Invalid Route"
        ]);
        break;
}
?>