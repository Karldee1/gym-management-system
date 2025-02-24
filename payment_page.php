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

$uid = $_SESSION['uid'];
$bookingId = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$price = isset($_GET['price']) ? floatval($_GET['price']) : 0;

// Check if booking ID and price are valid
if ($bookingId <= 0 || $price <= 0) {
    echo "Invalid booking ID or price.";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amountPaid = $_POST['amount_paid'];
    $paymentMethod = $_POST['payment_method'];
    $paymentCode = $_POST['payment_code'];
    
    if (empty($amountPaid) || empty($paymentMethod) || empty($paymentCode)) {
        echo "Please fill in all payment details.";
    } else {
        try {
            // Insert payment details into the database
            $sql = "INSERT INTO tblpayment (userid, amount, paymentMethod, paymentDetails, paymentDate, booking_id) 
                    VALUES (:uid, :amount_paid, :payment_method, :payment_code, NOW(), :booking_id)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':uid', $uid, PDO::PARAM_INT);
            $query->bindParam(':amount_paid', $amountPaid, PDO::PARAM_STR);
            $query->bindParam(':payment_method', $paymentMethod, PDO::PARAM_STR);
            $query->bindParam(':payment_code', $paymentCode, PDO::PARAM_STR);
            $query->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
            $query->execute();

            // Update booking status
            $sqlUpdate = "UPDATE tblbooking SET payment_id = LAST_INSERT_ID() WHERE id = :booking_id";
            $queryUpdate = $dbh->prepare($sqlUpdate);
            $queryUpdate->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
            $queryUpdate->execute();

            // Redirect to generate invoice page
            header('Location: generate_invoice.php?packages=' . $bookingId);
            exit;

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>Payment | Package Booking</title>
    <meta charset="UTF-8">
    <meta name="description" content="Gym Management System">
    <meta name="keywords" content="gym, booking">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/font-awesome.min.css"/>
    <link rel="stylesheet" href="css/style.css"/>
</head>
<body>
    <!-- Header Section -->
    <?php include 'include/header.php';?>
    <!-- Header Section end -->

    <!-- Payment Page Section -->
    <section class="contact-page-section spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 m-auto">
                    <h2 class="text-center">Payment Details</h2>
                    <form method="post" action="">
                        <div class="form-group">
                            <label for="amount">Package Price</label>
                            <input type="text" class="form-control" id="amount" value="<?php echo htmlentities($price); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="amount_paid">Amount Paid</label>
                            <input type="text" class="form-control" id="amount_paid" name="amount_paid" oninput="updateBalance()" required>
                        </div>
                        <div class="form-group">
                            <label for="balance">Balance</label>
                            <input type="text" class="form-control" id="balance" value="<?php echo htmlentities($price); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select class="form-control" id="payment_method" name="payment_method" required>
                                <option value="">Select Payment Method</option>
                                <option value="M-Pesa" data-info="M-Pesa Till Number: 222222">M-Pesa</option>
                                <option value="Credit Card" data-info="Credit Card Number: 636363">Credit Card</option>
                                <option value="Debit Card" data-info="Debit Card Number: 636363">Debit Card</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="payment_code">Payment Code</label>
                            <input type="text" class="form-control" id="payment_code" name="payment_code" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!-- Payment Page Section end -->

    <!-- Footer Section -->
    <?php include 'include/footer.php'; ?>
    <!-- Footer Section end -->

    <!--====== Javascripts & Jquery ======-->
    <script src="js/vendor/jquery-3.2.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

    <script>
        // JavaScript to update balance dynamically
        function updateBalance() {
            const price = parseFloat(document.getElementById('amount').value);
            const amountPaid = parseFloat(document.getElementById('amount_paid').value) || 0;
            const balance = price - amountPaid;
            document.getElementById('balance').value = balance.toFixed(2);
        }

        // Display payment method details based on selection
        document.getElementById('payment_method').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            alert(selectedOption.dataset.info);
        });
    </script>
</body>
</html>
