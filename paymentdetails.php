<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('include/config.php');

// Check if user is logged in
if(strlen($_SESSION["uid"]) == 0) {   
    header('location:login.php');
    exit;
} else {
    $uid = $_SESSION['uid'];
}

// Process payment if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['selected_packages']) && isset($_POST['total_price'])) {
        $selectedPackages = $_POST['selected_packages'];
        $totalPrice = $_POST['total_price'];

        try {
            // Begin transaction
            $dbh->beginTransaction();

            // Insert payment details
            $paymentSql = "INSERT INTO tblpayment (user_id, amount, payment_date) VALUES (:uid, :amount, NOW())";
            $paymentStmt = $dbh->prepare($paymentSql);
            $paymentStmt->bindParam(':uid', $uid, PDO::PARAM_INT);
            $paymentStmt->bindParam(':amount', $totalPrice, PDO::PARAM_STR);
            $paymentStmt->execute();

            // Get the last inserted payment ID
            $paymentId = $dbh->lastInsertId();

            // Update selected bookings with payment ID
            $updateBookingSql = "UPDATE tblbooking SET payment_id = :payment_id WHERE id = :booking_id";
            $updateBookingStmt = $dbh->prepare($updateBookingSql);

            foreach ($selectedPackages as $bookingId) {
                $updateBookingStmt->bindParam(':payment_id', $paymentId, PDO::PARAM_INT);
                $updateBookingStmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
                $updateBookingStmt->execute();
            }

            // Commit transaction
            $dbh->commit();

            // Redirect to a success page or display a success message
            header('Location: payment_success.php');
            exit;

        } catch (PDOException $e) {
            // Rollback transaction on error
            $dbh->rollBack();
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Invalid submission. Ensure all required fields are filled.";
    }
} else {
    echo "Invalid request method.";
}
?>
