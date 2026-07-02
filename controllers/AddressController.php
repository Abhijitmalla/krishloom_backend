<?php

require_once __DIR__ . '/../models/Address.php';

class AddressController
{
    private $address;

    public function __construct()
    {
        $this->address = new Address();
    }

    public function addAddress()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $success = $this->address->addAddress($data);

        echo json_encode([
            "status" => $success,
            "message" => $success
                ? "Address added successfully"
                : "Unable to add address"
        ]);
    }

    public function getAddresses()
    {
        $userId = $_GET['user_id'];

        $addresses = $this->address->getAddresses($userId);

        echo json_encode([
            "status" => true,
            "data" => $addresses
        ]);
    }

    public function updateAddress()
    {
        $data = json_decode(file_get_contents("php://input"), true) ?: $_REQUEST;
        $addressId = $data['id'] ?? null;

        if (empty($addressId) || empty($data['user_id'])) {
            echo json_encode([
                "status" => false,
                "message" => "Address ID and user ID are required"
            ]);
            return;
        }

        $success = $this->address->updateAddress($addressId, $data);

        echo json_encode([
            "status" => $success,
            "message" => $success
                ? "Address updated successfully"
                : "Unable to update address"
        ]);
    }

    public function deleteAddress()
    {
        $data = json_decode(file_get_contents("php://input"), true) ?: $_REQUEST;
        $addressId = $data['id'] ?? null;
        $userId = $data['user_id'] ?? null;

        if (empty($addressId)) {
            echo json_encode([
                "status" => false,
                "message" => "Address ID is required"
            ]);
            return;
        }

        $success = $this->address->deleteAddress($addressId, $userId);

        echo json_encode([
            "status" => $success,
            "message" => $success
                ? "Address deleted successfully"
                : "Unable to delete address"
        ]);
    }
}
