<?php

require_once __DIR__ . '/../models/DeliveryCharge.php';

class DeliveryChargeController
{
    private $deliveryCharge;

    public function __construct()
    {
        $this->deliveryCharge = new DeliveryCharge();
    }

    public function addDeliveryCharge()
    {
        $delivery_charge = $_POST['delivery_charge'] ?? 0;
        $minimum_package = $_POST['minimum_package'] ?? 0;

        echo json_encode(
            $this->deliveryCharge->add(
                $delivery_charge,
                $minimum_package
            )
        );
    }

    public function getDeliveryCharge()
    {
        echo json_encode(
            $this->deliveryCharge->getAll()
        );
    }

    public function updateDeliveryCharge()
    {
        $id = $_POST['id'] ?? 0;
        $delivery_charge = $_POST['delivery_charge'] ?? 0;
        $minimum_package = $_POST['minimum_package'] ?? 0;

        echo json_encode(
            $this->deliveryCharge->update(
                $id,
                $delivery_charge,
                $minimum_package
            )
        );
    }

    public function deleteDeliveryCharge()
    {
        $id = $_POST['id'] ?? 0;

        echo json_encode(
            $this->deliveryCharge->delete($id)
        );
    }
}
?>