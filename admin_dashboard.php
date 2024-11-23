<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'login_register');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch products
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

// Fetch orders
$sqlOrders = "SELECT * FROM orders";
$resultOrders = $conn->query($sqlOrders);

// Check for form submission for adding a product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['product_image'])) {
    $productName = $_POST['product_name'];
    $productPrice = $_POST['product_price'];
    $productImage = $_FILES['product_image'];

    // Handle image upload
    $imageName = time() . '_' . basename($productImage['name']);
    $targetDir = 'uploads/';
    $targetFile = $targetDir . $imageName;
    
    if (move_uploaded_file($productImage['tmp_name'], $targetFile)) {
        // Insert new product into the database
        $stmt = $conn->prepare("INSERT INTO products (name, price, image) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $productName, $productPrice, $imageName);
        $stmt->execute();
        $stmt->close();
    }
}

// Check for form submission for deleting a product
if (isset($_POST['delete_product'])) {
    $productId = $_POST['product_id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->close();
}

// Check for form submission for updating order status
if (isset($_POST['update_order_status'])) {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['new_status'];
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $orderId);
    $stmt->execute();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Admin Dashboard</h1>
    <h2>Add New Product</h2>
    <form action="admin_dashboard.php" method="post" enctype="multipart/form-data">
        <label for="product_name">Product Name:</label>
        <input type="text" name="product_name" id="product_name" required>

        <label for="product_price">Product Price:</label>
        <input type="number" step="0.01" name="product_price" id="product_price" required>

        <label for="product_image">Product Image:</label>
        <input type="file" name="product_image" id="product_image" required>

        <button type="submit">Add Product</button>
    </form>

    <h2>Product List</h2>
    <div class="product-grid">
        <?php while ($row = $result->fetch_assoc()) { ?>
            <div class="product-item">
                <img src="uploads/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                <h6><?php echo $row['name']; ?></h6>
                <p>$<?php echo $row['price']; ?></p>
                <form action="admin_dashboard.php" method="post">
                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="delete_product">Delete</button>
                </form>
            </div>
        <?php } ?>
    </div>

    <h2>Order List</h2>
    <div class="order-grid">
        <?php while ($order = $resultOrders->fetch_assoc()) { ?>
            <div class="order-item">
                <h6>Order ID: <?php echo $order['id']; ?></h6>
                <p>Status: <?php echo $order['status']; ?></p>
                <form action="admin_dashboard.php" method="post">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <select name="new_status">
                        <option value="Pending">Pending</option>
                        <option value="Shipped">Shipped</option>
                        <option value="Delivered">Delivered</option>
                    </select>
                    <button type="submit" name="update_order_status">Update Status</button>
                </form>
            </div>
        <?php } ?>
    </div>
</body>
</html>

<?php $conn->close(); ?>