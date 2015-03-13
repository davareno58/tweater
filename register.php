  <?php
  require_once 'app_config.php';
  
  $ret = $_GET['return'];
  if (isset($_COOKIE['font_size'])) {
    $font_size = $_COOKIE['font_size'];
  } else {
    $font_size = FONTSIZE;
  }

  $user_name = trim($_POST['user_name']);
  $password_confirmation_error = "<p style='color:red'>The password confirmation does not match the password. Please re-enter both.</p>";
  $password_length_error = "<p style='color:red'>The password is too short. It must have at least 6 characters.</p>";
  $already_exists_error = "<p style='color:red'>The username \"{$user_name}\" is already being used by someone. Please choose another username.</p>";
  $password = trim($_POST['new_user_password']);
  $password_confirm = trim($_POST['password_confirm']);
  $password_hash = crypt($password,CRYPT_SALT);
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  
  if (intval(trim($_POST['added'])) != intval(trim($_POST['given_added']))) {
    $password_confirmation_error = "<p style='color:red'>The answer to the math question was incorrect. You may try again with a new question below.</p>";
    //$password_confirm = $password . " turing error";
  }
  if ((strlen($user_name) < 1) || (strlen($name) < 1)) {
    $password_confirmation_error = "<p style='color:red'>A username and name are both required.</p>";
    $already_exists_error = "";
    //$password_confirm = $password . " no username or name";
  }
  if (strlen($password) < 6) {
    $already_exists_error = "";
  }
  if ((strlen($email) != 0) && (strpos($email, "@") > 0)) {
    $tweat_notify = 1;
  } else {
    $email = NULL;
    $tweat_notify = 0;
  }
  
  $mysqli = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);

  if (mysqli_connect_errno()) {
    echo <<<EOD
<!DOCTYPE html><html>
  <head><title>Tweater: Error!</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
EOD;
    require_once '_shim.php';
    echo "</head><body style='background-color:#c0c0f0;padding:8px;font-size:{$font_size}px'>";
    require_once '_header' . $ret . '.php';
    echo "<div class='container'>";
    echo $_GET['message'];
    echo "</div>";
    echo "<p style='color:red'>ERROR: Account not created! ";
    echo "Database connection failed: ";
    echo mysqli_connect_error();
    echo ". <br />You may try again later.</p></body></html>";
    exit();
  }
  
  if ($stmt = $mysqli->prepare("SELECT user_name FROM " . DATABASE_TABLE . " WHERE user_name = ?")) {
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $stmt->bind_result($uname);
    $stmt->fetch();
    if ($uname == $user_name) {
      $already_exists = 1;
    } else {
      $already_exists = 0;
    }
    if (($already_exists == 1) || ($password_confirm != $password) || (strlen($password) < 6)) {
      $error_message = "";
      if ($already_exists == 1) {
        $error_message .= $already_exists_error;
      }
      if ($password_confirm != $password) {
        $error_message .= $password_confirmation_error;
      }
      if (strlen($password) < 6) {
        $error_message .= $password_length_error;
      }
    
      echo <<<EOD
<!DOCTYPE html><html>
  <head><title>Tweater: Error!</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
EOD;
      require_once '_shim.php';
      echo <<<EODS
<SCRIPT LANGUAGE="JavaScript">
<!--
  function turingsetup() {
    var firstnumber = Math.floor((Math.random() * 9) + 1);
    var secondnumber = Math.floor((Math.random() * 90) + 1);
    document.getElementById("firstnumber").innerHTML = firstnumber;
    document.getElementById("secondnumber").innerHTML = secondnumber;
    document.getElementById("added").value = firstnumber + secondnumber;
  };
-->
</SCRIPT>
</head><body style='background-color:#C0C0F0;padding:8px;font-family:{$font};
      font-size:{$font_size}px' onload='turingsetup();'>
EODS;

      require_once '_header' . $ret . '.php';
      echo "<div class='container'>";
      echo $message;
      echo "</div>";
      $self_name = $_SERVER['PHP_SELF'];
      echo "<h4 style='color:red'>Error:&nbsp;&nbsp;{$error_message}</h4>";
      echo <<<EOD2
<div class="center"><p style="text-align:center">
  <a href="home{$ret}.php" style="font-size:72px;color:red;background-color:violet"><b>
&nbsp;Tweater&nbsp;</b></a></p></div>
<div  class="center">
  <form action="register.php?return={$ret}" method="POST" autocomplete="off">
    <span>
    <div>
      <fieldset class="fieldset-auto-width" style="float:left;background-color:#A0A0C0">
        <legend>Register New User:</legend>
          <input type="text" style="display:none">
          <input type="password" style="display:none">
          <div class="input-group"><input type="text" class="form-control" placeholder="Desired Username" name="user_name" value="{$user_name}" autocomplete="off" maxlength="50" size="50"></div>
          <div class="input-group"><input type="password" class="form-control" placeholder="Password: Minimum 6 Characters" name="new_user_password" autocomplete="off" maxlength="32" size="32"></div>
          <div class="input-group"><input type="password" class="form-control" placeholder="Confirm Password" name="password_confirm" autocomplete="off" maxlength="32" size="32"></div>
          <div class="input-group"><input type="text" class="form-control" placeholder="Name" name="name" value="{$name}" autocomplete="off" maxlength="60" size="60"></div>
          <div class="input-group"><input type="text" class="form-control" 
placeholder="Optional: Your Email for Tweat Notifications" name="email" value="{$email}" autocomplete="off" maxlength="50" size="50"></div>
          <div class="input-group"><img src="qt.png" /><span id="firstnumber" name="firstnumber"> </span><img src="sa.png" /> 
            <span id="secondnumber" name="secondnumber"> </span>? <input type="text" name="given_added" autocomplete="off" size="3"><br />
          <input type="hidden" class="form-control" id="added" name="added" value="101"></div>
          <button type="submit" class="btn btn-primary">Register</button>
      </fieldset><br />
    </div>
    </span>
  </form>
</div>
</body>
</html>
EOD2;
      $stmt->close();
      $mysqli->close();
      exit();
    }
  } 
  $stmt->close();
  $mysqli->close();
  
  $mysqli2 = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);

  if (mysqli_connect_errno()) {
    echo <<<EOD
<!DOCTYPE html><html>
  <head><title>Tweater: Error!</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
EOD;
    require_once '_shim.php';
    echo "</head><body style='background-color:#c0c0f0;padding:8px;font-size:{$font_size}px'>";
    require_once '_header' . $ret . '.php';
    echo "<div class='container'>";
    echo $_GET['message'];
    echo "</div>";
    echo "<p style='color:red'>ERROR: Account not created! ";
    echo "Database connection failed: ";
    echo mysqli_connect_error();
    echo ". <br />You may try again later.</p></body></html>";
    exit();
  }
  $interests_words = "  " . trim(strtolower($user_name)) . " " . trim(strtolower($name)) . " ";
  if (mb_check_encoding($interests_words, 'UTF-8' ) === true ) {
    $stmt->prepare("SET NAMES 'utf8'");
    $stmt->execute();
  }
  if ($stmt = $mysqli2->prepare("INSERT INTO " . DATABASE_TABLE . " (user_name, password_hash, name, " . 
    "interests, interests_words, tweat_notify, email, picture_ext, password_reset_hash) values(?,?,?,NULL,?,?,?,NULL,NULL)")) {
    $stmt->bind_param('ssssis', $user_name, $password_hash, $name, $interests_words, $tweat_notify, $email);
    $stmt->execute();
    $stmt = $mysqli2->prepare("INSERT INTO followed_ones (id, user_name, followed_one) VALUES (NULL, ?, ?)");
    $stmt->bind_param('ss', $user_name, $user_name);
    $stmt->execute();
    $interests_words = trim(strtolower($user_name)) . " " . trim(strtolower($name));
    $interests_array = array_unique(explode(" ", $interests_words));
    foreach ($interests_array as $item) {
      if (strlen($item) > 0) {
        $stmt->prepare("INSERT INTO interests (id, user_name, interest) values(NULL, ?,?)");
        mysqli_set_charset('$con', 'utf8mb4');
        $stmt->bind_param('ss', $user_name, $item);
        $stmt->execute();
      }
    }
    $stmt = $mysqli2->prepare("SELECT user_name FROM " . DATABASE_TABLE . " WHERE user_name = ?");
    $stmt->bind_param('s', $user_name);
    $stmt->execute();
    $stmt->bind_result($uname);
    $stmt->fetch();
    if ($uname == $user_name) {
      echo <<<EOD
<!DOCTYPE html><html>
  <head><title>Tweater: {$name}'s Page</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
EOD;
      require_once '_shim.php';
      echo "</head><body style='background-color:#c0c0f0;padding:8px;font-size:{$font_size}px'>";
      require_once '_header' . $ret . '.php';
      echo "<div class='container'>";
      echo $_GET['message'];
      echo "</div></body></html>";
      
      $message = strtr("Success! {$name}'s account was created! Welcome! For help, click Help above.", " ", "+");
      $stmt->close();
      $mysqli2->close();
      setcookie('user_name', $user_name, 0, "/");
      setcookie('password', $password, 0, "/");
      header("Location: home" . $ret . ".php?message=" . $message);
      exit();
    }
  }
  
  echo <<<EOD
<!DOCTYPE html><html>
  <head><title>Tweater: Error!</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
EOD;
  require_once '_shim.php';
  echo "</head><body style='background-color:#c0c0f0;padding:8px;font-size:{$font_size}px'>";
  require_once '_header' . $ret . '.php';
  echo "<div class='container'>";
  echo $_GET['message'];
  echo "</div>";
  echo "<p style='color:red'>ERROR: Account not created!</p>";
  echo "Error type: " . $mysqli2->connect_error;
  echo "</body></html>";
  
  $stmt->close();
  $mysqli2->close();
  exit();
  
