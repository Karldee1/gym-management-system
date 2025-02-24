<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('include/config.php');

// Check if user is logged in
if (strlen($_SESSION["uid"]) == 0) {   
    header('location:login.php');
    exit;
} else {
    $uid = $_SESSION['uid'];
}

// Check if booking IDs are passed via GET method
if (!isset($_GET['packages']) || empty($_GET['packages'])) {
    die('No bookings selected.');
}

// Convert selected package IDs into an array
$bookingIds = explode(',', $_GET['packages']);

try {
    // Create placeholders for the SQL query
    $placeholders = implode(',', array_fill(0, count($bookingIds), '?'));

    // SQL query to fetch booking details and payment status
    $sql = "SELECT t1.id as bookingid, t1.booking_date as bookingdate, 
            t2.titlename as title, t2.PackageDuration as PackageDuration,
            t2.Price as Price, t2.Description as Description, 
            t4.category_name as category_name, t5.PackageName as PackageName, 
            t1.status as status,
            SUM(COALESCE(t3.amount, 0)) as total_paid
            FROM tblbooking as t1
            JOIN tbladdpackage as t2 ON t1.package_id = t2.id
            LEFT JOIN tblpayment as t3 ON t1.id = t3.booking_id
            JOIN tblcategory as t4 ON t2.category = t4.id
            JOIN tblpackage as t5 ON t2.PackageType = t5.id
            WHERE t1.id IN ($placeholders) AND t1.userid = ?
            GROUP BY t1.id, t2.Price";

    // Prepare and execute the SQL query
    $query = $dbh->prepare($sql);
    $query->execute(array_merge($bookingIds, [$uid]));
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    // If no results are found, terminate the script
    if (empty($results)) {
        die('No bookings found.');
    }

    // Initialize variables to calculate total price, total paid, and check payment status
    $totalPrice = 0;
    $totalPaid = 0;

    foreach ($results as $result) {
        $totalPrice += $result->Price; // Total price of all bookings
        $totalPaid += $result->total_paid; // Total amount paid so far
    }

    $balanceDue = $totalPrice - $totalPaid; // Calculate balance due

    // Generate HTML for the invoice
    $html = "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Invoice</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
            }
            .container {
                width: 80%;
                margin: auto;
            }
            header {
                text-align: center;
                padding: 20px;
                border-bottom: 2px solid #333;
                background-color: orange;
                color: white;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            header h1 {
                margin: 0;
                font-size: 24px;
                text-transform: uppercase;
                letter-spacing: 2px;
            }
            header .nav {
                display: flex;
                align-items: center;
            }
            header .nav a {
                color: white;
                text-decoration: none;
                margin-left: 15px;
                font-size: 16px;
            }
            header .nav a:hover {
                text-decoration: underline;
            }
            .company-details {
                text-align: left;
                margin-bottom: 20px;
            }
            .company-details h2 {
                margin: 0;
            }
            .company-details p {
                margin: 0;
                color: #555;
            }
            .invoice-details {
                text-align: right;
                margin-bottom: 20px;
            }
            .invoice-details h2 {
                margin: 0;
                color: #007bff;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            table, th, td {
                border: 1px solid #ddd;
            }
            th, td {
                padding: 10px;
                text-align: left;
            }
            th {
                background-color: #f4f4f4;
            }
            .total, .balance {
                text-align: right;
                padding: 20px 0;
            }
            .total h3, .balance h3 {
                margin: 0;
                font-size: 18px;
            }
            .signature {
                text-align: right;
                margin-top: 40px;
                margin-right: 30px;
            }
            .signature p {
                margin: 0;
            }
            footer {
                text-align: center;
                margin-top: 40px;
                color: #555;
            }
            .print-btn {
                margin: 20px 0;
                text-align: right;
            }
            .print-btn button {
                background-color: orange;
                color: white;
                border: none;
                padding: 10px 20px;
                font-size: 16px;
                cursor: pointer;
            }
            .print-btn button:hover {
                background-color: darkorange;
            }
            @media print {
                header, .print-btn {
                    display: none;
                }
                .container {
                    width: 100%;
                    margin: 0;
                    padding: 0;
                }
                table {
                    border: 1px solid #000;
                }
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <header>
                <h1>ONLINE BOOKING SYSTEM</h1>
                <nav class='nav'>
                    <a href='Booking-History.php' class='nav-link'>
                        <span class='material-icons'></span> Back
                    </a>
                    <a href='change_password.php' class='nav-link'>
                        <span class='material-icons'></span> Change Password
                    </a>
                    <a href='logout.php' class='nav-link'>
                        <span class='material-icons'>logout</span> Logout
                    </a>
                </nav>
            </header>

            <div class='company-details'>
                <h2>Gym Booking</h2>
                <p>123 Main St, City, Kenya</p>
                <p>+123 456 789</p>
                <p>email@example.com</p>
            </div>

            <div class='invoice-details'>
                <h2>Invoice #12345</h2>
                <p>Date: " . date('Y-m-d') . "</p>
                <p>Due Date: " . date('Y-m-d', strtotime('+14 days')) . "</p>
            </div>

            <div class='print-btn'>
                <button onclick='window.print()'>Print Invoice</button>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Qty</th>
                        <th>Description</th>
                        <th>Unit Price</th>
                        <th>Amount Paid</th>
                    </tr>
                </thead>
                <tbody>";

    // Loop through each booking and display details in the table
    foreach ($results as $result) {
        $html .= "
                    <tr>
                        <td>1</td>
                        <td>{$result->title} - {$result->PackageDuration} days</td>
                        <td>KES {$result->Price}</td>
                        <td>KES {$result->total_paid}</td>
                    </tr>";
    }

    $html .= "
                </tbody>
            </table>

            <div class='total'>
                <h3>Total: KES $totalPrice</h3>
            </div>

            <div class='balance'>
                <h3>Balance Due: KES $balanceDue</h3>
            </div>

            <div class='signature'>
                <p>Authorized Signature</p>
                <p><img src='path_to_signature_image.png' alt='Signature' width='100'></p>
            </div>

            <footer>
                <p>Thank you for your business!</p>
                <p>Terms & Conditions: Payment is due within 14 days.</p>
            </footer>
        </div>
    </body>
    </html>";

    echo $html;

} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
