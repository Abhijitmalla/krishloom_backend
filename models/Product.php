<?php

require_once __DIR__ . '/../config/database.php';

class Product
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // ADD PRODUCT
    public function addProduct($data)
    {
        $sql = "
            INSERT INTO products (
                product_code,
                product_title,
                product_type,
                product_category,
                manufacturer,
                hsn_code,
                mrp,
                selling_price,
                basic_price,
                gst_percent,
                gst_amount,
                sizes,
                colors,
                features,
                description,
                status,
                main_image
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->conn->prepare($sql);
        $params = [
            $data[':product_code'],
            $data[':product_title'],
            $data[':product_type'],
            $data[':product_category'],
            $data[':manufacturer'],
            $data[':hsn_code'],
            $data[':mrp'],
            $data[':selling_price'],
            $data[':basic_price'],
            $data[':gst_percent'],
            $data[':gst_amount'],
            $data[':sizes'],
            $data[':colors'],
            $data[':features'],
            $data[':description'],
            $data[':status'],
            $data[':main_image']
        ];

        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        return $this->conn->insert_id;
    }

  public function addGalleryProduct($parentId, $image, $data)
{
    $sql = "
        INSERT INTO products (
            parent_product_id,
            image_type,
            product_code,
            product_title,
            product_type,
            product_category,
            manufacturer,
            hsn_code,
            mrp,
            selling_price,
            basic_price,
            gst_percent,
            gst_amount,
            sizes,
            colors,
            features,
            description,
            status,
            main_image
        ) VALUES (?, 'gallery', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $this->conn->prepare($sql);
    $params = [
        $parentId,
        $data[':product_code'],
        $data[':product_title'],
        $data[':product_type'],
        $data[':product_category'],
        $data[':manufacturer'],
        $data[':hsn_code'],
        $data[':mrp'],
        $data[':selling_price'],
        $data[':basic_price'],
        $data[':gst_percent'],
        $data[':gst_amount'],
        $data[':sizes'],
        $data[':colors'],
        $data[':features'],
        $data[':description'],
        $data[':status'],
        $image
    ];

    $types = 'i' . str_repeat('s', 17);
    $stmt->bind_param($types, ...$params);

    return $stmt->execute();
}

 



    // GET PRODUCT GALLERY
   public function getGallery($productId)
{
    $stmt = $this->conn->prepare("
        SELECT *
        FROM products
        WHERE parent_product_id = ?
        AND image_type = 'gallery'
        ORDER BY id ASC
    ");

    $stmt->bind_param("i", $productId);
    $stmt->execute();

    $result = $stmt->get_result();

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

    // UPDATE PRODUCT
   public function updateProduct($data)
{
    $sql = "
    UPDATE products
    SET
        product_code=?,
        product_title=?,
        product_type=?,
        product_category=?,
        manufacturer=?,
        hsn_code=?,
        mrp=?,
        selling_price=?,
        basic_price=?,
        gst_percent=?,
        gst_amount=?,
        sizes=?,
        colors=?,
        features=?,
        description=?,
        status=?,
        main_image=?
    WHERE id=?
    ";

    $stmt = $this->conn->prepare($sql);

    $stmt->bind_param(
        "ssssssdddddssssssi",
        $data[':product_code'],
        $data[':product_title'],
        $data[':product_type'],
        $data[':product_category'],
        $data[':manufacturer'],
        $data[':hsn_code'],
        $data[':mrp'],
        $data[':selling_price'],
        $data[':basic_price'],
        $data[':gst_percent'],
        $data[':gst_amount'],
        $data[':sizes'],
        $data[':colors'],
        $data[':features'],
        $data[':description'],
        $data[':status'],
        $data[':main_image'],
        $data[':id']
    );

    $stmt->execute();

    // Update all gallery rows
    $sql = "
    UPDATE products
    SET
        product_code=?,
        product_title=?,
        product_type=?,
        product_category=?,
        manufacturer=?,
        hsn_code=?,
        mrp=?,
        selling_price=?,
        basic_price=?,
        gst_percent=?,
        gst_amount=?,
        sizes=?,
        colors=?,
        features=?,
        description=?,
        status=?
    WHERE parent_product_id=?
    ";

    $stmt = $this->conn->prepare($sql);

    $stmt->bind_param(
        "ssssssdddddsssssi",
        $data[':product_code'],
        $data[':product_title'],
        $data[':product_type'],
        $data[':product_category'],
        $data[':manufacturer'],
        $data[':hsn_code'],
        $data[':mrp'],
        $data[':selling_price'],
        $data[':basic_price'],
        $data[':gst_percent'],
        $data[':gst_amount'],
        $data[':sizes'],
        $data[':colors'],
        $data[':features'],
        $data[':description'],
        $data[':status'],
        $data[':id']
    );

    return $stmt->execute();
}

  public function updateGalleryImage($id, $image)
{
    $stmt = $this->conn->prepare("
        UPDATE products
        SET main_image = ?
        WHERE id = ?
        AND image_type='gallery'
    ");

    $stmt->bind_param("si", $image, $id);

    return $stmt->execute();
}

    // DELETE PRODUCT
   public function deleteProduct($id)
{
    // Delete gallery images
    $stmt = $this->conn->prepare("
        DELETE FROM products
        WHERE parent_product_id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Delete main product
    $stmt = $this->conn->prepare("
        DELETE FROM products
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);

    return $stmt->execute();
}

    // DELETE SINGLE GALLERY IMAGE
   public function deleteGalleryImage($id)
{
    $stmt = $this->conn->prepare("
        DELETE FROM products
        WHERE id = ?
        AND image_type='gallery'
    ");

    $stmt->bind_param("i", $id);

    return $stmt->execute();
}

    // GET ALL PRODUCTS
    public function getProducts()
    {
        $sql = "
            SELECT *
            FROM products
            ORDER BY id DESC
        ";

        $result = $this->conn->query($sql);

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // GET SINGLE PRODUCT
    public function getProduct($id)
    {
        $stmt = $this->conn->prepare("
            SELECT *
            FROM products
            WHERE id = ?
        ");

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result ? $result->fetch_assoc() : null;
    }
}