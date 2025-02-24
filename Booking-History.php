<?php
session_start();
require_once('include/config.php');

// Check if user is logged in
if (empty($_SESSION["uid"])) {   
    header('location:login.php');
    exit;
}
$uid = $_SESSION['uid'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>User | Booking History</title>
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
    <?php include 'include/header.php'; ?>
    <!-- Header Section end -->

    <!-- Page top Section -->
    <section class="page-top-section set-bg" data-setbg="img/page-top-bg.jpg">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 m-auto text-white">
                    <h2>Booking History</h2>
                </div>
            </div>
        </div>
    </section>
    <!-- Page top Section end -->

    <!-- Booking History Section -->
    <section class="contact-page-section spad overflow-hidden">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <form id="payment-form" method="post" action="process_payment.php">
                        <table class="table table-bordered">
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
                                    <th>Pay</th>
                                    <th>Invoice</th>
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
                                            CASE WHEN IFNULL(SUM(t3.amount), 0) >= t2.Price THEN 'Paid' ELSE 'Pending' END as payment_status
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

                                    if ($query->rowCount() > 0) {
                                        foreach ($results as $result) {
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
                                        <td>
                                            <?php echo $result->balance_due > 0 ? 'KES ' . number_format($result->balance_due, 2) : 'Nil'; ?>
                                        </td>
                                        <td>
                                            <?php if ($result->status === 'Approved' && $result->balance_due > 0): ?>
                                                <a href="payment_page.php?booking_id=<?php echo htmlentities($result->bookingid); ?>&price=<?php echo htmlentities($result->balance_due); ?>" class="btn btn-success btn-sm">Pay</a>
                                            <?php else: ?>
                                                <span>N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($result->payment_status === 'Paid'): ?>
                                                <a href="generate_invoice.php?packages=<?php echo htmlentities($result->bookingid); ?>" class="btn btn-info btn-sm">Generate Invoice</a>
                                            <?php else: ?>
                                                <span>No Invoice</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php  
                                            $cnt++; 
                                        } 
                                    } else {
                                        echo "<tr><td colspan='13'>No records found.</td></tr>";
                                    }
                                } catch (PDOException $e) {
                                    echo "Error: " . $e->getMessage();
                                }
                                ?>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!-- Booking History Section end -->

    <!-- Footer Section -->
    <?php include 'include/footer.php'; ?>
    <!-- Footer Section end -->

    <div class="back-to-top"><img src="img/icons/up-arrow.png" alt=""></div>

    <!--====== Javascripts & Jquery ======-->
    <script src="js/vendor/jquery-3.2.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
