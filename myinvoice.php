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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: orange;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #fff;
        }
        header .logo {
            font-size: 20px;
            font-weight: bold;
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
        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
        }
        header {
            border-bottom: 2px solid #333;
        }
        header h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .invoice-details, .company-details {
            margin-bottom: 20px;
        }
        .company-details {
            text-align: left;
        }
        .invoice-details {
            text-align: right;
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
        .total {
            text-align: right;
            padding: 20px 0;
        }
        .total h3 {
            margin: 0;
            font-size: 18px;
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
    <header>
        <div class="logo">Online Booking System</div>
        <nav class="nav">
            <a href="Booking-history.php" class="nav-link">
                <span class="material-icons">account_circle</span> Home
            </a>
            <a href="change_password.php" class="nav-link">
                <span class="material-icons">brightness_7</span> Change Password
            </a>
            <a href="logout.php" class="nav-link">
                <span class="material-icons">logout</span> Logout
            </a>
        </nav>
    </header>

    <div class="container">
        <header>
            <h1>Online Booking System</h1>
        </header>

        <div class="company-details">
            <h2>Company Name</h2>
            <p>123 Main St, City, Country</p>
            <p>+123 456 789</p>
            <p>email@example.com</p>
        </div>

        <div class="invoice-details">
            <h2>Invoice</h2>
            <p>Date: <?php echo date('Y-m-d'); ?></p>
            <p>Due Date: <?php echo date('Y-m-d', strtotime('+14 days')); ?></p>
        </div>

        <div class="print-btn">
            <button onclick="window.print()">Print Invoice</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Sr.No</th>
                    <th>Booking Date</th>
                    <th>Title</th>
                    <th>Package Duration</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Category Name</th>
                    <th>Package Name</th>
                    <th>Status</th>
                    <th>Payment Status</th>
                    <th>Balance Due</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    // SQL Query to fetch booking details with payment check and balance calculation
                    $sql = "SELECT t1.id as bookingid, t1.booking_date as bookingdate, 
                            t2.titlename as title, t2.PackageDuration as PackageDuration, 
                            t2.Price as Price, t2.Description as Description, 
                            t4.category_name as category_name, t5.PackageName as PackageName, 
                            t1.status as status, 
                            IFNULL(SUM(t3.amount), 0) as total_paid,
                            (t2.Price - IFNULL(SUM(t3.amount), 0)) as balance_due,
                            CASE 
                                WHEN IFNULL(SUM(t3.amount), 0) >= t2.Price THEN 'Paid' 
                                ELSE 'Pending' 
                            END as payment_status
                            FROM tblbooking as t1
                            JOIN tbladdpackage as t2 ON t1.package_id = t2.id
                            LEFT JOIN tblpayment as t3 ON t1.id = t3.booking_id
                            JOIN tblcategory as t4 ON t2.category = t4.id
                            JOIN tblpackage as t5 ON t2.PackageType = t5.id
                            WHERE t1.userid = :uid
                            GROUP BY t1.id";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':uid', $uid, PDO::PARAM_INT);
                    $query->execute();

                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                    $cnt = 1;
                    $totalPrice = 0;

                    if ($query->rowCount() > 0) {
                        foreach ($results as $result) {
                            $totalPrice += $result->Price;
                            ?>
                            <tr>
                                <td><?php echo($cnt);?></td>
                                <td><?php echo htmlentities($result->bookingdate);?></td>
                                <td><?php echo htmlentities($result->title);?></td>
                                <td><?php echo htmlentities($result->PackageDuration);?></td>
                                <td><?php echo htmlentities($result->Price);?></td>
                                <td><?php echo htmlentities($result->Description);?></td>
                                <td><?php echo htmlentities($result->category_name);?></td>
                                <td><?php echo htmlentities($result->PackageName);?></td>
                                <td><?php echo htmlentities($result->status);?></td>
                                <td><?php echo htmlentities($result->payment_status);?></td>
                                <td><?php echo $result->balance_due > 0 ? 'KES ' . number_format($result->balance_due, 2) : 'Nil'; ?></td>
                            </tr>
                            <?php  
                            $cnt++; 
                        } 
                    } else {
                        echo "<tr><td colspan='11'>No records found.</td></tr>";
                    }
                } catch (PDOException $e) {
                    echo "<tr><td colspan='11'>Error: " . $e->getMessage() . "</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="total">
            <h3>Total: KES <?php echo number_format($totalPrice, 2); ?></h3>
        </div>

        <footer>
            <p>Thank you for your business!</p>
            <p>Terms & Conditions: Payment is due within 14 days.</p>
        </footer>
    </div>

    <!-- Include Material Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
</body>
</html>
