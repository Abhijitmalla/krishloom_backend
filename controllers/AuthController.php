<?php

require_once __DIR__ . '/../models/User.php';
class AuthController
{
    private $user;

    public function __construct()
    {
        $this->user = new User();
    }

  public function register()
{
    // Support both JSON body and multipart/form-data
    $raw  = file_get_contents("php://input");
    $data = json_decode($raw, true);

    // Fallback to $_POST if JSON decode fails (form-data submission)
    if (!is_array($data)) {
        $data = $_POST;
    }

    $name     = trim($data['name']     ?? '');
    $email    = trim($data['email']    ?? '');
    $phone    = trim($data['phone']    ?? '');
    $password = trim($data['password'] ?? '');

    // Validate required fields
    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        echo json_encode([
            "status"  => false,
            "message" => "All fields are required",
            "debug"   => [
                "name_received"     => $name,
                "email_received"    => $email,
                "phone_received"    => $phone,
                "raw_body_preview"  => substr($raw, 0, 200),
                "content_type"      => $_SERVER['CONTENT_TYPE'] ?? 'not set',
            ]
        ]);
        return;
    }

    // Validate Name (only letters and spaces)
    if (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        echo json_encode([
            "status"  => false,
            "message" => "Name must contain only letters and spaces"
        ]);
        return;
    }

    // Validate Phone (exactly 10 digits)
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        echo json_encode([
            "status"  => false,
            "message" => "Mobile number must be exactly 10 digits"
        ]);
        return;
    }

    $check = $this->user->findByEmailOrPhone($email, $phone);

    if ($check->num_rows > 0) {
        echo json_encode([
            "status"  => false,
            "message" => "Email or Phone already exists"
        ]);
        return;
    }

    $password = password_hash($password, PASSWORD_BCRYPT);

    $save = $this->user->create($name, $email, $phone, $password);

    if (!$save) {
        $error = $this->user->getLastError();
        $message = "Registration Failed";

        if (stripos($error, 'duplicate') !== false) {
            if (stripos($error, 'phone') !== false) {
                $message = 'Phone already exists';
            } elseif (stripos($error, 'email') !== false) {
                $message = 'Email already exists';
            } else {
                $message = 'Email or Phone already exists';
            }
        }

        echo json_encode([
            "status"  => false,
            "message" => $message,
            "error"   => $error
        ]);
        return;
    }

    echo json_encode([
        "status"  => true,
        "message" => "Registration Successful"
    ]);
}
public function login()
{
    $data = json_decode(file_get_contents("php://input"), true);

    // Accept 'login' field OR individual 'email'/'phone' fields
    $login    = $data['login']    ?? $data['email'] ?? $data['phone'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($login) || empty($password)) {
        echo json_encode([
            "status"  => false,
            "message" => "Email/Phone and password are required"
        ]);
        return;
    }

    $result = $this->user->findByLogin($login);

    if ($result->num_rows == 0) {
        echo json_encode([
            "status"  => false,
            "message" => "Invalid Email or Mobile Number"
        ]);
        return;
    }

    $user = $result->fetch_assoc();

    if (!password_verify($password, $user['password'])) {
        echo json_encode([
            "status"  => false,
            "message" => "Invalid Password"
        ]);
        return;
    }

    echo json_encode([
        "status"  => true,
        "message" => "Login Successful",
        "user"    => [
            "id"    => $user['id'],
            "name"  => $user['name'],
            "email" => $user['email'],
            "phone" => $user['phone']
        ]
    ]);
}

public function getUser()
{
    $id = $_GET['id'] ?? '';

    if (empty($id)) {
        echo json_encode([
            "status" => false,
            "message" => "User ID is required"
        ]);
        return;
    }

    $result = $this->user->getUserById($id);

    if (!$result || $result->num_rows == 0) {
        echo json_encode([
            "status" => false,
            "message" => "User not found"
        ]);
        return;
    }

    $user = $result->fetch_assoc();

    echo json_encode([
        "status" => true,
        "message" => "User details fetched successfully",
        "user" => $user
    ]);
}


public function updateProfile()
{
    $data = json_decode(file_get_contents("php://input"), true);

    $id = $data['id'] ?? '';
    $dob = $data['dob'] ?? '';
    $gender = $data['gender'] ?? '';
    $address = $data['address'] ?? '';

    if (empty($id)) {
        echo json_encode([
            "status" => false,
            "message" => "User ID required"
        ]);
        return;
    }

    $update = $this->user->updateProfile(
        $id,
        $dob,
        $gender,
        $address
    );

    echo json_encode([
        "status" => $update,
        "message" => $update
            ? "Profile updated successfully"
            : "Update failed"
    ]);
}

public function uploadProfileImage()
{
    $id = $_POST['id'] ?? '';

    if (empty($id) || !isset($_FILES['profile_image'])) {
        echo json_encode([
            "status" => false,
            "message" => "Invalid request"
        ]);
        return;
    }

    $file = $_FILES['profile_image'];

    // Allow only images
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        echo json_encode([
            "status" => false,
            "message" => "Only JPG, PNG and WEBP are allowed"
        ]);
        return;
    }

    $fileName = time() . "_" . uniqid() . "." . $ext;

    $uploadDir = __DIR__ . '/../uploads/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (!move_uploaded_file(
        $file['tmp_name'],
        $uploadDir . $fileName
    )) {
        echo json_encode([
            "status" => false,
            "message" => "Image upload failed"
        ]);
        return;
    }

    $update = $this->user->updateProfileImage(
        $id,
        $fileName
    );

    echo json_encode([
        "status" => $update,
        "image" => $fileName
    ]);
}
}