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
            (product_id, user_id, rating, title, comment, status)
            VALUES (?, ?, ?, ?, ?, 'pending')";

    $stmt = $this->conn->prepare($sql);

    $title = $data['title'] ?? '';
    $comment = $data['comment'] ?? '';

    $stmt->bind_param(
        "iiiss",
        $data['product_id'],
        $data['user_id'],
        $data['rating'],
        $title,
        $comment
    );

    return $stmt->execute();
}
    // User - Accepted Reviews Only
    public function getReviews($productId)
    {
        $sql = "SELECT
                    r.id,
                    r.rating,
                    r.title,
                    r.comment,
                    r.status,
                    r.created_at,
                    u.name AS user_name
                FROM reviews r
                LEFT JOIN users u
                    ON r.user_id = u.id
                WHERE r.product_id = ?
                AND r.status = 'accepted'
                ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $productId);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Admin - All Reviews
    public function getAllReviews()
    {
        $sql = "SELECT
                    r.id,
                    r.product_id,
                    r.user_id,
                    r.rating,
                    r.title,
                    r.comment,
                    r.status,
                    r.created_at,
                    u.name AS user_name
                FROM reviews r
                LEFT JOIN users u
                    ON r.user_id = u.id
                ORDER BY r.created_at DESC";

        $result = $this->conn->query($sql);

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Delete Review
    public function deleteReview($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM {$this->table} WHERE id = ?"
        );

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

public function updateReviewStatus($id, $status)
{
    $sql = "UPDATE reviews
            SET status = ?
            WHERE id = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("si", $status, $id);

    return $stmt->execute();
}

}