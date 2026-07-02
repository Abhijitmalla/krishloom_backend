<?php

require_once __DIR__ . '/../config/database.php';

class Order
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // ===========================================
    // CHECKOUT
    // ===========================================
    public function checkout($data)
    {
        $user_id = $data['user_id'];

        try {

$this->conn->begin_transaction();
            // Get cart items
         $cartQuery = "
    SELECT
        c.product_id,
        c.quantity,
        p.selling_price
    FROM cart c
    INNER JOIN products p
        ON c.product_id = p.id
    WHERE c.user_id = ?
";

           $stmt = $this->conn->prepare($cartQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$cartItems = $result->fetch_all(MYSQLI_ASSOC);

            if (count($cartItems) == 0) {

                return [
                    "status" => false,
                    "message" => "Cart is empty"
                ];
            }

            // Calculate Total
            $total = 0;

            foreach ($cartItems as $item) {
$total += $item['selling_price'] * $item['quantity'];            }

            // Insert Order
            $orderSql = "
                INSERT INTO orders
                (
                    user_id,
                    total_amount,
                    payment_status,
                    order_status
                )
                VALUES
                (?, ?, 'Pending', 'Pending')
            ";

          $stmt = $this->conn->prepare($orderSql);
$stmt->bind_param("id", $user_id, $total);
$stmt->execute();

$order_id = $this->conn->insert_id;

            // Insert Order Items
            $itemSql = "
                INSERT INTO order_items
                (
                    order_id,
                    product_id,
                    quantity,
                    price
                )
                VALUES
                (?, ?, ?, ?)
            ";

            $itemStmt = $this->conn->prepare($itemSql);

            foreach ($cartItems as $item) {

              $product_id = $item['product_id'];
$quantity = $item['quantity'];
$price = $item['selling_price'];

$itemStmt->bind_param(
    "iiid",
    $order_id,
    $product_id,
    $quantity,
    $price
);

$itemStmt->execute();
            }

            // Clear Cart
            $deleteCart = $this->conn->prepare("DELETE FROM cart WHERE user_id=?");
$deleteCart->bind_param("i", $user_id);
$deleteCart->execute();
            $this->conn->commit();

            return [
                "status" => true,
                "message" => "Order placed successfully",
                "order_id" => $order_id
            ];

        } catch (Exception $e) {

$this->conn->rollback();
            return [
                "status" => false,
                "message" => $e->getMessage()
            ];
        }
    }
public function placeOrder($data)
{
    $orderNo = 'KL' . time();

    $discount = $data['coupon_discount'] ?? 0;
    $totalAmount = $data['total'] ?? 0;

    $shipping = $data['shipping_address'] ?? [];

    if (empty($shipping)) {
        echo json_encode(["status" => false, "message" => "Shipping address is required"]);
        return;
    }

    $stmt = $this->conn->prepare("
        INSERT INTO orders
        (
            user_id,
            order_number,
            payment_method,
            subtotal,
            delivery_charge,
            discount,
            total_amount,
            shipping_name,
            shipping_phone,
            shipping_address,
            shipping_city,
            shipping_state,
            shipping_pincode
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "issddddssssss",
        $data['user_id'],
        $orderNo,
        $data['payment_method'],
        $data['subtotal'],
        $data['delivery_charge'],
        $discount,
        $totalAmount,
        $shipping['name'],
        $shipping['phone'],
        $shipping['address'],
        $shipping['city'],
        $shipping['state'],
        $shipping['pincode']
    );

    if ($stmt->execute()) {

        $orderId = $this->conn->insert_id;

        $itemStmt = $this->conn->prepare("
            INSERT INTO order_items
            (
                order_id,
                product_id,
                quantity,
                price
            )
            VALUES (?, ?, ?, ?)
        ");

        foreach ($data['items'] as $item) {

            $productId = $item['product_id'] ?? $item['id'];
            $quantity = $item['quantity'];
            $price = $item['selling_price'];

            $itemStmt->bind_param(
                "iiid",
                $orderId,
                $productId,
                $quantity,
                $price
            );

            $itemStmt->execute();
        }

        return [
            "status" => true,
            "order_id" => $orderId,
            "order_number" => $orderNo,
            
        ];
    }

    return [
        "status" => false,
        "message" => $stmt->error
    ];
}
    // ===========================================
    // GET USER ORDERS
    // ===========================================
    public function getOrdersByUser($user_id)
    {
        $sql = "
            SELECT o.*,
                   (SELECT p.product_title FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = o.id LIMIT 1) as first_product_title,
                   (SELECT p.main_image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = o.id LIMIT 1) as first_product_image,
                   (SELECT COUNT(id) FROM order_items WHERE order_id = o.id) as total_items
            FROM orders o
            WHERE o.user_id = ?
            ORDER BY o.id DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ===========================================
    // GET ALL ORDERS (ADMIN)
    // ===========================================
    public function getAllOrders()
    {
        $sql = "
            SELECT o.*,
                   u.name as user_name,
                   u.phone as user_phone,
                   (SELECT p.product_title FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = o.id LIMIT 1) as first_product_title,
                   (SELECT p.main_image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = o.id LIMIT 1) as first_product_image,
                   (SELECT COUNT(id) FROM order_items WHERE order_id = o.id) as total_items
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            ORDER BY o.id DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ===========================================
    // UPDATE ORDER STATUS (ADMIN)
    // ===========================================
    public function updateOrderStatus($orderId, $status)
    {
        $sql = "UPDATE orders SET order_status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        
        $stmt->bind_param("si", $status, $orderId);
        return $stmt->execute();
    }

    // ===========================================
    // DELETE ORDER (ADMIN)
    // ===========================================
    public function deleteOrder($orderId)
    {
        // First delete order_items to maintain referential integrity if there's no ON DELETE CASCADE
        $itemSql = "DELETE FROM order_items WHERE order_id = ?";
        $itemStmt = $this->conn->prepare($itemSql);
        if ($itemStmt) {
            $itemStmt->bind_param("i", $orderId);
            $itemStmt->execute();
        }

        $sql = "DELETE FROM orders WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        
        $stmt->bind_param("i", $orderId);
        return $stmt->execute();
    }

    // ===========================================
    // GET ORDER DETAILS
    // ===========================================
    public function getOrderDetails($order_id)
    {

        $orderSql = "
            SELECT *
            FROM orders
            WHERE id=?
        ";

        $stmt = $this->conn->prepare($orderSql);
        $stmt->bind_param("i", $order_id);
$stmt->execute();

$result = $stmt->get_result();
$order = $result->fetch_assoc();

        $itemsSql = "
            SELECT
                oi.id,
                oi.product_id,
                oi.quantity,
                oi.price,
               p.product_title,
                p.main_image
            FROM order_items oi
            INNER JOIN products p
                ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ";

        $stmt = $this->conn->prepare($itemsSql);
        $stmt->bind_param("i", $order_id);
$stmt->execute();

$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);

        return [
            "order" => $order,
            "products" => $products
        ];
    }
}