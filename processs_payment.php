<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = 'localhost';
$dbname = 'gymdb';
$user = 'root';
$pass = '';

try {
    $dbh = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection error: ' . $e->getMessage());
}

// Check if user is logged in
if (strlen($_SESSION["uid"]) == 0) {   
    header('location:login.php');
    exit;
} else {
    $uid = $_SESSION['uid'];
}

// Check if required POST parameters are set
if (!isset($_POST['payment_amount']) || !isset($_POST['total_price']) || !isset($_POST['bookingids'])) {
    die('Missing required parameters.');
}

$paymentAmount = floatval($_POST['payment_amount']);
$totalPrice = floatval($_POST['total_price']);
$bookingIds = explode(',', $_POST['bookingids']);

try {
    // Validate payment amount
    if ($paymentAmount <= 0 || $paymentAmount > $totalPrice) {
        die('Invalid payment amount.');
    }

    // Start a transaction
    $dbh->beginTransaction();
    
    foreach ($bookingIds as $bookingId) {
        // Insert payment record including user id
        $sql = "INSERT INTO tblpayment (booking_id, amount, userid) VALUES (:bookingid, :amount, :userid)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':bookingid', $bookingId, PDO::PARAM_INT);
        $query->bindParam(':amount', $paymentAmount, PDO::PARAM_STR);
        $query->bindParam(':userid', $uid, PDO::PARAM_INT); // Bind user id here
        $query->execute();

        // Update booking status
        $sql = "UPDATE tblbooking SET payment_id = LAST_INSERT_ID(), status = 'Paid' WHERE id = ?";
        $query = $dbh->prepare($sql);
        $query->execute([$bookingId]);
    }

    $dbh->commit();

    // Generate receipt
    $balanceRemaining = $totalPrice - $paymentAmount;
    $receiptHtml = "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Receipt</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .container { width: 80%; margin: auto; }
            header { 
                text-align: center; 
                padding: 20px; 
                border-bottom: 2px solid #333; 
                background-color: orange; 
                color: white; 
            }
            header h1 { margin: 0; }
            .receipt-details { margin: 20px 0; }
            .total-price { font-size: 1.2em; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <header>
                <h1>ONLINE BOOKING SYSTEM</h1>
                <p>Receipt Date: " . date('Y-m-d') . "</p>
            </header>
            <div class='receipt-details'>
                <h2>Receipt</h2>
                <p>Total Price: KES $totalPrice</p>
                <p>Payment Amount: KES $paymentAmount</p>
                <p>Balance Remaining: KES $balanceRemaining</p>
                <p>Thank you for your payment!</p>
            </div>
        </div>
        <script>
            window.print();
        </script>
    </body>
    </html>";

    // Output the receipt HTML content
    echo $receiptHtml;

} catch (PDOException $e) {
    $dbh->rollBack();
    die('Error: ' . $e->getMessage());
}
?>
