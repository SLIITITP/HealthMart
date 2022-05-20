<?php session_start(); ?>
<?php
//include database connection
require_once 'conn.php'; ?>
<?php
$itemList = '';
$items = '';
$errors = array();
//check if there is a search term
if (isset($_GET['search'])) {
  $search = mysqli_real_escape_string($conn, $_GET['search']);
  $query = "SELECT * FROM item WHERE (genericName LIKE '%{$search}%' OR brandName LIKE '%{$search}%') AND isDeleted = 0 ORDER BY genericName";

  $items = mysqli_query($conn, $query);
  if ($items) {
    while ($item = mysqli_fetch_assoc($items)) {
      $itemList .= "<a href=\"searchedItem.php?item_ID={$item['itemID']}\">{$item['genericName']} / {$item['brandName']}</a>";
    }
  } else {
    $errors[] = 'Database query failed.';
  }
}
?>
<?php
//checking if a user is logged in
if (!isset($_SESSION['user_id'])) {
  header('location: loginForm.php');
}

$errors = array();
$uName = '';
$uMobileNo = '';
$uAddress = '';
$city = '';

if (isset($_GET['user_ID'])) {
  //getting the user information
  $user_ID = mysqli_real_escape_string($conn, $_GET['user_ID']);
  $query = "SELECT * FROM hmuser WHERE hmUID = {$user_ID} LIMIT 1";

  $result_set = mysqli_query($conn, $query);
  if ($result_set) {
    if (mysqli_num_rows($result_set) == 1) {
      //user found
      $result = mysqli_fetch_assoc($result_set);
      $uName =  $result['uName'];
      $uMobileNo = $result['uMobileNo'];
      $uAddress = $result['uAddress'];
      $city = $result['city'];
    } else {
      //user not found
      header('Location: myaccnew.php?err=user_not_found');
    }
  } else {
    //query unsuccessful
    header('Location: myaccnew.php?err=query_failed');
  }
}
//check if form submitted, 
if (isset($_POST['submit'])) {
  $user_ID = $_POST['user_ID'];
  $uName = $_POST['Name'];
  $uMobileNo = $_POST['MoblieNo'];
  $uAddress = $_POST['address'];
  $city = $_POST['city'];

  if (empty($errors)) {
    //no errors found...updating the existing values
    $uName = mysqli_real_escape_string($conn, $_POST['Name']);
    $uMobileNo = mysqli_real_escape_string($conn, $_POST['MoblieNo']);
    $uAddress = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);

    $query = "UPDATE hmuser SET ";
    $query .= "uName = '{$uName}',";
    $query .= "uMobileNo = '{$uMobileNo}',";
    $query .= "uAddress = '{$uAddress}',";
    $query .= "city = '{$city}'";
    $query .= "WHERE hmUID = {$user_ID} LIMIT 1";

    $result = mysqli_query($conn, $query);

    if ($result) {
      //query unsuccessful.... redirecting to home
      header('location: loginForm.php?user_modified=true');
    } else {
      $errors[] = 'Failed to modify the record';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>healthmart.com</title>
  <link rel="shortcut icon" href="/Images/logo.ico" type="image/x-icon" />
  <link rel="stylesheet" href="/CSS/template2.css" />
  <link rel="stylesheet" href="/CSS/normalize.css" />
  <link rel="stylesheet" href="/CSS/editacc.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css" />
  <!--stylesheet for icons in footer -->
  <script src="/JS/home.js"></script>
  <script src="/JS/editacc.js"></script>
  <script src="/JS/cancel.js"></script>
</head>

<body>
  <div class="header">
    <a href="#" onclick="home();" class="logo"><i class="far fa-eye"></i> HealthMart</a>
    <div class="header-right">
      <?php
      if (isset($_SESSION['user_id'])) {
        echo "<a onclick=\"myacc();\"><i class=\"far fa-user-circle\"> </i>&nbsp;&nbsp;&nbsp;";
        echo $_SESSION['name'] . "</a>";
      } else {
        echo "<a onclick=\"register();\"><i class=\"far fa-user-circle\"></i> Sign in</a>";
      }
      ?>
      <?php
      if (!empty($_SESSION["shopping_cart"])) {
        $cart_count = count(array_keys($_SESSION["shopping_cart"]));
      ?>
        <a href="cart.php"><i class="fa fa-shopping-cart"></i> : <?php echo $cart_count; ?></a>
      <?php
      }
      ?>
    </div>
  </div>
  <div class="menu">
    <div class="menu-links">
      <a class="active" href="#" onclick="home();"><i class="fa fa-fw fa-home"></i> Home</a>
      <a href="#" onclick="medicine();">Medicines</a>
      <a href="#" onclick="medicalDevices();">Medical Devices</a>
      <a href="#" onclick="traditionalRemedies();">Traditional Remedies</a>
      <a href="#" onclick="aboutUs();">About us</a>
    </div>
    <div class="search-container">
      <form action="editacc.php" method="GET">
        <input type="text" placeholder="Search.." name="search" />
        <button type="submit">Submit</button>
      </form>
      <div class="dropdown-content" id="drop">
        <?php
        if ($items) {
          echo $itemList;
        }
        ?>
      </div>
    </div>
  </div>
  <div class="register-mother-left-right">
    <div class="register-child-left">
      <img src="/Images/register.png">
    </div>
    <div class="register-child-right">
      <div class="Sign-Up">
        <form action="editacc.php" method="POST" name="RegForm" enctype="multipart/form-data">
          <div class="signup-container">
            <h1>Update Account</h1>
            <p>Please fill this form to update your account.</p>
            <hr />
            <!-- display error messages from php validation -->
            <?php
            if (!empty($errors)) {
              echo '<div class="error">';
              echo '<b>There were error(s) on your form.</b><br>';
              echo "<script>alert('There were error(s) on your form!')</script>";
              foreach ($errors as $error) {
                echo $error . '<br>';
              }
              echo '</div><br>';
            }
            ?>
            <p>
              <input type="hidden" name="user_ID" value="<?php echo $user_ID; ?>" />
              <label for="Name"><b>Full Name</b></label>
              <input type="text" placeholder="Enter your full name" name="Name" <?php echo 'value ="' . $uName . '"'; ?> />
              <label for="MoblieNo"><b>Mobile Number</b></label>
              <input type="text" placeholder="Enter your mobile number" name="MoblieNo" <?php echo 'value ="' . $uMobileNo . '"'; ?> />
              <label for="address"><b>Address</b></label>
              <input type="text" placeholder="Enter your address" name="address" <?php echo 'value ="' . $uAddress . '"'; ?> />
              <label for="city"><b>City</b></label>
              <input type="text" placeholder="Enter your city" name="city" <?php echo 'value ="' . $city . '"'; ?> />
            <p>

            </p>
            <div class="signupfrom-buttons">
              <button type="submit" class="signupbtn" name="submit" onclick="return confirm('Are you sure you want to update your account?');">Change Account Details</button>
              <button type="button" class="cancelbtn" onclick="cancelModifyacc();">Cancel</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <footer>
    <div class="row primary">
      <div class="column about">
        <h3><i class="far fa-eye"></i> HealthMart</h3>
        <p>
          We are committed to your health at Health Mart. We now offer an
          online shopping and ordering experience to make health and wellness
          products more accessible to you. You may browse thousands of home
          health and over-the-counter goods on our site.
        </p>
        <div class="social">
          <a href="#"><i class="fa-brands fa-facebook-square" id="i1" onclick="fbLogin();"></i></a>
          <a href="#"><i class="fa-brands fa-instagram-square" id="i2" onclick="instaLogin();"></i></a>
          <a href="#"><i class="fa-brands fa-twitter-square" id="i3" onclick="twitterLogin();"></i></a>
          <a href="#"><i class="fa-brands fa-youtube-square" id="i4" onclick="youtube();"></i></a>
          <a href="#"><i class="fa-brands fa-whatsapp-square" id="i5" onclick="whatsapp();"></i></a>
        </div>
      </div>
      <div class="column links">
        <h3>Customer Service</h3>
        <ul>
          <li>
            <a href="#" onclick="contactUs();">Contact Us</a>
          </li>
          <li>
            <a href="#" onclick="privacyPolicy();">Privacy Policy</a>
          </li>
          <li>
            <a href="#" onclick="aboutUs();">About Us</a>
          </li>
        </ul>
      </div>
      <div class="column subscribe">
        <h3>Newsletter</h3>
        <div class="footersearch">
          <input type="email" placeholder="Your email id here" />
          <button>Subscribe</button>
        </div>
      </div>
    </div>
    <div class="row copyright">
      <p>© 2022 HealthMart,inc. All rights reserved.</p>
    </div>
  </footer>






</body>

</html>
<?php
//close database connection
mysqli_close($conn); ?>