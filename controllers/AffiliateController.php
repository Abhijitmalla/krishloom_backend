<?php

require_once __DIR__ . '/../models/Affiliate.php';

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
}