<?php
  require_once 'app_config.php';
  
  $ret = $_GET['return'];
  if (isset($_COOKIE['user_name'])) {
    $user_name = $_COOKIE['user_name'];
  } else {
    header("location: home" . $ret . ".php?message=Error:+Your+password+was+not+changed.+Cookies+must+be+enabled.");
    exit();
  }
  $old_password = trim($_POST['old_password']);
  $new_password = trim($_POST['new_password']);
  $password_confirm = trim($_POST['password_confirm']);

  if ($new_password != $password_confirm) {
    header("location: home" . $ret . ".php?message=Error:+Your+password+was+not+changed.+The+confirmation+didn't+match.");
    exit();
  }
  if (strlen($new_password) < 6) {
    header("location: home" . $ret . ".php?message=Error:+Your+password+was+not+changed.+It+must+have+at+least+6+characters.");
    exit();
  }
  
  $con = mysqli_connect(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
  if (!$con) {
    die('Could not connect: ' . mysqli_error($con));
  }
  mysqli_select_db($con,DATABASE_TABLE);
  $stmt = $con->stmt_init();
  $stmt->prepare("update " . DATABASE_TABLE . " set password_hash = ? where user_name = ? AND password_hash = ?");
  $stmt->bind_param('sss', crypt($new_password,CRYPT_SALT), $user_name, crypt($old_password,CRYPT_SALT));
  $stmt->execute();
  $stmt->prepare("select * from " . DATABASE_TABLE . " where user_name = ? AND password_hash = ?");
  $stmt->bind_param('ss', $user_name, crypt($new_password,CRYPT_SALT));
  $stmt->execute();
  $result = $stmt->get_result();
  $row = mysqli_fetch_array($result);
  if ($row['user_name'] == NULL) {
    header("location: home" . $ret . ".php?message=Your+password+has+not+been+changed.+You+may+try+again." . 
      "+Remember+that+passwords+are+case-sensitive,+and+to+be+sure+your+caps+lock+isn't+on.");
    exit();
  }
  $email = $row['email'];
  if ((is_null($email)) && (strpos($row['user_name'], "@") > 0) && (strpos($row['user_name'], ".") > strpos($row['user_name'], "@") + 1)) {
    $email = $row['user_name'];
  }
  if (!is_null($email)) {
    mail($email, $row['name'] . ', your Tweater password has been changed',
      $row['name'] . ', Your password has been changed for your Tweater account.');
  }
  mysqli_close($con);
 
  setcookie('user_name', "", 0, "/");
  setcookie('password', "", 0, "/");
  header("location: home" . $ret . ".php?message=Your+password+has+been+changed.+Please+sign+in.");
  exit();
  