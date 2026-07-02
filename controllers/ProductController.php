<?php

require_once __DIR__ . '/../models/Product.php';

class ProductController
{
    private $product;

    public function __construct()
    {
        $this->product = new Product();
    }

    // ===========================
    // ADD PRODUCT
    // ===========================
    public function addProduct()
    {
        // Support both FormData ($_POST) and raw JSON body
        $post = $_POST;
        $rawBody = file_get_contents('php://input');
        if (!empty($rawBody)) {
            $json = json_decode($rawBody, true);
            if (is_array($json)) {
                $post = array_merge($post, $json);
            }
        }

        $uploadDir = __DIR__ . '/../uploads/';
        $mainImage = $post['main_image'] ?? '';

        // Upload Main Image file (overrides JSON path if file is provided)
        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == 0) {

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . '_' . basename($_FILES['main_image']['name']);

            move_uploaded_file(
                $_FILES['main_image']['tmp_name'],
                $uploadDir . $fileName
            );

            $mainImage = 'uploads/' . $fileName;
        }

        $data = [
            ':product_code'    => $post['product_code']    ?? '',
            ':product_title'   => $post['product_title']   ?? '',
            ':product_type'    => $post['product_type']    ?? '',
            ':product_category'=> $post['product_category']?? '',
            ':manufacturer'    => $post['manufacturer']    ?? '',
            ':hsn_code'        => $post['hsn_code']        ?? '',
            ':mrp'             => $post['mrp']             ?? 0,
            ':selling_price'   => $post['selling_price']   ?? 0,
            ':basic_price'     => $post['basic_price']     ?? 0,
            ':gst_percent'     => $post['gst_percent']     ?? 0,
            ':gst_amount'      => $post['gst_amount']      ?? 0,
            ':sizes'           => $post['sizes']           ?? '',
            ':colors'          => $post['colors']          ?? '',
            ':features'        => $post['features']        ?? '',
            ':description'     => $post['description']     ?? '',
            ':status'          => $post['status']          ?? 'Active',
            ':main_image'      => $mainImage
        ];

        try {

            // Save Main Product
            $productId = $this->product->addProduct($data);

            // Save Gallery Images
            if (isset($_FILES['gallery_images'])) {

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp) {

                    if ($_FILES['gallery_images']['error'][$key] == 0) {

                        $galleryFile =
                            time() . "_" . $key . "_" .
                            basename($_FILES['gallery_images']['name'][$key]);

                        move_uploaded_file(
                            $tmp,
                            $uploadDir . $galleryFile
                        );

                        $this->product->addGalleryProduct(
                            $productId,
                            'uploads/' . $galleryFile,
                            $data
                        );
                    }
                }
            }

            echo json_encode([
                'status'  => true,
                'message' => 'Product Added Successfully'
            ]);

        } catch (Exception $e) {

            echo json_encode([
                'status'  => false,
                'message' => $e->getMessage()
            ]);
        }
    }





public function getProducts()
{
    $products = $this->product->getProducts();

    echo json_encode([
        'status' => true,
        'data' => $products
    ]);
}


    // ===========================
    // UPDATE PRODUCT
    // ===========================
public function updateProduct()
{
    $input = $_POST;

    $id = $input['id'] ?? 0;

    if (!$id) {
        echo json_encode([
            'status' => false,
            'message' => 'Product ID is required'
        ]);
        return;
    }

    $product = $this->product->getProduct($id);

    if (!$product) {
        echo json_encode([
            'status' => false,
            'message' => 'Product not found'
        ]);
        return;
    }

    // Keep old image by default
    $mainImage = $product['main_image'];

    // Upload new image if selected
    if (
        isset($_FILES['main_image']) &&
        $_FILES['main_image']['error'] === 0
    ) {
        $uploadDir = __DIR__ . '/../uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . '_' . basename($_FILES['main_image']['name']);

        if (
            move_uploaded_file(
                $_FILES['main_image']['tmp_name'],
                $uploadDir . $fileName
            )
        ) {
            $mainImage = 'uploads/' . $fileName;
        }
    }

    $data = [
        ':id'               => $id,
        ':product_code'     => $input['product_code'] ?? $product['product_code'],
        ':product_title'    => $input['product_title'] ?? $product['product_title'],
        ':product_type'     => $input['product_type'] ?? $product['product_type'],
        ':product_category' => $input['product_category'] ?? $product['product_category'],
        ':manufacturer'     => $input['manufacturer'] ?? $product['manufacturer'],
        ':hsn_code'         => $input['hsn_code'] ?? $product['hsn_code'],
        ':mrp'              => $input['mrp'] ?? $product['mrp'],
        ':selling_price'    => $input['selling_price'] ?? $product['selling_price'],
        ':basic_price'      => $input['basic_price'] ?? $product['basic_price'],
        ':gst_percent'      => $input['gst_percent'] ?? $product['gst_percent'],
        ':gst_amount'       => $input['gst_amount'] ?? $product['gst_amount'],
        ':sizes'            => $input['sizes'] ?? $product['sizes'],
        ':colors'           => $input['colors'] ?? $product['colors'],
        ':features'         => $input['features'] ?? $product['features'],
        ':description'      => $input['description'] ?? $product['description'],
        ':status'           => $input['status'] ?? $product['status'],
        ':main_image'       => $mainImage
    ];

    try {
        $this->product->updateProduct($data);

        echo json_encode([
            'status' => true,
            'message' => 'Product Updated Successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => false,
            'message' => $e->getMessage()
        ]);
    }
}


    // ===========================
    // UPDATE GALLERY IMAGE
    // ===========================
    public function updateGalleryImage()
    {
        $galleryId = $_POST['gallery_id'];

        if (!isset($_FILES['gallery_image'])) {

            echo json_encode([
                'status' => false,
                'message' => 'No Image Selected'
            ]);

            return;
        }

        $uploadDir = __DIR__ . '/../uploads/';

        $fileName =
            time() . "_" .
            basename($_FILES['gallery_image']['name']);

        move_uploaded_file(
            $_FILES['gallery_image']['tmp_name'],
            $uploadDir . $fileName
        );

        $image = 'uploads/' . $fileName;

        $this->product->updateGalleryImage(
            $galleryId,
            $image
        );

        echo json_encode([
            'status' => true,
            'message' => 'Gallery Image Updated'
        ]);
    }


    // ===========================
    // DELETE PRODUCT
    // ===========================
    public function deleteProduct()
    {
        $id = $_POST['id'];

        $this->product->deleteProduct($id);

        echo json_encode([
            'status' => true,
            'message' => 'Product Deleted Successfully'
        ]);
    }


    // ===========================
    // DELETE GALLERY IMAGE
    // ===========================
    public function deleteGalleryImage()
    {
        $id = $_POST['gallery_id'];

        $this->product->deleteGalleryImage($id);

        echo json_encode([
            'status' => true,
            'message' => 'Gallery Image Deleted Successfully'
        ]);
    }
}