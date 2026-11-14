<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Change as needed
$password = ""; // Change as needed
$dbname = "woodworks"; 

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Checkout Form</h2>
        <form action="" method="post" class="card p-4 shadow-lg">
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="phone" name="phone" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
            </div>

            <h4 class="mt-4">Products in Cart</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                        foreach($_SESSION['cart'] as $item) {
                            echo "<tr>
                                    <td>{$item['name']}</td>
                                    <td>{$item['quantity']}</td>
                                    <td>\${$item['price']}</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3' class='text-center'>No products in cart</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <div class="mb-3">
                <label class="form-label">Payment Method</label>
                <select class="form-select" id="payment-method" name="payment" required>
                    <option value="">Select Payment Method</option>
                    <option value="Debit Card">Debit Card</option>
                    <option value="GPay">GPay</option>
                    <option value="Cash on Delivery">Cash on Delivery</option>
                </select>
            </div>

            <div id="card-details" class="mb-3" style="display: none;">
                <label class="form-label">Card Details</label>
                <input type="text" class="form-control mb-2" name="card_number" placeholder="Card Number (16 digits)">
                <input type="text" class="form-control mb-2" name="expiry_date" placeholder="Expiry Date (MM/YY)">
                <input type="text" class="form-control mb-2" name="cvv" placeholder="CVV (3-4 digits)">
                <input type="text" class="form-control mb-2" name="cardholder_name" placeholder="Cardholder's Name">
            </div>

            <div id="gpay-details" class="mb-3 text-center" style="display: none;">
                <label class="form-label">Scan to Pay with GPay</label>
                <img src="gpay_qr.png" alt="GPay QR Code" class="img-fluid" style="max-width: 200px;">
            </div>

            <div class="d-flex justify-content-between">
                <a href="cart.php" class="btn btn-secondary">Shop More</a>
                <button type="submit" name="place_order" class="btn btn-primary">Place Order</button>
            </div>
        </form>
    </div>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])) {
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $address = $_POST['address'];
        $payment_method = $_POST['payment'];

        $card_number = isset($_POST['card_number']) ? $_POST['card_number'] : null;
        $expiry_date = isset($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
        $cvv = isset($_POST['cvv']) ? $_POST['cvv'] : null;
        $cardholder_name = isset($_POST['cardholder_name']) ? $_POST['cardholder_name'] : null;
        
        $total_price = 0;
        $order_details = "";
        
        // Check if cart is not empty
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                $order_details .= "{$item['name']} ({$item['quantity']} x \${$item['price']}), ";
                $total_price += $item['quantity'] * $item['price'];
            }
        } else {
            die("No products in cart.");
        }
        
        // If payment method is NOT card, clear card details
        if ($payment_method !== "Card") {
            $card_number = null;
            $expiry_date = null;
            $cvv = null;
            $cardholder_name = null;
        } else {
            // Store only the last 4 digits of the card number for security
            $card_number = substr($card_number, -4);
            $cvv = "***"; // Mask the CVV completely
        }
        
        // Prepare the SQL query
        $sql = "INSERT INTO orders (name, phone, email, address, payment_method, card_number, expiry_date, cvv, cardholder_name, total_price, order_details) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param(
                "sssssssssss", 
                $name, $phone, $email, $address, $payment_method, 
                $card_number, $expiry_date, $cvv, $cardholder_name, 
                $total_price, $order_details
            );
        
            if ($stmt->execute()) {
                echo "Order placed successfully!";
            } else {
                echo "Error: " . $stmt->error;
            }
            
            $stmt->close();
        } else {
            die("Query preparation failed: " . $conn->error);
        }
        
            if ($stmt->execute()) {
                echo "<script>alert('Order placed successfully!'); window.location.href = 'thank_you.php';</script>";
                unset($_SESSION['cart']); // Clear cart after placing order
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            die("SQL Error: " . $conn->error);
        }
    
    ?>
</body>
</html>
