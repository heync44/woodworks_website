<?php
session_start();

// Sample products
$products = [
    ["id" => 1, "name" => "wooden bed", "price" => 20000],
    ["id" => 2, "name" => "wooden table", "price" => 30000],
    ["id" => 3, "name" => "cozy bed", "price" => 40000],
];

// Initialize cart session if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle adding products to cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'])) {
    $productId = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    foreach ($products as $product) {
        if ($product["id"] == $productId) {
            $found = false;

            // Check if product is already in cart
            foreach ($_SESSION['cart'] as &$cartItem) {
                if ($cartItem["id"] == $productId) {
                    $cartItem["quantity"] += $quantity; // Update quantity
                    $found = true;
                    break;
                }
            }

            // If not found, add new product to cart
            if (!$found) {
                $_SESSION['cart'][] = [
                    "id" => $product["id"],
                    "name" => $product["name"],
                    "price" => $product["price"],
                    "quantity" => $quantity,
                ];
            }
            break;
        }
    }
}

// Handle deleting products from cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];
    $_SESSION['cart'] = array_filter($_SESSION['cart'], function ($item) use ($deleteId) {
        return $item['id'] != $deleteId;
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Shop Products</h2>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4">
                    <div class="card p-3 shadow-lg">
                        <h5><?php echo $product["name"]; ?></h5>
                        <p>Price: $<?php echo $product["price"]; ?></p>
                        <form method="post">
                            <input type="hidden" name="product_id" value="<?php echo $product["id"]; ?>">
                            <input type="number" name="quantity" class="form-control mb-2" value="1" min="1" required>
                            <button type="submit" class="btn btn-primary">Add to Cart</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-5">
            <h4>Cart Items</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $item) {
                            echo "<tr>
                                    <td>{$item['name']}</td>
                                    <td>{$item['quantity']}</td>
                                    <td>\${$item['price']}</td>
                                    <td>
                                        <form method='post' style='display:inline;'>
                                            <input type='hidden' name='delete_id' value='{$item['id']}'>
                                            <button type='submit' class='btn btn-danger btn-sm'>Delete</button>
                                        </form>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center'>Cart is empty</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4 text-center">
            <a href="order.php" class="btn btn-success">Proceed to Order</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>