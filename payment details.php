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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uid = $_SESSION['uid'];
    $selectedPackages = $_POST['selected_packages'];
    $totalPrice = $_POST['totalPrice'];
    $paymentMethod = 'Bank Transfer'; // or 'M-Pesa' or other methods
    $paymentDetails = 'Details here'; // Update with actual details

    try {
        // Insert payment details
        $sql = "INSERT INTO tblpayment (userid, paymentMethod, paymentDetails, paymentDate) 
                VALUES (:userid, :paymentMethod, :paymentDetails, NOW())";
        $query = $dbh->prepare($sql);
        $query->bindParam(':userid', $uid, PDO::PARAM_INT);
        $query->bindParam(':paymentMethod', $paymentMethod, PDO::PARAM_STR);
        $query->bindParam(':paymentDetails', $paymentDetails, PDO::PARAM_STR);
        $query->execute();
        
        // Get the last inserted payment ID
        $paymentId = $dbh->lastInsertId();

        // Associate selected packages with the payment
        foreach ($selectedPackages as $packageId) {
            $sql = "UPDATE tblbooking SET payment_id = :paymentId WHERE id = :packageId";
            $query = $dbh->prepare($sql);
            $query->bindParam(':paymentId', $paymentId, PDO::PARAM_INT);
            $query->bindParam(':packageId', $packageId, PDO::PARAM_INT);
            $query->execute();
        }

        echo "Payment successful. Your total amount is $totalPrice.";
        // Redirect to a success page or display a message
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
