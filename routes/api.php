<?php
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
} else {
    header("Access-Control-Allow-Origin: *");
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/AffiliateController.php';
require_once __DIR__ . '/../controllers/AdminController.php';


$auth = new AuthController();
$affiliate = new AffiliateController(); 
$admin = new AdminController();



$route = $_GET['route'] ?? '';

switch ($route) {
    case 'register':
        $auth->register();
        break;

    case 'login':
        $auth->login();
        break;
    case 'getUser':
    $auth->getUser();
        break;


        case 'updateProfile':
    $auth->updateProfile();
    break;

    case 'uploadProfileImage':
    $auth->uploadProfileImage();
    break;

    case 'getAllUsers':
    $auth->getAllUsers();
    break;

         // Affiliate routes
    case 'getAllAffiliates':
        $affiliate->getAllAffiliates();
        break;

    case 'adminLoginAsAffiliate':
        $affiliate->adminLoginAsAffiliate();
        break;

    case 'affiliateRegister':
        $affiliate->register();
        break;

        

    case 'affiliateLogin':
        $affiliate->login();
        break;
        case 'adminLogin':
        $admin->login();
        break;

    case 'adminChangePassword':
        $admin->changePassword();
        break;

    default:
        echo json_encode([
            "status" => false,
            "message" => "Route not found"
        ]);


    
        
}