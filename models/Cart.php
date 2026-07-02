<?php

require_once __DIR__ . '/../config/database.php';

class Cart
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Add to Cart
    public function addCart($userId, $productId, $quantity)
    {
        // Check existing product
        $stmt = $this->conn->prepare("
            SELECT id, quantity
            FROM cart
            WHERE user_id=? AND product_id=?
        ");
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $row = $result->fetch_assoc();

            $newQty = $row['quantity'] + $quantity;

            $stmt = $this->conn->prepare("
                UPDATE cart
                SET quantity=?
                WHERE id=?
            ");

            $stmt->bind_param("ii", $newQty, $row['id']);

            return $stmt->execute();
        }

        $stmt = $this->conn->prepare("
            INSERT INTO cart(user_id,product_id,quantity)
            VALUES(?,?,?)
        ");

        $stmt->bind_param("iii", $userId, $productId, $quantity);

        return $stmt->execute();
    }

    // Get Cart
   public function getCart($userId)
{
    $stmt = $this->conn->prepare("
        SELECT
            c.id AS cart_id,
            c.quantity,
            p.*
        FROM cart c
        INNER JOIN products p
            ON c.product_id = p.id
        WHERE c.user_id = ?
    ");

    $stmt->bind_param("i", $userId);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

    // Update Quantity
    public function updateQuantity($userId,$productId,$quantity)
    {
        $stmt=$this->conn->prepare("
            UPDATE cart
            SET quantity=?
            WHERE user_id=? AND product_id=?
        ");

        $stmt->bind_param("iii",$quantity,$userId,$productId);

        return $stmt->execute();
    }

    // Remove Cart Item
    public function removeCart($userId,$productId)
    {
        $stmt=$this->conn->prepare("
            DELETE FROM cart
            WHERE user_id=? AND product_id=?
        ");

        $stmt->bind_param("ii",$userId,$productId);

        return $stmt->execute();
    }

    // Clear Cart
    public function clearCart($userId)
    {
        $stmt=$this->conn->prepare("
            DELETE FROM cart
            WHERE user_id=?
        ");

        $stmt->bind_param("i",$userId);

        return $stmt->execute();
    }
}