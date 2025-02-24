<?php
session_start();
require_once('include/config.php');

if (strlen($_SESSION["uid"]) == 0) {
    header('location:login.php');
} else {
    $uid = $_SESSION['uid'];
    $bookingid = intval($_GET['bookingid']);

    $sql = "SELECT t1.id as bookingid, t3.fname as Name, t3.email as email, t1.booking_date as bookingdate, t2.titlename as title, t2.PackageDuratiobn as PackageDuration, t2.Price as Price, t2.Description as Description, t4.category_name as category_name, t5.PackageName as PackageName, t1.status as status
            FROM tblbooking as t1
            JOIN tbladdpackage as t2 ON t1.package_id = t2.id
            JOIN tbluser as t3 ON t1.userid = t3.id
            JOIN tblcategory as t4 ON t2.category = t4.id
            JOIN tblpackage as t5 ON t2.PackageType = t5.id
            WHERE t1.id = :bookingid AND t1.userid = :uid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':bookingid', $bookingid, PDO::PARAM_INT);
    $query->bindParam(':uid', $uid, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    if ($result) {
        $sqlPayment = "SELECT * FROM tblpayment WHERE bookingID = :bookingid";
        $queryPayment = $dbh->prepare($sqlPayment);
        $queryPayment->bindParam(':bookingid', $bookingid, PDO::PARAM_INT);
        $queryPayment->execute();
        $payments = $queryPayment->fetchAll(PDO::FETCH_OBJ);
        $totalPayment = 0;
    }
?>

<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>User | Booking Details</title>
    <meta charset="UTF-8">
    <meta name="description" content="Ahana Yoga HTML Template">
    <meta name="keywords" content="yoga, html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/font-awesome.min.css"/>
    <link rel="stylesheet" href="css/owl.carousel.min.css"/>
    <link rel="stylesheet" href="css/nice-select.css"/>
    <link rel="stylesheet" href="css/slicknav.min.css"/>
    <!-- Main Stylesheets -->
    <link rel="stylesheet" href="css/style.css"/>
</head>
<body>
    <!-- Header Section -->
    <?php include 'include/header.php'; ?>
    <!-- Page top Section -->
    <section class="page-top-section set-bg" data-setbg="img/page-top-bg.jpg">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 m-auto text-white">
                    <h2>Booking Details</h2>
                </div>
            </div>
        </div>
    </section>
    <!-- Contact Section -->
    <section class="contact-page-section spad overflow-hidden">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <?php if ($result) { ?>
                        <table class="table table-bordered">
                            <tr>
                                <th>Booking Date</th>
                                <td><?php echo htmlentities($result->bookingdate); ?></td>
                                <th>Name</th>
                                <td><?php echo htmlentities($result->Name); ?></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><?php echo htmlentities($result->email); ?></td>
                                <th>Category</th>
                                <td><?php echo htmlentities($result->category_name); ?></td>
                            </tr>
                            <tr>
                                <th>Package Name</th>
                                <td><?php echo htmlentities($result->PackageName); ?></td>
                                <th>Title</th>
                                <td><?php echo htmlentities($result->title); ?></td>
                            </tr>
                            <tr>
                                <th>Package Duration</th>
                                <td><?php echo htmlentities($result->PackageDuration); ?></td>
                                <th>Price</th>
                                <td><?php echo htmlentities($result->Price); ?></td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td colspan="3"><?php echo htmlentities($result->Description); ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td colspan="3"><?php echo htmlentities($result->status); ?></td>
                            </tr>
                        </table>

                        <?php if ($payments) { ?>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th colspan="3" style="text-align:center;font-size:20px;">Payment History</th>
                                    </tr>
                                    <tr>
                                        <th>Payment Type</th>
                                        <th>Amount Paid</th>
                                        <th>Payment Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment) { ?>
                                        <tr>
                                            <td><?php echo htmlentities($payment->paymentType); ?></td>
                                            <td><?php echo htmlentities($payment->payment); ?></td>
                                            <td><?php echo htmlentities($payment->payment_date); ?></td>
                                        </tr>
                                        <?php $totalPayment += $payment->payment; ?>
                                    <?php } ?>
                                    <tr>
                                        <th>Total</th>
                                        <th><?php echo htmlentities($totalPayment); ?></th>
                                        <th></th>
                                    </tr>
                                </tbody>
                            </table>
                        <?php } else { ?>
                            <p>No payments made yet.</p>
                        <?php } ?>
                    <?php } else { ?>
                        <p>No booking found.</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
    <!-- Footer Section -->
    <?php include 'include/footer.php'; ?>
    <div class="back-to-top"><img src="img/icons/up-arrow.png" alt=""></div>
    <!-- Javascripts & Jquery -->
    <script src="js/vendor/jquery-3.2.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.slicknav.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jquery.nice-select.min.js"></script>
    <script src="js/jquery-ui.min.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
<?php } ?>
