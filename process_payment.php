<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('include/config.php');

// Check if user is logged in
if(strlen($_SESSION["uid"]) == 0) {   
    header('location:login.php');
    exit;
}

$bookingId = $_GET['booking_id'];
$price = $_GET['price'];

?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>Payment</title>
    <meta charset="UTF-8">
    <meta name="description" content="Gym Management System Payment">
    <meta name="keywords" content="gym, payment">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/style.css"/>
</head>
<body>
    <!-- Header Section -->
    <?php include 'include/header.php';?>
    <!-- Header Section end -->

    <!-- Payment Section -->
    <section class="payment-section spad">
        <div class="container">
            <h2>Payment</h2>
            <form id="payment-form" method="post" action="process_payment.php">
                <input type="hidden" name="booking_id" value="<?php echo htmlentities($bookingId); ?>">
                <input type="hidden" name="price" value="<?php echo htmlentities($price); ?>">

                <div class="form-group">
                    <label for="payment_amount">Enter Amount:</label>
                    <input type="number" id="payment_amount" name="payment_amount" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="payment_method">Select Payment Method:</label>
                    <select id="payment_method" name="payment_method" class="form-control" required>
                        <option value="">Select...</option>
                        <option value="M-Pesa">M-Pesa</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Debit Card">Debit Card</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Submit Payment</button>
                </div>
            </form>

            <div id="balance-info" class="mt-4">
                <p>Balance: <span id="balance"></span></p>
            </div>
        </div>
    </section>
    <!-- Payment Section end -->

    <!-- Footer Section -->
    <?php include 'include/footer.php'; ?>
    <!-- Footer Section end -->

    <div class="back-to-top"><img src="img/icons/up-arrow.png" alt=""></div>

    <!--====== Javascripts & Jquery ======-->
    <script src="js/vendor/jquery-3.2.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        // JavaScript to calculate balance
        document.getElementById('payment_amount').addEventListener('input', function() {
            const price = parseFloat('<?php echo htmlentities($price); ?>');
            const amountPaid = parseFloat(this.value) || 0;
            const balance = (price - amountPaid).toFixed(2);
            document.getElementById('balance').textContent = balance;
        });
    </script>
</body>
</html>
