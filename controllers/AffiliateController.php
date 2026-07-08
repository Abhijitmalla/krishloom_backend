<?php

require_once __DIR__ . '/../models/Affiliate.php';
require_once __DIR__ . '/../helpers/SmsHelper.php';

class AffiliateController
{
    private $affiliate;

    public function __construct()
    {
        $this->affiliate = new Affiliate();
    }

    public function register()
    {
        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        if (
            empty($data['membership_id']) ||
            empty($data['name']) ||
            empty($data['mobile_no']) ||
            empty($data['email']) ||
            empty($data['shipping_address']) ||
            empty($data['state']) ||
            empty($data['city']) ||
            empty($data['pin_code']) ||
            empty($data['password'])
        ) {
            echo json_encode([
                "status" => false,
                "message" => "All fields are required."
            ]);
            return;
        }

        if (!preg_match('/^[0-9]{10}$/', $data['mobile_no'])) {
            echo json_encode([
                "status" => false,
                "message" => "Mobile number must be 10 digits."
            ]);
            return;
        }

        if (!preg_match('/^[0-9]{6}$/', $data['pin_code'])) {
            echo json_encode([
                "status" => false,
                "message" => "PIN code must be 6 digits."
            ]);
            return;
        }

        $data['password'] = password_hash(
            $data['password'],
            PASSWORD_DEFAULT
        );

        try {

            $result = $this->affiliate->register($data);

            echo json_encode([
                "status" => $result,
                "message" => $result
                    ? "Affiliate registered successfully."
                    : "Registration failed."
            ]);

        } catch (Exception $e) {

            echo json_encode([
                "status" => false,
                "message" => $e->getMessage()
            ]);
        }
    }

    public function login()
    {
        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        if (
            empty($data['login']) ||
            empty($data['password'])
        ) {
            echo json_encode([
                "status" => false,
                "message" => "Login and password are required."
            ]);
            return;
        }

        $user = $this->affiliate->findByLogin(
            $data['login']
        );

        if (
            $user &&
            password_verify(
                $data['password'],
                $user['password']
            )
        ) {

            unset($user['password']);

            echo json_encode([
                "status" => true,
                "message" => "Login successful.",
                "user" => $user
            ]);

        } else {

            echo json_encode([
                "status" => false,
                "message" => "Invalid Membership ID/Mobile Number or Password."
            ]);
        }
    }



public function sendOTP()
{
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['mobile_no'])) {
        $data['mobile_no'] = $this->normalizeDigits($data['mobile_no']);
    }

    if (isset($data['pin_code'])) {
        $data['pin_code'] = $this->normalizeDigits($data['pin_code']);
    }

    // Validate required fields
    if (
        empty($data['membership_id']) ||
        empty($data['name']) ||
        empty($data['mobile_no']) ||
        empty($data['email']) ||
        empty($data['shipping_address']) ||
        empty($data['state']) ||
        empty($data['city']) ||
        empty($data['pin_code']) ||
        empty($data['password'])
    ) {
        echo json_encode([
            "status" => false,
            "message" => "All fields are required."
        ]);
        return;
    }

    if (!preg_match('/^[0-9]{10}$/', $data['mobile_no'])) {
        echo json_encode([
            "status" => false,
            "message" => "Mobile number must be 10 digits."
        ]);
        return;
    }

    if (!preg_match('/^[0-9]{6}$/', $data['pin_code'])) {
        echo json_encode([
            "status" => false,
            "message" => "PIN code must be 6 digits."
        ]);
        return;
    }

    // Check duplicate records
    if ($this->affiliate->membershipExists($data['membership_id'])) {
        echo json_encode(["status"=>false,"message"=>"Membership ID already exists"]);
        return;
    }

    if ($this->affiliate->mobileExists($data['mobile_no'])) {
        echo json_encode(["status"=>false,"message"=>"Mobile already exists"]);
        return;
    }

    if ($this->affiliate->emailExists($data['email'])) {
        echo json_encode(["status"=>false,"message"=>"Email already exists"]);
        return;
    }

    $otp = rand(100000,999999);

    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

    $result = $this->affiliate->saveOTP($data,$otp);

    if($result){

        // Try to send OTP via configured SMS provider
        $smsSent = SmsHelper::sendOtp($data['mobile_no'], (string)$otp);

        if ($smsSent) {
            // SMS delivered successfully
            echo json_encode([
                "status"  => true,
                "message" => "OTP sent successfully to your mobile number."
            ]);
        } else {
            // SMS failed (e.g. account not recharged) –
            // Return the OTP in the response so the user can still complete registration.
            // Remove this fallback once Fast2SMS is recharged.
            echo json_encode([
                "status"       => true,
                "message"      => "SMS delivery failed. Your OTP is shown below.",
                "sms_fallback" => true,
                "sms_error"    => SmsHelper::getLastError(),
                "otp"          => (string)$otp
            ]);
        }

    }else{

        echo json_encode([
            "status"=>false,
            "message"=>"Unable to save OTP. Please try again."
        ]);

    }
}

public function verifyOTP()
{
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['mobile_no'])) {
        $data['mobile_no'] = $this->normalizeDigits($data['mobile_no']);
    }

    if (isset($data['otp'])) {
        $data['otp'] = $this->normalizeDigits($data['otp']);
    }

    if (empty($data['mobile_no']) || empty($data['otp'])) {
        echo json_encode([
            "status" => false,
            "message" => "Mobile number and OTP are required."
        ]);
        return;
    }

    $result = $this->affiliate->verifyOTP(
        $data['mobile_no'],
        $data['otp']
    );

    if($result){

        echo json_encode([
            "status"=>true,
            "message"=>"Registration Successful"
        ]);

    }else{

        echo json_encode([
            "status"=>false,
            "message"=>"Invalid OTP"
        ]);
    }
}

    private function normalizeDigits($value)
    {
        return preg_replace('/\D+/', '', (string)$value);
    }

    public function getAllAffiliates()
    {
        try {
            $affiliates = $this->affiliate->getAll();
            
            echo json_encode([
                "status" => true,
                "data" => $affiliates
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => false,
                "message" => $e->getMessage()
            ]);
        }
    }

    public function adminLoginAsAffiliate()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['affiliate_id'])) {
            echo json_encode(["status" => false, "message" => "Affiliate ID required."]);
            return;
        }

        $user = $this->affiliate->findByLogin($data['affiliate_id']);

        if ($user) {
            unset($user['password']);
            echo json_encode([
                "status" => true,
                "message" => "Login successful as affiliate.",
                "user" => $user
            ]);
        } else {
            echo json_encode([
                "status" => false,
                "message" => "Affiliate not found."
            ]);
        }
    }

    public function submitKYC()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (
            empty($data['affiliate_id']) ||
            empty($data['aadhar_no']) ||
            empty($data['pan_no']) ||
            empty($data['bank_account']) ||
            empty($data['ifsc_code'])
        ) {
            echo json_encode([
                "status" => false,
                "message" => "All KYC fields are required."
            ]);
            return;
        }

        try {
            $result = $this->affiliate->updateKYC(
                $data['affiliate_id'],
                $data['aadhar_no'],
                $data['pan_no'],
                $data['bank_account'],
                $data['ifsc_code']
            );

            echo json_encode([
                "status" => $result,
                "message" => $result
                    ? "KYC updated successfully."
                    : "Failed to update KYC."
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => false,
                "message" => $e->getMessage()
            ]);
        }
    }

    public function getAffiliateProfile()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['id'])) {
            echo json_encode(["status" => false, "message" => "Affiliate ID required."]);
            return;
        }

        $affiliate = $this->affiliate->getById((int)$data['id']);

        if ($affiliate) {
            unset($affiliate['password']);
            echo json_encode(["status" => true, "data" => $affiliate]);
        } else {
            echo json_encode(["status" => false, "message" => "Affiliate not found."]);
        }
    }

    public function updateAffiliateProfile()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['id'])) {
            echo json_encode(["status" => false, "message" => "Affiliate ID required."]);
            return;
        }

        $result = $this->affiliate->updateProfile((int)$data['id'], $data);

        if ($result) {
            $affiliate = $this->affiliate->getById((int)$data['id']);
            unset($affiliate['password']);
            echo json_encode(["status" => true, "message" => "Profile updated successfully.", "data" => $affiliate]);
        } else {
            echo json_encode(["status" => false, "message" => "Failed to update profile."]);
        }
    }

    public function uploadProfilePic()
    {
        if (empty($_POST['id'])) {
            echo json_encode(["status" => false, "message" => "Affiliate ID required."]);
            return;
        }

        if (empty($_FILES['profile_pic']) || $_FILES['profile_pic']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(["status" => false, "message" => "No file uploaded or upload error."]);
            return;
        }

        $id = (int)$_POST['id'];
        $file = $_FILES['profile_pic'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed)) {
            echo json_encode(["status" => false, "message" => "Invalid file type. Only JPG, PNG, GIF, WEBP allowed."]);
            return;
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            echo json_encode(["status" => false, "message" => "File size must be under 2MB."]);
            return;
        }

        $uploadDir = __DIR__ . '/../uploads/profile_pics/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'affiliate_' . $id . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            echo json_encode(["status" => false, "message" => "Failed to save file."]);
            return;
        }

        $picUrl = 'http://localhost/krishloom-vastram/backend/uploads/profile_pics/' . $filename;
        $result = $this->affiliate->updateProfilePic($id, $picUrl);

        if ($result) {
            $affiliate = $this->affiliate->getById($id);
            unset($affiliate['password']);
            echo json_encode(["status" => true, "message" => "Profile picture updated.", "pic_url" => $picUrl, "data" => $affiliate]);
        } else {
            echo json_encode(["status" => false, "message" => "Failed to update profile picture in DB."]);
        }
    }

    public function sendAadharOTP()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['aadhar_no']) || empty($data['mobile_no'])) {
            echo json_encode(["status" => false, "message" => "Aadhar number and mobile number required."]);
            return;
        }

        $otp = rand(100000, 999999);
        $result = $this->affiliate->saveAadharOTP($data['aadhar_no'], $otp);

        if ($result) {
            $smsSent = SmsHelper::sendOtp($data['mobile_no'], (string)$otp);
            if ($smsSent) {
                echo json_encode(["status" => true, "message" => "OTP sent to Aadhar linked mobile number."]);
            } else {
                echo json_encode([
                    "status" => true,
                    "message" => "SMS delivery failed. Your OTP is shown below.",
                    "sms_fallback" => true,
                    "otp" => (string)$otp
                ]);
            }
        } else {
            echo json_encode(["status" => false, "message" => "Failed to generate OTP."]);
        }
    }

    public function verifyAadharOTP()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['aadhar_no']) || empty($data['otp'])) {
            echo json_encode(["status" => false, "message" => "Aadhar number and OTP required."]);
            return;
        }

        $result = $this->affiliate->verifyAadharOTP($data['aadhar_no'], $data['otp']);

        if ($result) {
            echo json_encode(["status" => true, "message" => "Aadhar Verified successfully."]);
        } else {
            echo json_encode(["status" => false, "message" => "Invalid or expired OTP."]);
        }
    }
}
