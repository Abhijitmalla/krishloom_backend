<?php

require_once __DIR__ . '/../models/Order.php';

class OrderController
{
    private $order;

    public function __construct()
    {
        $this->order = new Order();
    }

    // ===============================
    // CHECKOUT
    // ===============================
    public function checkout()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['user_id'])) {
            echo json_encode([
                "status" => false,
                "message" => "User ID is required"
            ]);
            return;
        }

        $result = $this->order->checkout($data);

        echo json_encode($result);
    }
public function placeOrder($data)
{
    $result = $this->order->placeOrder($data);

    echo json_encode($result);
}

    public function downloadInvoice()
    {
        $orderId = $_GET['order_id'] ?? null;

        if (!$orderId) {
            http_response_code(400);
            echo json_encode([
                'status' => false,
                'message' => 'Order ID is required'
            ]);
            return;
        }

        $success = $this->order->downloadInvoice($orderId);

        if ($success === false) {
            http_response_code(404);
            echo json_encode([
                'status' => false,
                'message' => 'Invoice not found'
            ]);
        }
    }
    // ===============================
    // GET USER ORDERS
    // ===============================
    public function getOrdersByUser($userId)
    {
        $result = $this->order->getOrdersByUser($userId);

        echo json_encode([
            "status" => true,
            "orders" => $result
        ]);
    }

    // ===============================
    // GET ORDER DETAILS
    // ===============================
    public function getOrderDetails($orderId)
    {
        $result = $this->order->getOrderDetails($orderId);

        echo json_encode([
            "status" => true,
            "data" => $result
        ]);
    }

    // ===============================
    // GET ALL ORDERS
    // ===============================
    public function getAllOrders()
    {
        $result = $this->order->getAllOrders();

        echo json_encode([
            "status" => true,
            "orders" => $result
        ]);
    }

    // ===============================
    // UPDATE ORDER STATUS
    // ===============================
    public function updateOrderStatus()
    {
        $data = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        
        $orderId = $data['order_id'] ?? null;
        $status = $data['status'] ?? null;

        if (!$orderId || !$status) {
            echo json_encode([
                "status" => false,
                "message" => "Order ID and status are required"
            ]);
            return;
        }

        $success = $this->order->updateOrderStatus($orderId, $status);

        echo json_encode([
            "status" => $success,
            "message" => $success ? "Order status updated successfully" : "Failed to update order status"
        ]);
    }

    // ===============================
    // DELETE ORDER
    // ===============================
    public function deleteOrder()
    {
        $data = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        $orderId = $data['order_id'] ?? null;

        if (!$orderId) {
            echo json_encode([
                "status" => false,
                "message" => "Order ID is required"
            ]);
            return;
        }

        $success = $this->order->deleteOrder($orderId);

        echo json_encode([
            "status" => $success,
            "message" => $success ? "Order deleted successfully" : "Failed to delete order"
        ]);
    }
}