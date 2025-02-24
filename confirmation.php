<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database configuration file
require_once 'db_config.php';

// Get the booking ID from the URL
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

// Fetch booking details from the database
$sql = "SELECT * FROM tblbooking WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    echo 'Invalid booking ID.';
    exit();
}

// Fetch package details
$package_id = $booking['package_id'];
$sql = "SELECT * FROM tblpackage WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$package_id]);
$package = $stmt->fetch();

// Display confirmation details
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .confirmation-box {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .confirmation-box h2 {
            margin-top: 0;
        }
        .confirmation-box p {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="confirmation-box">
        <h2>Booking Confirmation</h2>
        <p><strong>Package:</strong> <?php echo htmlspecialchars($package['name']); ?></p>
        <p><strong>Booking ID:</strong> <?php echo htmlspecialchars($booking['id']); ?></p>
        <p><strong>Booking Date:</strong> <?php echo htmlspecialchars($booking['booking_date']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($booking['status']); ?></p>
        <p><strong>Total Price:</strong> $<?php echo htmlspecialchars($package['price']); ?></p>
        <p>Thank you for booking with us! We look forward to seeing you.</p>
    </div>
</body>
</html>
