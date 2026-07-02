<?php

class Review
{
    private $conn;
    private $table = "reviews";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Add Review
    public function addReview($data)
{
    $sql = "INSERT INTO reviews
            (product_id, user_id, rating, comment)
            VALUES (?, ?, ?, ?)";

    $stmt = $this->conn->prepare($sql);

    if (!$stmt) {
        die($this->conn->error);
    }

    $stmt->bind_param(
        "iiis",
        $data['product_id'],
        $data['user_id'],
        $data['rating'],
        $data['comment']
    );

    return $stmt->execute();
}

    // Get Product Reviews
   public function getReviewsByProduct($productId)
{
    $sql = "SELECT
                r.id,
                r.rating,
                r.comment,
                r.created_at,
                u.name AS user_name
            FROM reviews r
            JOIN users u
                ON r.user_id = u.id
            WHERE r.product_id = ?
            ORDER BY r.created_at DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();

    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

    // Delete Review
    public function deleteReview($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM {$this->table} WHERE id = ?"
        );

        return $stmt->execute([$id]);
    }
}