<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

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

     $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($orderId, 5, '0', STR_PAD_LEFT);

$invoiceDir = __DIR__ . '/../uploads/invoices/';
$qrDir = __DIR__ . '/../uploads/qrcodes/';

if (!is_dir($qrDir)) {
    mkdir($qrDir, 0777, true);
}

$qrFile = $invoiceNumber . '.png';
$qrPath = $qrDir . $qrFile;

/*
Data that will be inside the QR
*/
$qrData =
'http://localhost/krishloom-vastram/backend/routes/orderApi.php?route=getOrderDetails&order_id='
. $orderId;

$builder = new Builder(
    writer: new PngWriter(),
    data: $qrData,
    size: 250,
    margin: 10
);
$result = $builder->build();

$result->saveToFile($qrPath);

$qrDbPath = 'uploads/qrcodes/' . $qrFile;

if (!is_dir($invoiceDir)) {
    mkdir($invoiceDir, 0777, true);
}


$fileName = $invoiceNumber . '.svg';
$filePath = $invoiceDir . $fileName;

$invoiceItems = $this->prepareInvoiceItems($data['items']);
$this->createInvoiceImage(
    $filePath,
    [
        'invoice_number' => $invoiceNumber,
        'order_number' => $orderNo,
        'qr_code' => $qrDbPath,
        'order_date' => date('d M Y'),
        'payment_method' => $data['payment_method'],
        'subtotal' => $data['subtotal'],
        'delivery_charge' => $data['delivery_charge'],
        'discount' => $discount,
        'total_amount' => $totalAmount,
        'shipping' => $shipping,
        'items' => $invoiceItems
    ]
);
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

        $update = $this->conn->prepare("
    UPDATE orders
    SET
        invoice_number = ?,
        qr_code = ?
    WHERE id = ?
");
$update->bind_param(
    "ssi",
    $invoiceNumber,
    $qrDbPath,
    $orderId
);

$update->execute();

      return [
    "status" => true,
    "order_id" => $orderId,
    "order_number" => $orderNo,
    "invoice_number" => $invoiceNumber,
    "qr_code" => $qrPath
];
    }

    return [
        "status" => false,
        "message" => $stmt->error
    ];
}

private function prepareInvoiceItems($items)
{
    $prepared = [];

    foreach ($items as $item) {
        $productId = $item['product_id'] ?? $item['id'];
        $title = $item['product_title'] ?? $item['title'] ?? $item['name'] ?? null;

        if (!$title) {
            $titleStmt = $this->conn->prepare("SELECT product_title FROM products WHERE id = ? LIMIT 1");
            if ($titleStmt) {
                $titleStmt->bind_param("i", $productId);
                $titleStmt->execute();
                $result = $titleStmt->get_result();
                $product = $result->fetch_assoc();
                $title = $product['product_title'] ?? 'Product #' . $productId;
            }
        }

        $quantity = (int) ($item['quantity'] ?? 1);
        $price = (float) ($item['selling_price'] ?? $item['price'] ?? 0);

        $prepared[] = [
            'title' => $title ?: 'Product #' . $productId,
            'quantity' => $quantity,
            'price' => $price,
            'total' => $quantity * $price
        ];
    }

    return $prepared;
}

private function createInvoiceImage($filePath, $invoice)
{
    $width = 1200;
    $rowHeight = 52;
    $itemRows = max(1, count($invoice['items']));
    $height = max(1050, 760 + ($itemRows * $rowHeight));

    $svg = [];
    $svg[] = '<?xml version="1.0" encoding="UTF-8"?>';
    $svg[] = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">';
    $svg[] = '<style>
        .brand{font:700 34px Arial,sans-serif;fill:#581c87}
        .title{font:700 34px Arial,sans-serif;fill:#1f2937}
        .h{font:700 18px Arial,sans-serif;fill:#1f2937}
        .text{font:400 16px Arial,sans-serif;fill:#374151}
        .muted{font:400 15px Arial,sans-serif;fill:#6b7280}
        .small{font:400 13px Arial,sans-serif;fill:#6b7280}
        .thead{font:700 16px Arial,sans-serif;fill:#ffffff}
        .amount{font:700 21px Arial,sans-serif;fill:#581c87}
    </style>';
    $svg[] = '<rect width="1200" height="' . $height . '" fill="#ffffff"/>';
    $svg[] = '<rect width="1200" height="18" fill="#7e3af2"/>';
    $svg[] = '<text x="70" y="86" class="brand">KRISHLOOM VASTRAM</text>';
    $svg[] = '<text x="70" y="120" class="muted">Handloom Sarees and Ethnic Wear</text>';
    $svg[] = '<text x="930" y="86" class="title">INVOICE</text>';
    $svg[] = '<text x="930" y="120" class="muted">' . $this->escapeSvg($invoice['invoice_number']) . '</text>';
    $svg[] = '<line x1="70" y1="145" x2="1130" y2="145" stroke="#e2e8f0"/>';

    $shippingLines = [
        $invoice['shipping']['name'] ?? '',
        'Phone: ' . ($invoice['shipping']['phone'] ?? ''),
        $invoice['shipping']['address'] ?? '',
        trim(($invoice['shipping']['city'] ?? '') . ', ' . ($invoice['shipping']['state'] ?? '') . ' ' . ($invoice['shipping']['pincode'] ?? ''))
    ];
    $svg[] = $this->infoBlockSvg(70, 180, 'Bill To', $shippingLines);
    $svg[] = $this->infoBlockSvg(650, 180, 'Order Details', [
        'Order No: ' . $invoice['order_number'],
        'Invoice Date: ' . $invoice['order_date'],
        'Payment: ' . $invoice['payment_method'],
        'Status: Pending'
    ]);

    $tableY = 405;
    $svg[] = '<rect x="70" y="' . $tableY . '" width="1060" height="48" rx="6" fill="#581c87"/>';
    $svg[] = '<text x="95" y="' . ($tableY + 31) . '" class="thead">Item</text>';
    $svg[] = '<text x="740" y="' . ($tableY + 31) . '" class="thead">Qty</text>';
    $svg[] = '<text x="835" y="' . ($tableY + 31) . '" class="thead">Price</text>';
    $svg[] = '<text x="1010" y="' . ($tableY + 31) . '" class="thead">Total</text>';

    $currentY = $tableY + 48;
    foreach ($invoice['items'] as $index => $item) {
        if ($index % 2 === 0) {
            $svg[] = '<rect x="70" y="' . $currentY . '" width="1060" height="' . $rowHeight . '" fill="#f8fafc"/>';
        }
        $svg[] = '<text x="95" y="' . ($currentY + 32) . '" class="text">' . $this->escapeSvg($this->fitText($item['title'], 68)) . '</text>';
        $svg[] = '<text x="750" y="' . ($currentY + 32) . '" class="text">' . (int) $item['quantity'] . '</text>';
        $svg[] = '<text x="835" y="' . ($currentY + 32) . '" class="text">' . $this->escapeSvg($this->money($item['price'])) . '</text>';
        $svg[] = '<text x="1010" y="' . ($currentY + 32) . '" class="text">' . $this->escapeSvg($this->money($item['total'])) . '</text>';
        $svg[] = '<line x1="70" y1="' . ($currentY + $rowHeight) . '" x2="1130" y2="' . ($currentY + $rowHeight) . '" stroke="#e2e8f0"/>';
        $currentY += $rowHeight;
    }

    $summaryX = 730;
    $summaryY = $currentY + 48;
    $svg[] = $this->amountLineSvg($summaryX, $summaryY, 'Subtotal', $invoice['subtotal']);
    $svg[] = $this->amountLineSvg($summaryX, $summaryY + 38, 'Delivery', $invoice['delivery_charge']);
    $svg[] = $this->amountLineSvg($summaryX, $summaryY + 76, 'Discount', -1 * (float) $invoice['discount']);
    $svg[] = '<line x1="' . $summaryX . '" y1="' . ($summaryY + 118) . '" x2="1130" y2="' . ($summaryY + 118) . '" stroke="#e2e8f0"/>';
    $svg[] = '<text x="' . $summaryX . '" y="' . ($summaryY + 155) . '" class="h">Grand Total</text>';
    $svg[] = '<text x="1010" y="' . ($summaryY + 155) . '" class="amount">' . $this->escapeSvg($this->money($invoice['total_amount'])) . '</text>';

    $svg[] = '<line x1="70" y1="' . ($height - 125) . '" x2="1130" y2="' . ($height - 125) . '" stroke="#e2e8f0"/>';
    $svg[] = '<text x="70" y="' . ($height - 92) . '" class="text">Thank you for shopping with Krishloom Vastram.</text>';
    $svg[] = '<text x="70" y="' . ($height - 62) . '" class="small">This is a computer generated invoice.</text>';
    if (!empty($invoice['qr_code'])) {

    $qrImage =
        'http://localhost/krishloom-vastram/backend/' .
        $invoice['qr_code'];

    $svg[] = '<image
                x="900"
                y="' . ($height - 300) . '"
                width="150"
                height="150"
                href="' . $qrImage . '" />';

    $svg[] = '<text
                x="915"
                y="' . ($height - 120) . '"
                class="small">
                Scan Order
              </text>';
}
    $svg[] = '</svg>';

    file_put_contents($filePath, implode("\n", $svg));
}

private function infoBlockSvg($x, $y, $title, $lines)
{
    $svg = [];
    $svg[] = '<rect x="' . $x . '" y="' . $y . '" width="480" height="170" rx="8" fill="#f8fafc" stroke="#e2e8f0"/>';
    $svg[] = '<text x="' . ($x + 22) . '" y="' . ($y + 38) . '" class="h">' . $this->escapeSvg($title) . '</text>';
    $lineY = $y + 55;
    foreach ($lines as $lineText) {
        if (trim($lineText) === '') {
            continue;
        }
        $svg[] = '<text x="' . ($x + 22) . '" y="' . ($lineY + 22) . '" class="muted">' . $this->escapeSvg($this->fitText($lineText, 58)) . '</text>';
        $lineY += 26;
    }

    return implode("\n", $svg);
}

private function amountLineSvg($x, $y, $label, $amount)
{
    return '<text x="' . $x . '" y="' . $y . '" class="muted">' . $this->escapeSvg($label) . '</text>'
        . '<text x="1010" y="' . $y . '" class="text">' . $this->escapeSvg($this->money($amount)) . '</text>';
}

private function fitText($text, $maxChars)
{
    $text = trim((string) $text);
    if (strlen($text) <= $maxChars) {
        return $text;
    }

    return substr($text, 0, max(0, $maxChars - 3)) . '...';
}

private function escapeSvg($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

private function money($amount)
{
    return 'Rs. ' . number_format((float) $amount, 2);
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
