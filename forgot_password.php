<?php
  require_once 'app_config.php';
  
  if (isset($_COOKIE['font_size'])) {
    $font_size = $_COOKIE['font_size'];
  } else {
    $font_size = FONTSIZE;
  }
  
  if (isset($_COOKIE['font_family'])) {
    $font = $_COOKIE['font_family'] . ", Helvetica";
  } else {
    $font = "Helvetica";
  }

echo <<<EOD
<!DOCTYPE html><html>
  <head><title>Password Reset Result</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
EOD;
  require_once '_shim.php';
  echo "</head><body style='background-color:#c0c0f0;padding:8px;font-family:{$font};font-size:{$font_size}'>";
  require_once '_header.php';
  echo "<div class='container'>";
  echo $_REQUEST['message'];
  echo "</div>";
  $user_name = trim($_REQUEST['given_user_name']);
  $password_reset_code = trim($_REQUEST['password_reset_code']);
  $given_password_reset_code = trim($_REQUEST['given_password_reset_code']);
  $password = trim($_REQUEST['password']);
  $password_confirm = trim($_REQUEST['password_confirm']);
  if (intval(trim($_REQUEST['added'])) != intval(trim($_REQUEST['given_added']))) {
    echo "<br /><br /><br /><blockquote><p style='color:red'>The answer to the math question was incorrect. To try again,<br />" . 
      "click the browser's Back button, or return to the <span style='color:black'><a href='index.html'>Sign In</a>" . 
      "<span style='color:red'> page,<br />enter your username and then " . 
      "click on 'I forgot my password.'<br />and click the Sign In button to get " . 
      "another password reset code<br />sent to your email address.</p></blockquote></body></html>";
      exit();
  }
  if ($given_password_reset_code != $password_reset_code) {
    echo "<br /><br /><br /><blockquote><p style='color:red'>The password reset code given is not correct. To try again,<br />" . 
      "click the browser's Back button, or return to the <span style='color:black'><a href='home.php'>Sign In</a>" . 
      "<span style='color:red'> page,<br />enter your username and then " . 
      "click on 'I forgot my password.'<br />and click the Sign In button to get " . 
      "another password reset code<br />sent to your email address.</p></blockquote></body></html>";
  } else if ($password != $password_confirm) {
    echo "<br /><br /><br /><blockquote><p style='color:red'>The new password confirmation does not match the new password.<br />" . 
      "To try again, click the browser's Back button, or return to the " . 
      "<br /><span style='color:black'><a href='index.html'>Sign In</a>" . 
      "<span style='color:red'> page, enter your username and then click on " . 
      "<br />'I forgot my password.' and click the Sign In button to get another " . 
      "<br />password reset code sent to your email address.</p></blockquote></body></html>";
  } else {
    $con = mysqli_connect(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
    if (!$con) {
      die('Could not connect: ' . mysqli_error($con));
    }
    mysqli_select_db($con,DATABASE_TABLE);
    $stmt = $con->stmt_init();
    $stmt->prepare("update " . DATABASE_TABLE . " set password_hash = ? where user_name = ?");
    $stmt->bind_param('ss', crypt($password,"pling515"), $user_name);
    $stmt->execute();
    mysqli_close($con);
    setcookie('user_name', $user_name, 0, "/");
    setcookie('password', $password, 0, "/");

    header("location: home.php");
  }
?>
