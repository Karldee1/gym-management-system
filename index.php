<?php 
session_start();
error_reporting(0);
include 'include/config.php';

$uid = $_SESSION['uid'];

if(isset($_POST['submit'])) { 
    $pid = $_POST['pid'];
    $booking_date = date('Y-m-d H:i:s'); // Get the current date and time

    try {
        $sql = "INSERT INTO tblbooking (package_id, userid, booking_date) VALUES (:pid, :uid, :booking_date)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':pid', $pid, PDO::PARAM_STR);
        $query->bindParam(':uid', $uid, PDO::PARAM_STR);
        $query->bindParam(':booking_date', $booking_date, PDO::PARAM_STR);
        $query->execute();
        echo "<script>alert('Package has been booked.');</script>";
        echo "<script>window.location.href='booking-history.php'</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . htmlspecialchars($e->getMessage()) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>Gym Management System</title>
    <meta charset="UTF-8">
    <meta name="description" content="Ahana Yoga HTML Template">
    <meta name="keywords" content="yoga, html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/font-awesome.min.css"/>
    <link rel="stylesheet" href="css/owl.carousel.min.css"/>
    <link rel="stylesheet" href="css/nice-select.css"/>
    <link rel="stylesheet" href="css/magnific-popup.css"/>
    <link rel="stylesheet" href="css/slicknav.min.css"/>
    <link rel="stylesheet" href="css/animate.css"/>
    <!-- Main Stylesheets -->
    <link rel="stylesheet" href="css/style.css"/>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            background-color: #f8f9fa;
        }
        .page-top-section {
            background: linear-gradient(135deg, #ff7e5f, #feb47b);
            padding: 60px 0;
            text-align: center;
            color: white;
        }

        .pricing-section {
            padding: 60px 0;
            background-color: #f7f7f7;
        }

        .section-title {
            margin-bottom: 50px;
        }

        .section-title h2 {
            font-size: 36px;
            color: #ff7e5f;
        }

        .section-title p {
            font-size: 16px;
            color: #666;
        }

        .pricing-item {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-align: center;
            padding: 30px 20px;
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }

        .pricing-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }

        .pi-top h4 {
            font-size: 24px;
            color: #ff7e5f;
        }

        .pi-price h3 {
            font-size: 32px;
            color: #333;
        }

        .pi-price p {
            font-size: 18px;
            color: #888;
        }

        .site-btn {
            display: inline-block;
            padding: 10px 30px;
            border-radius: 25px;
            background: linear-gradient(135deg, #ff7e5f, #feb47b);
            color: white;
            text-transform: uppercase;
            font-weight: bold;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .site-btn:hover {
            background: linear-gradient(135deg, #feb47b, #ff7e5f);
        }

        .sb-line-gradient {
            border: 2px solid #ff7e5f;
            background: transparent;
            color: #ff7e5f;
        }

        .sb-line-gradient:hover {
            background: #ff7e5f;
            color: white;
        }

        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            cursor: pointer;
        }

        .back-to-top img {
            width: 40px;
        }
    </style>
</head>
<body>
    <!-- Page Preloader -->

    <!-- Header Section -->
    <?php include 'include/header.php';?>
    <!-- Header Section end -->

    <!-- Page top Section -->
    <section class="page-top-section set-bg" data-setbg="img/page-top-bg.jpg">
    <div class="container">
        <div class="row">
            <div class="col-lg-7 m-auto text-white">
                <br><br><br><br><br>
                <h2>Home</h2>
                <p class="slide-in">Stronger Every Day, One Rep at a Time</p>
            </div>
        </div>
    </div>
</section>

<style>
    /* Add this style block in your CSS or inside a <style> tag in the HTML file */

    .slide-in {
        position: relative;
        animation: slideIn 2s ease-in-out;
    }

    @keyframes slideIn {
        from {
            left: -100%;
            opacity: 0;
        }
        to {
            left: 0;
            opacity: 1;
        }
    }
</style>


    <!-- Pricing Section -->
    <section class="pricing-section spad">
        <div class="container">
            <div class="section-title text-center">
                <img src="img/icons/logo-icon.png" alt="">
                <h2>Pricing plans</h2>
                <p>Practice Yoga to perfect physical beauty, take care of your soul and enjoy life more fully!</p>
            </div>
            <div class="row">
                <?php 
                $sql ="SELECT id, category, titlename, PackageType, PackageDuration, Price, uploadphoto, Description, create_date FROM tbladdpackage";
                $query = $dbh->prepare($sql);
                $query->execute();
                $results = $query->fetchAll(PDO::FETCH_OBJ);
                $cnt = 1;
                if($query->rowCount() > 0) {
                    foreach($results as $result) {
                ?>
                <div class="col-lg-3 col-sm-6">
                    <div class="pricing-item begginer">
                        <div class="pi-top">
                            <h4><?php echo htmlspecialchars($result->titlename);?></h4>
                        </div>
                        <div class="pi-price">
                            <h3><?php echo htmlspecialchars($result->Price);?></h3>
                            <p><?php echo htmlspecialchars($result->PackageDuration);?></p>
                        </div>
                        <ul>
                            <?php echo htmlspecialchars($result->Description);?>
                        </ul>
                        <?php if(strlen($_SESSION['uid'])==0): ?>
                        <a href="login.php" class="site-btn sb-line-gradient">Booking Now</a>
                        <?php else :?>
                            <form method='post'>
                                <input type='hidden' name='pid' value='<?php echo htmlspecialchars($result->id);?>'>
                                <input class='site-btn sb-line-gradient' type='submit' name='submit' value='Booking Now' onclick="return confirm('Do you really want to book this package?');"> 
                            </form> 
                        <?php endif;?>
                    </div>
                </div>
                <?php $cnt++; } } ?>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <?php include 'include/footer.php'; ?>
    <!-- Footer Section end -->

    <div class="back-to-top"><img src="img/icons/up-arrow.png" alt=""></div>

    <!--====== Javascripts & Jquery ======-->
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
