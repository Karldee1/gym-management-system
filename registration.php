<?php
error_reporting(0);
require_once('include/config.php');

if(isset($_POST['submit'])) { 
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    $state = trim($_POST['state']);
    $city = trim($_POST['city']);
    $Password = trim($_POST['password']);
    $pass = md5($Password);
    $RepeatPassword = trim($_POST['RepeatPassword']);

    // Email id Already Exists
    $usermatch = $dbh->prepare("SELECT mobile, email FROM tbluser WHERE (email=:usreml OR mobile=:mblenmbr)");
    $usermatch->execute(array(':usreml' => $email, ':mblenmbr' => $mobile)); 
    $row = $usermatch->fetch(PDO::FETCH_ASSOC);
    $usrdbeml = $row['email'] ?? null;
    $usrdbmble = $row['mobile'] ?? null;

    // Validation
    if (empty($fname) || !preg_match("/^[a-zA-Z]+$/", $fname)) {
        $error = "First Name must contain only letters";
    } elseif (empty($lname) || !preg_match("/^[a-zA-Z]+$/", $lname)) {
        $error = "Last Name must contain only letters";
    } elseif (empty($mobile)) {
        $error = "Please Enter Mobile No";
    } elseif (empty($email)) {
        $error = "Please Enter Email";
    } elseif ($email == $usrdbeml || $mobile == $usrdbmble) {
        $error = "Email Id or Mobile Number Already Exists!";
    } elseif (empty($state) || !preg_match("/^[a-zA-Z]+$/", $state)) {
        $error = "State must contain only letters";
    } elseif (empty($city) || !preg_match("/^[a-zA-Z]+$/", $city)) {
        $error = "City must contain only letters";
    } elseif (empty($Password) || empty($RepeatPassword)) {
        $error = "Password and Confirm Password cannot be empty!";
    } elseif ($Password != $RepeatPassword) {
        $error = "Password and Confirm Password do not match!";
    } else {
        $sql = "INSERT INTO tbluser (fname, lname, email, mobile, state, city, password) 
                VALUES (:fname, :lname, :email, :mobile, :state, :city, :Password)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':fname', $fname, PDO::PARAM_STR);
        $query->bindParam(':lname', $lname, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':mobile', $mobile, PDO::PARAM_STR);
        $query->bindParam(':state', $state, PDO::PARAM_STR);
        $query->bindParam(':city', $city, PDO::PARAM_STR);
        $query->bindParam(':Password', $pass, PDO::PARAM_STR);

        $query->execute();
        $lastInsertId = $dbh->lastInsertId();
        if ($lastInsertId > 0) {
            echo "<script>alert('Registration successful. Please login');</script>";
            echo "<script> window.location.href='login.php';</script>";
        } else {
            $error = "Registration not successful";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>Gym Management System</title>
    <meta charset="UTF-8">
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/font-awesome.min.css"/>
    <link rel="stylesheet" href="css/owl.carousel.min.css"/>
    <link rel="stylesheet" href="css/nice-select.css"/>
    <link rel="stylesheet" href="css/slicknav.min.css"/>

    <!-- Main Stylesheets -->
    <link rel="stylesheet" href="css/style.css"/>

    <script>
        function validateForm() {
            let fname = document.getElementById("fname").value;
            let lname = document.getElementById("lname").value;
            let mobile = document.getElementById("mobile").value;
            let email = document.getElementById("email").value;
            let state = document.getElementById("state").value;
            let city = document.getElementById("city").value;
            let password = document.getElementById("password").value;
            let repeatPassword = document.getElementById("RepeatPassword").value;

            if (!/^[a-zA-Z]+$/.test(fname)) {
                alert("First Name must contain only letters");
                return false;
            }
            if (!/^[a-zA-Z]+$/.test(lname)) {
                alert("Last Name must contain only letters");
                return false;
            }
            if (!/^[a-zA-Z]+$/.test(state)) {
                alert("State must contain only letters");
                return false;
            }
            if (!/^[a-zA-Z]+$/.test(city)) {
                alert("City must contain only letters");
                return false;
            }
            if (mobile == "" || isNaN(mobile) || mobile.length != 10) {
                alert("Enter a valid 10-digit mobile number");
                return false;
            }
            if (email == "" || !validateEmail(email)) {
                alert("Enter a valid email address");
                return false;
            }
            if (password == "" || repeatPassword == "") {
                alert("Password and Confirm Password cannot be empty");
                return false;
            }
            if (password !== repeatPassword) {
                alert("Passwords do not match");
                return false;
            }
            return true;
        }

        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(String(email).toLowerCase());
        }
    </script>
</head>
<body>
    <!-- Page Preloder -->

    <!-- Header Section -->
    <?php include 'include/header.php';?>
    <!-- Header Section end -->
                                                                              
    <!-- Page top Section -->
    <section class="page-top-section set-bg" data-setbg="img/page-top-bg.jpg">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 m-auto text-white">
                    <h2>Registration</h2>
                </div>
            </div>
        </div>
    </section>
    <!-- Page top Section end -->

    <!-- Contact Section -->
    <section class="contact-page-section spad overflow-hidden">
        <div class="container">
            
            <div class="row">
                <div class="col-lg-2">
                </div>
                <div class="col-lg-8">
                    <?php if ($error) { ?><div class="errorWrap"><strong>ERROR</strong>: <?php echo htmlentities($error); ?> </div><?php } 
                    else if ($succmsg) { ?><div class="succWrap"><strong>SUCCESS</strong>: <?php echo htmlentities($succmsg); ?> </div><?php } ?><br><br>
                    <form class="singup-form contact-form" method="post" onsubmit="return validateForm()">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" name="fname" id="fname" placeholder="First Name" autocomplete="off" value="<?php echo $fname;?>" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="lname" id="lname" placeholder="Last Name" autocomplete="off" value="<?php echo $lname;?>" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="email" id="email" placeholder="Your Email" autocomplete="off" value="<?php echo $email;?>" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="mobile" id="mobile" maxlength="10" placeholder="Mobile Number" autocomplete="off" value="<?php echo $mobile;?>" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="state" id="state" placeholder="Your State" autocomplete="off" value="<?php echo $state;?>" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="city" id="city" placeholder="Your City" autocomplete="off" value="<?php echo $city;?>" required>
                            </div>
                            <div class="col-md-6">
                                <input type="password" name="password" id="password" placeholder="Password" autocomplete="off">
                            </div>
                            <div class="col-md-6">
                                <input type="password" name="RepeatPassword" id="RepeatPassword" placeholder="Confirm Password" autocomplete="off" required>
                            </div>
                            <div class="col-md-4">
                                <input type="submit" id="submit" name="submit" value="Register Now" class="site-btn sb-gradient">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-2">
                </div>
            </div>
        </div>
    </section>
    <!-- Trainers Section end -->

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

<style>
.errorWrap {
    padding: 10px;
    margin: 0 0 20px 0;
    background: #dd3d36;
    color: #fff;
    -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
}
.succWrap {
    padding: 10px;
    margin: 0 0 20px 0;
    background: #5cb85c;
    color: #fff;
    -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
}
</style>
