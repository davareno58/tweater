<?php
  require_once 'app_config.php';

  $tweat_max_size = TWEATMAXSIZE;
  $site_root = SITE_ROOT;
  $self_name = $_SERVER['PHP_SELF'];

  if (isset($_REQUEST['message'])) {
    $message = strtr($_REQUEST['message'], "+", " ");
  } else {  
    $message = "";
  }
  
  if (isset($_COOKIE['pic_scale'])) {
    $pic_scale = $_COOKIE['pic_scale'];
    if ($pic_scale > 16) {
      $pic_scale = 16;
    }
    if ($pic_scale <= 0.01) {
      $pic_scale = 1;
    }
  } else {
    $pic_scale = 1;
  }

  if (isset($_COOKIE['pic_position'])) {
    $pic_position = $_COOKIE['pic_position'];
  } else {
    $pic_position = "Top";
  }
  
  if (isset($_COOKIE['pic_visible'])) {
    $pic_visible = $_COOKIE['pic_visible'];
  } else {
    $pic_visible = "Show";
  }
    
  if (isset($_COOKIE['text_color'])) {
    $text_color = $_COOKIE['text_color'];
  } else {
    $text_color = "black";
  }
  
  if (isset($_COOKIE['font_size'])) {
    $font_size = $_COOKIE['font_size'];
  } else {
    $font_size = FONTSIZE;
  }
  $bigfont = $font_size * 1.5;

  if (isset($_COOKIE['tweat_width'])) {
    $tweat_width = $_COOKIE['tweat_width'];
  } else {
    $tweat_width = floor(1600 / $font_size);
  }
  
// Limit set for number of Tweats and Search Results
  if (isset($_COOKIE['shown_limit'])) {
    $shown_limit = $_COOKIE['shown_limit'];
  } else {
    $shown_limit = 50;
  }
  
  if (isset($_COOKIE['font_family'])) {
    $font = $_COOKIE['font_family'] . ", Helvetica";
  } else {
    $font = "Helvetica";
  }

  $chat = 'false';
  if (isset($_COOKIE['chat'])) {
    $chat = $_COOKIE['chat'];
  }

// Unsubscribe request
  if (isset($_COOKIE['unsub']) && isset($_COOKIE['user_name']) && isset($_COOKIE['password'])) {
    $user_name = trim($_COOKIE['user_name']);
    $password = trim($_COOKIE['password']);
    
    $con = mysqli_connect(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
    if (!$con) {
      die('Could not connect: ' . mysqli_error($con));
    }
    mysqli_select_db($con,DATABASE_TABLE);
    $stmt = $con->stmt_init();
    $stmt->prepare("DELETE FROM " . DATABASE_TABLE . 
      " WHERE ((user_name = ?) OR (email = ?)) AND (binary password_hash = ?)");
    $stmt->bind_param('sss', $user_name, $user_name, crypt($password,CRYPT_SALT));
    $stmt->execute();
    mysqli_close($con);
    setcookie('user_name', "", time() - 7200, "/");
    setcookie('password', "", time() - 7200, "/");
    setcookie('unsub', "", time() - 7200, "/");
    echo <<<EODU
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE>TWEATER UNSUBSCRIBE</TITLE>
<META NAME="description" CONTENT="Tweater Social Site">
<META NAME="keywords" CONTENT="tweater, social site, tweats">
<LINK rel='shortcut icon' href='favicon.png' type='image/png'>
<SCRIPT language='JavaScript'>
<!--
function openit() {
    document.cookie = "user_name=; expires=-7200; path=/";
    document.cookie = "password=; expires=-7200; path=/";
    document.cookie = "unsub=; expires=-7200; path=/";
}
//-->
</SCRIPT>
</HEAD>
<BODY style='background-color:#C0C0F0;font-family:{$font};font-size:{$font_size}px' LINK="#C00000" VLINK="#800080" alink="#FFFF00" bgcolor="00D0C0" onLoad="openit();">
<h1 style='text-align:center'>Tweater: You are now unsubscribed to Tweater. Sorry to see you go!<br />(Actually I'm a computer and have no human feelings!)</h1>
<h2 style='text-align:center'><a href="{$self_name}">Click here to sign in another user or register a new user.</a></h2>
<img src='tweatysad.png' /></BODY>
</HTML>
EODU;
    exit();
  }
// Automatic signin
  if (isset($_COOKIE['user_name']) && isset($_COOKIE['password']) && (strlen($_COOKIE['password']) > 0)) {
    $user_name = trim($_COOKIE['user_name']);
    $password = trim($_COOKIE['password']);
    $stay_logged_in = $_POST['stay_logged_in'];
  } else {
// Manual signin
    $user_name = trim($_POST['user_name']);
    $password = trim($_POST['password']);
    $stay_logged_in = $_POST['stay_logged_in'];
  }
// Change email address
  if (isset($_GET['new_email_address'])) {
    $new_email_address = trim($_GET['new_email_address']);
    if ($new_email_address == "null") {
      $new_email_address = NULL;
      $null = "None";
    } else {
      $null = "";
    }
    if (mb_check_encoding($new_email_address, 'UTF-8' ) === true ) {
      $mysqli3 = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
      if ($stmt = $mysqli3->prepare("SET NAMES 'utf8'")) {
        $stmt->execute();
      }
      if ($stmt = $mysqli3->prepare("UPDATE users SET email = ? WHERE user_name = ? AND binary password_hash = ?")) {
        $mysqli3->set_charset('utf8mb4');
        $stmt->bind_param('sss', $new_email_address, $user_name, crypt($password,CRYPT_SALT));
        $stmt->execute();
        if (mysqli_connect_errno()) {
          $message = "ERROR: Email address not updated! Sorry, but something went wrong.<br />" . 
            "You may try again later. ";
        } else {
          $message = "Email address updated to:  " . $new_email_address . $null;
          if (strlen($new_email_address) > 2) {
            $tweat_notify = 1;
          } else {
            $tweat_notify = 0;
          }
          $stmt = $mysqli3->prepare("UPDATE users SET tweat_notify = ? WHERE user_name = ? AND " . 
            "binary password_hash = ?");
          $stmt->bind_param('iss', $tweat_notify, $user_name, crypt($password,CRYPT_SALT));
          $stmt->execute();
        }
      } else {
        $message = "ERROR: Email address not updated! Sorry, but something went wrong.<br />" . 
          "You may try again later. ";
      }
    }
    $stmt->close();
    $mysqli3->close();
  }
// Change Tweat Email Notifications
  if (isset($_GET['notify'])) {
    $mysqli3 = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
    if ($_GET['notify'] == '0') {
      $tweat_notify = 0;
      $message = "Tweat Notifications are now disabled.";
    } else {
      $tweat_notify = 1;
      $message = "Tweat Notifications are now enabled.";
    }
    if ($stmt = $mysqli3->prepare("UPDATE users SET tweat_notify = ? WHERE user_name = ? AND 
      binary password_hash = ?")) {
      $stmt->bind_param('iss', $tweat_notify, $user_name, crypt($password,CRYPT_SALT));
      $stmt->execute();
      if (mysqli_connect_errno()) {
        $message = "ERROR: Tweat Notification was not updated! Sorry, but something went wrong.<br />" . 
          "You may try again later. ";
      }
    } else {
      $message = "ERROR: Tweat Notification was not updated! Sorry, but something went wrong.<br />" . 
        "You may try again later. ";
    }
    $stmt->close();
    $mysqli3->close();
  }
//Post New Tweat
  if (isset($_REQUEST['tweat']) && (strlen($_REQUEST['tweat']) > 0)) {
    $tweat = trim($_REQUEST['tweat']);
    $name = str_replace("+", " ", $_REQUEST['name']);
    if (mb_check_encoding($tweat, 'UTF-8' ) === true ) {
      $mysqli3 = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
      if ($stmt = $mysqli3->prepare("SET NAMES 'utf8'")) {
        $stmt->execute();
      }
      $hashtag_pos = strpos($tweat, "#");
      if ($hashtag_pos === false) {
        $hashtag = NULL;
      } else {
        $hashtag_pos = $hashtag_pos + 1;
        $start = $hashtag_pos;
        while (($hashtag_pos < strlen($tweat)) && (strpos(" ,.?!:;*/()-+{}[]|\"<>\\\`", 
          substr($tweat, $hashtag_pos, 1)) === false)) {
          $hashtag_pos = $hashtag_pos + 1;
        }
        $hashtag = trim(strtolower(substr($tweat, $start, $hashtag_pos - $start)));
      }
      if ($chat == "true") {
        $hashtag = "DEL" . (time() + 86400);
      }
      if ($stmt = $mysqli3->prepare("INSERT INTO tweats (id, user_name, tweat, hashtag) values(NULL,?,?,?)")) {
        $mysqli3->set_charset('utf8mb4');
        $stmt->bind_param('sss', $user_name, $tweat, $hashtag);
        $stmt->execute();
        if (mysqli_connect_errno()) {
          $message = "ERROR: Tweat not posted! Sorry, but something went wrong.<br />" . 
            "You may try to post the Tweat again. ";
        } else {
          $stmt = $mysqli3->prepare("SELECT user_name, name, tweat_notify, email FROM users WHERE user_name IN " . 
            "(SELECT user_name FROM followed_ones WHERE followed_one = ? AND user_name != followed_one)");
          $mysqli3->set_charset('utf8mb4');
          $stmt->bind_param('s', $user_name);
          $stmt->execute();
          $result = $stmt->get_result();
          while ($row = mysqli_fetch_array($result)) {
            $email = $row['email'];
// Send Email Tweat Notification(s)
            if (($chat != "true") && ($row['tweat_notify'] == 1) && (strpos($email, "@") > 0)) {
              $email_header = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\n";
              mail($email, 'Tweat Notification: ' . $name . ' (' . $user_name . ') has just posted this Tweat',
                'Hello ' . $row['name'] . ',<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $name . ' (' . 
                $user_name . ') has just posted this Tweat:<br /><br />' . wordwrap($tweat, 70, '<br />', true) . 
                '<br /><br />If you don\'t want to receive Tweat Notifications, please ' . 
                'sign in to your Tweat account at http://crandall.altervista.org/tweater<br />' . 
                'and click on the Tweat Notifications button at the left. A pop-up prompt ' . 
                'will appear. Type the word No and click on OK.<br /><br />--Tweater<br /><br />', $email_header);
            } else {
              setcookie('chat_timeout', time() + 300, time() + 7200, "/");
            }
          }
        }
      }
    }
    $stmt->close();
    $mysqli3->close();
  }
  
  $con = mysqli_connect(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
  if (!$con) {
    die('Could not connect: ' . mysqli_error($con));
  }
  mysqli_select_db($con,DATABASE_TABLE);
  //mysqli_set_charset('$con', 'utf8mb4');
  $stmt = $con->stmt_init();
  $stmt->prepare("SET NAMES 'utf8'");
  $stmt->execute();
  $stmt = $con->stmt_init();
// Delete Tweat
  if (isset($_GET['delete_tweat'])) {
    $tid = $_GET['delete_tweat'];
    $stmt->prepare("select * from " . DATABASE_TABLE . " where ((user_name = ?) or (email = ?)) and (binary password_hash = ?)");
    $stmt->bind_param('sss', $user_name, $user_name, crypt($password,CRYPT_SALT));
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $row = mysqli_fetch_array($result);
    $status = $row['admin_status'];
  
    $mysqli3 = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
    if ($status == 1) {
// Administrator deletes a Tweat
      if ($stmt = $mysqli3->prepare("DELETE FROM tweats WHERE id = ?")) {
        $stmt->bind_param('i', $tid);
        $stmt->execute();
        $stmt->close();
        $message = "Tweat #" . $tid . " was deleted.";
      } else {
        $message = "Error: Sorry, but there was a database error and the Tweat was not deleted: " . mysqli_error($mysqli3);
      }
    } else {
// Non-administrator deletes a Tweat
      if ($stmt = $mysqli3->prepare("DELETE FROM tweats WHERE user_name = ? AND id = ?")) {
        $stmt->bind_param('si', $user_name, $tid);
        $stmt->execute();
        $stmt->close();
        $message = "The Tweat was deleted.";
      } else {
        $message = "Error: Sorry, but there was a database error and the Tweat was not deleted: " . mysqli_error($mysqli3);
      }
    }
    $mysqli3->close();
  }

  $forgot_password = $_REQUEST['forgot_password'];
  mysqli_select_db($con,DATABASE_TABLE);
  $stmt = $con->stmt_init();
  mysqli_set_charset('$con', 'utf8mb4');
// Forgot password, so email password reset code if email address exists
  if ($forgot_password == "on") {
    $stmt->prepare("select * from " . DATABASE_TABLE . " where (user_name = ?) or (email = ?)");
    $stmt->bind_param('ss', $user_name, $user_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = mysqli_fetch_array($result);
    $email = $row['email'];
    if ((is_null($email)) && (strpos($row['user_name'], "@") > 0) && (strpos($row['user_name'], ".") > strpos($row['user_name'], "@") + 1)) {
      $email = $row['user_name'];
    }
    if (is_null($email)) {
      echo "<p style='color:red'>Sorry, but I don't have an email address to send the password reset code to.<br />Suggestion: Register as a new user and enter an email address at the bottom of the home page,<br />in case you forget your password again.</p>";
    } else {
    $password_reset_code = chr(rand(97, 122)) . chr(rand(97, 122)) . chr(rand(97, 122)) .
      chr(rand(97, 122)) . chr(rand(97, 122)) . chr(rand(97, 122)) . chr(rand(97, 122)) . 
      chr(rand(97, 122)) . chr(rand(97, 122)) . chr(rand(97, 122));
      mail($email, 'Password reset code for ' . $row['name']. '\'s Tweater account',
        $row['name'] . ', Here is the requested password reset code for your Tweater account: ' . 
        $password_reset_code);
    $stmt->prepare("update " . DATABASE_TABLE . " SET password_reset_hash = ? where (user_name = ?) or (email = ?)");
    $stmt->bind_param('sss', crypt($password_reset_code,CRYPT_SALT), $user_name, $user_name);
    $stmt->execute();

echo <<<EOD
<!DOCTYPE html><html>
  <head><title>Password Reset</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
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
EOD;
  require_once '_shim.php';
  echo "</head><body style='background-color:#A0A0C0;padding:8px;font-family:{$font};font-size:{$font_size}px' onload='turingsetup();'>";
  require_once '_header.php';
  if (strlen($message) > 0) {
    echo "<div class='container'><p style='font-size:{$bigfont}px;color:red'>{$message}</p></div>";
    $message = "";
  }
// Enter password reset code and choose new password
  echo <<<EOD3
A password reset code has been sent by the Apache server to your email address<br />
(or to the email address in your username). If you don't see it there, be sure to<br />
check your spam folder. Please enter it here, along with the new password that<br />
you would like to use:<br />
<br />
<form action="forgot_password.php" method="POST">
<span>
<div>
<fieldset class="fieldset-auto-width" style="float:left">
<legend>Password Reset:</legend>
<input type="text" style="display:none">
<input type="password" style="display:none">
<div class="input-group"><input type="text" class="form-control" placeholder="Password Reset Code" name="given_password_reset_code" autocomplete="off" maxlength="20" size=20></div>
<div class="input-group"><input type="password" class="form-control" placeholder="New Password" name="password" autocomplete="off" maxlength="32" size="32"></div>
<div class="input-group"><input type="password" class="form-control" placeholder="Confirm New Password" autocomplete="off" name="password_confirm" maxlength="32" size=32></div>
<div class="input-group"><img src="qt.png" /><span id="firstnumber" name="firstnumber"> </span><img src="sa.png" /> 
<span id="secondnumber" name="secondnumber"> </span>? <input type="text" name="given_added" autocomplete="off" size="5">
<input type="hidden" class="form-control" id="added" name="added" value="101" size="5"></div>
<div class="input-group"><input type="hidden" class="form-control" name="given_user_name" value={$user_name}></div>
<button type="submit" class="btn btn-success">Change Password</button>
</fieldset>
</div>
</span>
</form>
</body>
</html>
EOD3;
    }
    mysqli_close($con);
    exit();
  }

  $stmt->prepare("SELECT * FROM " . DATABASE_TABLE . " WHERE ((user_name = ?) OR (email = ?)) AND (binary password_hash = ?)");
  $stmt->bind_param('sss', $user_name, $user_name, crypt($password,CRYPT_SALT));
  $stmt->execute();
  $result = $stmt->get_result();
  $num_rows = $result->num_rows;
  if ((!$result) || ($num_rows == 0)) {
    echo <<<EOD
<!DOCTYPE html><html>
  <head><title>Tweater</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
    <LINK rel='shortcut icon' href='favicon.png' type='image/png'>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
<SCRIPT LANGUAGE="JavaScript">
<!--
  function turingsetup() {
    var firstnumber = Math.floor((Math.random() * 9) + 1);
    var secondnumber = Math.floor((Math.random() * 90) + 1);
    document.getElementById("firstnumber").innerHTML = firstnumber;
    document.getElementById("secondnumber").innerHTML = secondnumber;
    document.getElementById("added").value = firstnumber + secondnumber;
  };
    
  function URLsetup() {
    document.getElementById("action").action = "{$self_name}?user_name=" + 
      document.getElementById("user_name").value;
  };
  
  function about() {
    alert("Tweater is an app created by David Crandall, to show his programming skills using PHP, MySQL, Bootstrap, Angular.js, JavaScript, HTML and CSS.");
  };
 
  function contact() {
    alert("David Crandall's email is crandadk@aol.com");
  };
-->
</SCRIPT>
EOD;
    require_once '_shim.php';
    echo "</head><body style='background-color:#C0C0F0;padding:8px;font-family:{$font};
      font-size:{$font_size}px' onload='turingsetup();'>";
    require_once '_header.php';
    if (strlen($message) > 0) {
      echo "<div class='container'><p style='font-size:{$bigfont}px;color:red'>{$message}</p></div>";
      $message = "";
    }
// Signin failure
    if ($user_name != "") {
      echo "<p style='color:red'>\"{$user_name}\" was not found in " . DATABASE_TABLE . " with the password given.</p>";
    }
    if (strtolower($password) != $password) {
      echo "<p style='color:red'>Note: Make sure your caps lock isn't on by accident, since passwords are case sensitive.</p>";
    }

    echo <<<EOD2
<div style="margin-left: auto; margin-right: auto;"><p style="text-align:center">
<a href="{$self_name}" style="font-size:72px;color:red;background-color:violet"><b>
&nbsp;Tweater&nbsp;</b></a></p></div>
<div style="margin-left: auto; margin-right: auto;position: relative;right: -153px;">
<form action="{$self_name}" method="POST" id="action">
<span>
<div>
<fieldset class="fieldset-auto-width" style="float:left;background-color:#A0C0A0">
<legend>Sign In:</legend>
<div class="input-group"><input type="text" class="form-control" placeholder="Username or Email" name="user_name" id="user_name" maxlength="50" size="60"></div>
<div class="input-group"><input type="password" class="form-control" placeholder="Password" name="password" maxlength="32" size="32"></div>
<div class="checkbox"><label><input type="checkbox" name="forgot_password" unchecked>I forgot my password.</label></div>
<div class="checkbox"><label><input type="checkbox" name="stay_logged_in" unchecked>Remain signed in.</label></div>
<button type="submit" class="btn btn-success">Sign In</button>
</fieldset>
</div>
</span>
</form>
<div style="float:left">
<br /><br /><br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
OR&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</div>
<form action="register.php" method="POST" autocomplete="off">
<span>
<div>
<fieldset class="fieldset-auto-width" style="float:left;background-color:#A0A0C0">
<legend>Register New User:</legend>
<input type="text" style="display:none">
<input type="password" style="display:none">
<div class="input-group"><input type="text" class="form-control" autocomplete="off" placeholder="Desired Username" name="user_name" value="{$user_name}" maxlength="50" size="50"></div>
<div class="input-group"><input type="password" class="form-control" autocomplete="off" placeholder="Password: Minimum 6 Characters" name="new_user_password" maxlength="32" size="32"></div>
<div class="input-group"><input type="password" class="form-control" autocomplete="off" placeholder="Confirm Password" name="password_confirm" maxlength="32" size="32"></div>
<div class="input-group"><input type="text" class="form-control" autocomplete="off" placeholder="Name" name="name" value="{$name}" maxlength="60" size="60"></div>
<div class="input-group"><input type="text" class="form-control" autocomplete="off" 
placeholder="Optional: Your Email for Tweat Notifications" name="email" value="{$email}" autocomplete="off" maxlength="50" size="50"></div>
<div class="input-group"><img src="qt.png" /><span id="firstnumber" name="firstnumber"> </span><img src="sa.png" /> 
<span id="secondnumber" name="secondnumber"> </span>? <input type="text" name="given_added" autocomplete="off" size="3"><br />
<input type="hidden" class="form-control" id="added" name="added" autocomplete="off" value="101"></div>
<button type="submit" class="btn btn-primary">Register</button>
</fieldset><br />
</div>
</span>
</form>
</div>
</body>
</html>
EOD2;
    mysqli_close($con);
    exit();
  }
// Get picture filename or default
  $row = mysqli_fetch_array($result);
  $id = $row['id'];
  $picture_ext = $row['picture_ext'];
  if (strlen($picture_ext) < 1) {
    $picture_url = "nophoto.jpg";
  } else {
    $picture_url = $id . "." . $picture_ext;
  }
// Set signed-in cookie
  if (!isset($_COOKIE['user_name']) || !isset($_COOKIE['password'])) {
    setcookie('user_name', $user_name, 0, "/");
    setcookie('password', $password, 0, "/");
  }
// Show another user's profile
  if (isset($_GET['view_user_name'])) {

    $view_user_name = $_GET['view_user_name'];

    mysqli_select_db($con,DATABASE_TABLE);
    $stmt = $con->stmt_init();
    $stmt->prepare("select * from " . DATABASE_TABLE . " where user_name = ?");
    $stmt->bind_param('s', $view_user_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = mysqli_fetch_array($result);
    $view_name = $row['name'];
    $view_interests = $row['interests'];
    if (strlen($row['picture_ext']) < 1) {
      $picture_url = "nophoto.jpg";
    } else {
      $picture_url = $row['id'] . "." . $row['picture_ext'];
    }
    
    echo "<!DOCTYPE html><html><head><meta charset='utf-8' /><title>" . $view_name . 
      "'s Tweater Page (Username: " . $view_user_name . ")</title>";
    
    echo <<<EOD
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script src= "http://ajax.googleapis.com/ajax/libs/angularjs/1.2.26/angular.min.js">
EOD;
    require_once '_shim.php';
    echo "<script language='JavaScript'>\n<!--\n";
  
    if ($stay_logged_in == "on") {
      echo "  staySignedIn();\n\n";
    }
  
    echo "var fontsize = {$font_size};";

    $unsubscribe_password = crypt($password,CRYPT_SALT);
    echo <<<EODJ
  function signOut() {
    document.cookie = "user_name=; expires=-7200; path=/";
    document.cookie = "password=; expires=-7200; path=/";
    window.location.replace('signout.html');
  }
  
  function unsubscribe() {
    if (confirm("Are you sure you want to unsubscribe to Tweater and delete your account?")) {
      staySignedIn();
      var date = new Date();
      date.setTime(date.getTime() + (86400 * 365 * 67));
      document.cookie = "unsub=unsub; expires=" + date.toGMTString() + "; path=/";
    }
  }
  
  function staySignedIn() {
    var date = new Date();
    date.setTime(date.getTime() + (86400 * 365 * 67));
    document.cookie = "user_name={$user_name}; expires=" + date.toGMTString() + "; path=/";
    document.cookie = "password={$password}; expires=" + date.toGMTString() + "; path=/";
  }
  
  function staySignedInWithAlert() {
    staySignedIn();
    alert("You will now remain signed in.");
  }

  function about() {
    alert("Tweater is an app created by David Crandall, to show his programming skills using PHP, MySQL, Bootstrap, Angular.js, JavaScript, HTML and CSS.");
  }
 
  function contact() {
    alert("David Crandall's email is crandadk@aol.com");
  }
 
  function textErase() {
    document.getElementById("tweat").innerHTML = "";
  }
  
  function textLarger() {
    fontsize = fontsize + 4;
    if (fontsize  > 72) {
      fontsize = 72;
    }
    var date = new Date();
    date.setTime(date.getTime() + (86400 * 365 * 67));
    document.cookie = "font_size=" + fontsize + "; expires=" + date.toGMTString() + "; path=/";
    location.replace("{$self_name}");
  }

  function textSmaller() {
    fontsize = fontsize - 4;
    if (fontsize  < 6) {
      fontsize = 6;
    }
    var date = new Date();
    date.setTime(date.getTime() + (86400 * 365 * 67));
    document.cookie = "font_size=" + fontsize + "; expires=" + date.toGMTString() + "; path=/";
    location.replace("{$self_name}");
  }

  function fontEntry() {
    var newfont = prompt("Current font: {$font}. Enter desired font: ", "Helvetica");
    if ((newfont != "") && (newfont != "{$font}")) {
    var date = new Date();
    date.setTime(date.getTime() + (86400 * 365 * 67));
    document.cookie = "font_family=" + newfont + "; expires=" + date.toGMTString() + "; path=/";
    location.replace("{$self_name}");
    }
  }
  
  function changeEmail() {
    var emailAddress = prompt("Enter your new email address or just press OK to have no email address:", "");
    if (emailAddress == "") {
      emailAddress = null;
    }
    location.replace("{$self_name}?new_email_address=" + emailAddress);
  }

//-->
</script>
EODJ;
    echo "</head><body style='background-color:#C0C0F0;padding:8px;font-family:{$font};font-size:{$font_size}px'>";
    require_once '_header.php';
    
    if (strlen($message) > 0) {
      echo "<div class='container'><p style='font-size:{$bigfont}px;color:red'>{$message}</p></div>";
      $message = "";
    }
  
    mysqli_close($con);

    echo <<<EODT
<h1><a href="{$self_name}" style="font-size:{$bigfont}px;color:red;background-color:violet"><b>
&nbsp;&nbsp;&nbsp;Tweater&nbsp;&nbsp;&nbsp;</b></a>
<div style="text-shadow: 5px 5px 5px #007F00;">{$view_name}'s Tweater Page ({$view_user_name})&nbsp;&nbsp;
<button type="button" class="btn btn-success" onclick="location.replace(
'follow.php?followed_one={$view_user_name}&followed_name={$view_name}');">Follow</button>
<button type="button" class="btn btn-danger" onclick="location.replace(
'unfollow.php?followed_one={$view_user_name}&followed_name={$view_name}');">Unfollow</button>
</div></h1><br />
<b>Interests and Information:&nbsp;&nbsp;</b>{$view_interests}<br /><br />
<img id='picture' src='pictures/{$picture_url}' /><br /><br />
<b>Tweats:</b><br /><br />
EODT;

    $mysqli2 = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);

    $mysqli2->set_charset("utf8");
    
    if ($stmt = $mysqli2->prepare("SELECT t.id as tid, t.user_name, t.tweat, u.id, u.name, u.picture_ext FROM tweats AS t INNER JOIN " . 
      "users AS u ON t.user_name = u.user_name WHERE t.user_name = ? ORDER BY t.id DESC LIMIT ?")) {
      $stmt->bind_param('ss', $view_user_name, $shown_limit);
      $stmt->execute();
      $result = $stmt->get_result();
    } else {
      echo "Error: " . $mysqli2->error . " & " . $mysqli3->error;
    }

    while ($myrow = $result->fetch_assoc()) {
      if ($myrow['name']) {
        $myrow_tweat = $myrow['tweat'];
        $tid = $myrow['tid'];
      } else {
        $myrow_tweat = "";
        
      }
      echo "<p>" . wordwrap($myrow_tweat, $tweat_width, '<br />', true);
// Red X button for administrator to delete Tweat
        if ($status == 1) {
          $no_quote_tweat = strtr(substr($myrow_tweat,0,80), "\"'\t\r\n\f", "      ");
          echo "&nbsp;&nbsp;<img src='xdel.png' style='position:relative;top:-2px' onclick='if (confirm(\"Are you sure you want to delete this Tweat?:  " . 
            $no_quote_tweat . "...\")) {window.open(\"{$self_name}?delete_tweat=\" + {$tid});}' />";
        }
      echo "</p>";
    }

    echo "</div></body></html>";
    echo "</body></html>";
  
    $stmt->close();
    $mysqli2->close();
    exit();
  }

  $name = $row['name'];
  $status = $row['admin_status'];
// Administrator deletes a listed user
  if (($status == 1) && (isset($_GET['delete_listed_user']))) {
    $del_user_id = $_GET['delete_listed_user'];
    $condel = mysqli_connect(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
    if (!$condel) {
      die('Could not connect: ' . mysqli_error($condel));
    }
    mysqli_select_db($condel,DATABASE_TABLE);
    $stmtd = $condel->stmt_init();
    $stmtd->prepare("DELETE FROM " . DATABASE_TABLE . " WHERE id = ?");
    $stmtd->bind_param('i', $del_user_id);
    $stmtd->execute();
    mysqli_close($condel);
    $message = "Listed user with ID #{$del_user_id} has been deleted.";
  }
  
// Show signed-in user's page
  echo "<!DOCTYPE html><html ng-app='myApptw' ng-controller='twCtrl' ><head><meta charset='utf-8' />";

  echo "<title>" . $name . "'s Tweater Page (Username: " . $row['user_name'] . ")</title>
  <style>
  .stripedeven {
    background-color:#A0A0F0;
  }
  .stripedodd {
    background-color:#E0E0FF;
  }
  </style>";
    
  echo <<<EOD
    <link rel='shortcut icon' href='favicon.png' type='image/png'>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
EOD;

  require_once '_shim.php';
  echo "<script language='JavaScript'>\n<!--\nvar jq = $.noConflict();
";
  
  if ($stay_logged_in == "on") {
    echo "  staySignedIn();\n\n";
  }
  
  echo "var fontsize = {$font_size};";

  $unsubscribe_password = crypt($password,CRYPT_SALT);
    
  echo <<<EODJ
  var saveWidth = jq("#picture").width();
  var picHtml = "<img id='picture' src='pictures/{$picture_url}' />";
  var picHtmlBottom = "<img id='picture' src='pictures/{$picture_url}' style='position:relative;top:-20px;padding-bottom:20px' />";
  var color = "{$text_color}";
  var pic_scale = {$pic_scale};
  var pic_position = "{$pic_position}";
  var pic_visible = "{$pic_visible}";
  var chat = {$chat};

  function wordwrap(str, width, brk, cut) {
     brk = brk || '\\n';
     width = width || 75;
     cut = cut || false;

     if (!str) { return str; }

     var regex = '.{1,' +width+ '}(\\s|$)' + (cut ? '|.{' +width+ '}|.+$' : '|\\S+?(\\s|$)');

     return str.match( RegExp(regex, 'g') ).join( brk );
  }

  function startPic() {
    if (color == "white") {
      color = "black";
      toggleBW();
    }
    if (pic_position == "Bottom") {
      jq("body").attr("background", "pictures/backviolet.png");
      jq("#pic_top").html("");
      jq("#pic_bottom").html(picHtmlBottom);
    }
    if (pic_position == "Top") {
      jq("body").attr("background", "pictures/backviolet.png");
      jq("#pic_bottom").html("");
      jq("#pic_top").html(picHtml);
    }
    if (pic_position == "Background") {
      jq("#pic_top").html("");
      jq("#pic_bottom").html("");
      jq("body").attr("background", "pictures/{$picture_url}");
      jq("body").css("background-size", "cover");
    }
    if (pic_position == "Tile") {
      jq("#pic_top").html("");
      jq("#pic_bottom").html("");
      jq("body").attr("background", "pictures/{$picture_url}");
      jq("body").css("background-size", "auto");
      jq("body").css("background-repeat", "repeat");
    }
    if (pic_scale != 1) {
      jq("#picture").width(jq("#picture").width() * pic_scale);
    }
  }
  
  jq(document).ready(function(){
    jq("#selsize").change(function(){
      if (jq("#picture").width() == 0) {
        jq("#picture").width(saveWidth);
      }
      var date = new Date();
      date.setTime(date.getTime() + (86400 * 365 * 67));

      jq("body").attr("background", "pictures/backviolet.png");
      jq("body").css("background-size", "auto");
      jq("body").css("background-repeat", "repeat");
      
      if (jq("#selsize").val() == "Top") {
        jq("#pic_bottom").html("");
        jq("#pic_top").html(picHtml);
        jq("#picture").width(jq("#picture").width() * pic_scale);
        document.cookie = "pic_position=Top; expires=" + date.toGMTString() + "; path=/";
      }
      if (jq("#selsize").val() == "Bottom") {
        jq("#pic_top").html("");
        jq("#pic_bottom").html(picHtmlBottom);
        jq("#picture").width(jq("#picture").width() * pic_scale);
        document.cookie = "pic_position=Bottom; expires=" + date.toGMTString() + "; path=/";
      }
      if (jq("#selsize").val() == "Background") {
        jq("#pic_top").html("");
        jq("#pic_bottom").html("");
        jq("body").attr("background", "pictures/{$picture_url}");
        jq("body").css("background-size", "cover");
        document.cookie = "pic_position=Background; expires=" + date.toGMTString() + "; path=/";
      }
      if (jq("#selsize").val() == "Tile") {
        jq("#pic_top").html("");
        jq("#pic_bottom").html("");
        jq("body").attr("background", "pictures/{$picture_url}");
        jq("body").css("background-size", "auto");
        jq("body").css("background-repeat", "repeat");
        document.cookie = "pic_position=Tile; expires=" + date.toGMTString() + "; path=/";
      }
      if (jq("#selsize").val() == "Double") {
        jq("#picture").width(jq("#picture").width() * 2);
        pic_scale = pic_scale * 2;
        document.cookie = "pic_scale=" + pic_scale + "; expires=" + date.toGMTString() + "; path=/";
      }
      if (jq("#selsize").val() == "Half") {
        jq("#picture").width(jq("#picture").width() / 2);
        pic_scale = pic_scale / 2;
        document.cookie = "pic_scale=" + pic_scale + "; expires=" + date.toGMTString() + "; path=/";
      } 
      if (jq("#selsize").val() == "Hide") {
        saveWidth = jq("#picture").width();
        jq("#picture").width(0);
        pic_visible = "Hide";
        document.cookie = "pic_visible=" + pic_visible + "; expires=" + date.toGMTString() + "; path=/";
      }
      if (jq("#selsize").val() == "Show") {
        jq("#picture").width(saveWidth);
        pic_visible = "Show";
        document.cookie = "pic_visible=" + pic_visible + "; expires=" + date.toGMTString() + "; path=/";
      }      
      jq("#selsize").val("Caption");
    });
  });

  function signOut() {
    var date = new Date();
    date.setTime(date.getTime() - 7200);
    document.cookie = "user_name={$user_name}; expires=" + date.toGMTString() + "; path=/";
    document.cookie = "password={$password}; expires=" + date.toGMTString() + "; path=/";
    window.location.replace('signout.html');
  }
  
  function unsubscribe() {
    if (confirm("Are you sure you want to unsubscribe to Tweater and delete your account?")) {
      staySignedIn();
      var date = new Date();
      date.setTime(date.getTime() + (86400 * 365 * 67));
      document.cookie = "unsub=unsub; expires=" + date.toGMTString() + "; path=/";
    }
  }
  
  function staySignedIn() {
    var date = new Date();
    date.setTime(date.getTime() + (86400 * 365 * 67));
    document.cookie = "user_name={$user_name}; expires=" + date.toGMTString() + "; path=/";
    document.cookie = "password={$password}; expires=" + date.toGMTString() + "; path=/";
  }
  
  function staySignedInWithAlert() {
    staySignedIn();
    alert("You will now remain signed in.");
  }

  function about() {
    alert("Tweater is an app created by David Crandall, to show his programming skills using PHP, \
MySQL, Bootstrap, Angular.js, jQuery, JavaScript, HTML and CSS.\\n\\n\\
Note:  The creator of this website doesn't assume responsibility for its usage by others.");
  }
 
  function contact() {
    alert("David Crandall's email is crandadk@aol.com");
  }
 
  function textErase() {
    jq("#tweat").val("");
    jq("#hashtag_search").val("");
    jq("#search_any").val("");
    jq("#search_one").val("");
    jq("#search_two").val("");
  }
  
  function textLarger() {
    fontsize = fontsize + 4;
    if (fontsize  > 72) {
      fontsize = 72;
    }
    var date = new Date();
    date.setTime(date.getTime() + (86400 * 365 * 67));
    document.cookie = "font_size=" + fontsize + "; expires=" + date.toGMTString() + "; path=/";
    location.replace("{$self_name}");
  }

  function textSmaller() {
    fontsize = fontsize - 4;
    if (fontsize  < 6) {
      fontsize = 6;
    }
    var date = new Date();
    date.setTime(date.getTime() + (86400 * 365 * 67));
    document.cookie = "font_size=" + fontsize + "; expires=" + date.toGMTString() + "; path=/";
    location.replace("{$self_name}");
  }

  function fontEntry() {
    var newfont = prompt("Current font: {$font}. Enter desired font: ", "Helvetica");
    if ((newfont != "") && (newfont != "{$font}")) {
      var date = new Date();
      date.setTime(date.getTime() + (86400 * 365 * 67));
      document.cookie = "font_family=" + newfont + "; expires=" + date.toGMTString() + "; path=/";
      location.replace("{$self_name}");
    }
  }
// Text color for contrast
  function toggleBW() {
    var date = new Date();
    date.setTime(date.getTime() + (86400 * 365 * 67));
    if (color == "black") {
      jq("body").css("color", "white");
      jq("body").css("background-color", "black");
      jq(".row").css("color", "white");
      jq(".inbox").css("background-color", "black");      
      color = "white";
      document.cookie = "text_color=white; expires=" + date.toGMTString() + "; path=/";
    } else {
      jq("body").css("color", "black");
      jq("body").css("background-color", "white");      
      jq(".row").css("color", "black");
      jq(".inbox").css("background-color", "white");      
      color = "black";
      document.cookie = "text_color=black; expires=" + date.toGMTString() + "; path=/";
    }
  }

  function shownLimit() {
    var newlimit = prompt("Current limit of Tweats and Search Results: {$shown_limit}. Enter desired limit: ", "50");
    if ((newlimit != "") && (newlimit != "{$shown_limit}")) {
      if ((newlimit == "") || (newlimit + 1 == 1) || (newlimit.indexOf("-") >= 0)) {
        newlimit = 50;
      }
      var date = new Date();
      date.setTime(date.getTime() + (86400 * 365 * 67));
      document.cookie = "shown_limit=" + newlimit + "; expires=" + date.toGMTString() + "; path=/";
      location.replace("{$self_name}");
    }
  }
  
  function tweatWidth() {
    var newwidth = prompt("Current width of Tweats display: {$tweat_width} characters. Enter desired width: ", "80");
    if ((newwidth != "") && (newwidth != "{$tweat_width}")) {
      if ((newwidth == "") || (newwidth + 1 == 1) || (newwidth.indexOf("-") >= 0)) {
        newwidth = 80;
      }
      var date = new Date();
      date.setTime(date.getTime() + (86400 * 365 * 67));
      document.cookie = "tweat_width=" + newwidth + "; expires=" + date.toGMTString() + "; path=/";
      location.replace("{$self_name}");
    }
  }
  
  function viewUser(user) {
    window.open("{$self_name}?view_user_name=" + user);
  }

  function settings() {
    var chosen = prompt("Would you like to change your password or your email address? (password or email)", "");
    if (chosen.toLowerCase() == "password") {
      window.open("change_password.php");
    } else if (chosen.toLowerCase().substring(0,5) == "email") {
      var emailAddress = prompt("Enter your new email address or just press OK to have no email address:", "");
      if (emailAddress == "") {
        emailAddress = null;
      }
      location.replace("{$self_name}?new_email_address=" + emailAddress);
    }
  }

  function notifications() {
    var notify = prompt("Would you like email Tweat Notifications of Tweats posted by people " + 
      "you're following (Add apache@crandall.altervista.org to your contact list)? (Yes or No)", "");
    if (notify.trim().toLowerCase().substr(0,1) == "y") {
      location.replace("{$self_name}?notify=1");
    } else {
      location.replace("{$self_name}?notify=0");  
    }
  }
  
  function hashtagSearch() {
    var hashtag = jq("#hashtag_search").val();
    hashtag = hashtag.trim().toLowerCase();
    if (hashtag.substr(0,1) == "#") {
      hashtag = hashtag.substr(1);
    }
    hashtag = hashtag.replace(/\*/g, "%2A");
    hashtag = hashtag.replace(/\?/g, "%3F");
    hashtag = hashtag.replace(/ /g, "");
    window.open("hashtag_search_results.php?hashtag_search=" + hashtag + "&admin={$status}");
  }
  
  function chatToggle(mode) {
    var date = new Date();
    // 5 minute timeout if user doesn't send a Tweat
    var chatTimeout = Math.floor(date.getTime()/1000) + 300;
    date.setTime(date.getTime() + (86400 * 365 * 67));
    if (mode == true) {
      if (jq("#picture").width() == 0) {
        jq("#picture").width(saveWidth);
      }
      jq("#pic_top").html("");
      jq("#pic_bottom").html(picHtml);
      jq("#picture").width(jq("#picture").width() * pic_scale);
      document.cookie = "pic_position=Bottom; expires=" + date.toGMTString() + "; path=/";
      document.cookie = "chat_timeout=" + chatTimeout + "; expires=" + date.toGMTString() + "; path=/";
    }
    document.cookie = "chat=" + mode + "; expires=" + date.toGMTString() + "; path=/";
    location.replace("{$self_name}?chat=" + mode);
  }
  
  jq(document).ready(function() {
    jq('#tweat').keypress(function(e) {
      if (e.which == 13) {
        jq('#tweatform').submit();
        e.preventDefault();
      }
    });
  });
  
//-->
</script>
EODJ;
  echo "</head><body background='pictures/backviolet.png'  
    style='color:black;background-color:#c0c0f0;padding:8px;font-family:{$font};font-size:{$font_size}px'>";
  require_once '_header.php';
  
  if (strlen($message) > 0) {
    echo "<div class='container'><p style='font-size:{$bigfont}px;color:red'>{$message}</p></div><br />";
    $message = "";
  }

  $interests = "";
  $row_interests = $row['interests'];
// Update information and interests
  if (isset($_REQUEST['interests'])) {
    $interests = $_REQUEST['interests'];
    if ($interests == "") {
      $interests_names = "  " . $user_name . " " . $name . " ";
      
    $old_interests = str_replace("-", " ", substr(strtolower($row_interests), 0, 250));
    $old_interests = trim(strtr($old_interests, '!"#%&()*+,-./:;<=>?[\]^_`{|}~' . 
    '�������������', '                                                  ' . 
    '                                       '));
    $new_interests = str_replace("-", " ", substr(strtolower($interests), 0, 250));
    $new_interests = trim(strtr($new_interests, '!"#%&()*+,-./:;<=>?[\]^_`{|}~' . 
    '�������������', '                                                  ' . 
    '                                       '));
    $old_interests = str_replace("   ", " ", $old_interests);
    $old_interests = str_replace("  ", " ", $old_interests);
    
    if ((strlen($old_interests) == NULL) || (strlen($old_interests) < 1)) {
      $old_interests = " ";
    }
    if ((strlen($new_interests) == NULL) || (strlen($new_interests) < 1)) {
      $new_interests = " ";
    }
    
    $new_interests = str_replace("   ", " ", $new_interests);
    $new_interests = str_replace("  ", " ", $new_interests);
    
    $old_interests = strtolower($user_name) . " " . strtolower($name) . " " . $old_interests;
    $new_interests = strtolower($user_name) . " " . strtolower($name) . " " . $new_interests;
    $old_interests_array = array_unique(explode(" ", $old_interests));
    $new_interests_array = array_unique(explode(" ", $new_interests));
    $old_interests = "  " . $old_interests . " ";
    $new_interests = "  " . $new_interests . " ";

    if (mb_check_encoding($old_interests, 'UTF-8' ) === true ) {
        $stmt->prepare("SET NAMES 'utf8'");
        $stmt->execute();
    }
    if (mb_check_encoding($new_interests, 'UTF-8' ) === true ) {
        $stmt->prepare("SET NAMES 'utf8'");
        $stmt->execute();
    }
    
    foreach ($new_interests_array as $new_item) {
      if ((strlen($new_item) > 0) && (strpos($old_interests, " " . $new_item . " ") == false)) {
        $stmt->prepare("INSERT INTO interests (id, user_name, interest) values(NULL, ?,?)");
        mysqli_set_charset('$con', 'utf8mb4');
        $stmt->bind_param('ss', $user_name, $new_item);
        $stmt->execute();
      }
    }
    foreach ($old_interests_array as $old_item) {
      if ((strlen($old_item) > 0) && (strpos($new_interests, " " . $old_item . " ") == false)) {
        $stmt->prepare("DELETE FROM interests WHERE user_name = ? AND interest = ?");
        mysqli_set_charset('$con', 'utf8mb4');
        $stmt->bind_param('ss', $user_name, $old_item);
        $stmt->execute();
      }
    }
    
    $stmt->prepare("UPDATE " . DATABASE_TABLE . " SET interests_words = ? " . 
      "WHERE ((user_name = ?) OR (email = ?)) AND (binary password_hash = ?)");
    $stmt->bind_param('ssss', $new_interests, $user_name, $user_name, crypt($password,CRYPT_SALT));
    $stmt->execute();
    }
  } else {
    $interests = $row_interests;
  }
  if ($interests == "    ") {
    $interests = "";
  }
  
  mysqli_close($con);
  
  if ($font_size == FONTSIZE) {
    $input_width = 113;
  } else {
    $input_width = floor(2000 / $font_size);
  }
  $bigfont = $font_size * 1.5;
// Picture position adjustment
  if ($picture_ext == NULL) {
    $disable_photo_adjust = "disabled";
  } else {
    $disable_photo_adjust = "";
  }
  
  echo <<<EODT
<div class="container" style="position:relative;top:-16px">
  <div class='row'>
    <div class='col-md-3' style="background-color:#6644CC;text-align:center;height:259px;width:334px;
    margin-left: -53px;margin-right: 4px;padding: 10px;border: 4px outset violet">
    <form role="form">
      <div><a href="{$self_name}" style="font-size:{$bigfont}px;color:red;background-color:#990099"><b>
&nbsp;Tweater&nbsp;</b></a>
        <select class="inbox" id="selsize" {$disable_photo_adjust}>
          <option value="Caption" default>Adjust Picture:</option>
          <option value="Show">Show</option>
          <option value="Hide">Hide</option>
          <option value="Top">At the Top</option>
          <option value="Bottom">At the Bottom</option>
          <option value="Background">Full Background</option>
          <option value="Tile">Tiled Background</option>
          <option value="Double">Double the Size</option>
          <option value="Half">Half the Size</option>
        </select>
      </div>
    </form>
    <form role="form">
      <div class="form-group" style="text-align:center">
        <select id="selview" class="inbox" onchange='viewUser(this.value)'>
EODT;

// Get followers count 
  $mysqli4 = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
  $stmt4 = $mysqli4->prepare("SELECT COUNT(DISTINCT user_name) AS followers_count FROM followed_ones WHERE followed_one = ?");
  $stmt4->bind_param('s', $user_name);
  $stmt4->execute();
  $result4 = $stmt4->get_result();
  if ($myrow4 = $result4->fetch_assoc()) {
    $followers_count = $myrow4['followers_count'] - 1;
  } else {
    $followers_count = 0;
  }
  $stmt4->close();
  $mysqli4->close();

// List followed users with links to their pages
  $mysqli3 = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
  $mysqli3->set_charset("utf8");
  $stmt3 = $mysqli3->prepare("SELECT DISTINCT u.name, u.id, f.followed_one FROM followed_ones AS f " . 
    "INNER JOIN users AS u ON f.followed_one = u.user_name WHERE f.followed_one IN " . 
    "(SELECT f.followed_one FROM followed_ones AS f WHERE f.user_name = ?) ORDER BY name");
  $stmt3->bind_param('s', $user_name);
  $stmt3->execute();
  $result3 = $stmt3->get_result();

  echo "          <option>Followed Users:</option>";
  while ($myrow3 = $result3->fetch_assoc()) {
    if ($myrow3['followed_one'] != $user_name) {
      echo "          <option value='" . $myrow3['followed_one'] . "'>" . 
        wordwrap($myrow3['name'], 30, '<br />', true) . " (" . 
        wordwrap($myrow3['followed_one'], 30, '<br />', true) . ")</option>";
    }
  }
  $stmt3->close();
  $mysqli3->close();
  echo <<<EODF2
        </select>
      </div>
      <div style="text-align:center">
        <button type="button" class="btn btn-warning" onclick="notifications();">Notifications</button>
      &nbsp;{$followers_count} Followers
      </div>
    </form>
EODF2;

  $esc_name = str_replace(" ", "+", $name);
  if ($chat == 'true') {
    $chat_button = 'danger';
    $chat_button_action = 'Stop';
    $chat_toggle = 'false';
  } else {
    $chat_button = 'success';
    $chat_button_action = 'Start';
    $chat_toggle = 'true';
  }
// Interests and Information and Tweat Entry
  echo <<<EODF
    <form action="{$self_name}" method="POST" role="form" id="intinfo" name="intinfo" class="intinfo">
      <span>
        <div>
          <fieldset class="fieldset-auto-width" style="float:left">
            <b>Interests and Information:&nbsp;&nbsp;&nbsp;</b>
            <button type="submit" id="intsubmit" name="intsubmit" class="btn btn-info" style="margin-left:-9px;position:relative;left:2px">Update</button>
            <input type="hidden" name="message" value="Updated Interests and Information! (Limit: {$tweat_max_size} bytes.)" />
            <div class="span3 form-group">
              <textarea class="textarea inbox" rows="4" cols="36" id="interests" name="interests" maxlength="{$tweat_max_size}" 
  placeholder="You may type your interests and information here and press Update." style="font-size:{$fontsize};height:80px">{$interests}
              </textarea>
            </div>
          </fieldset>
        </div>
      </span>
    </form><nobr>
  </div>
</div>


<div class='col-md-9' style='float:right;background-color:#9999FF;margin-left: 0px;margin-right: 6px;border: 4px outset 
  darkblue;padding:10px;height:259px;width:854px;position:absolute;top:0px;left:306px'>
<form action="{$self_name}" method="POST" role="form" id="tweatform"></nobr>
<span>
<div ng-app="">
<fieldset class="fieldset-auto-width" style="float:left">
<div class="span9 form-group" style="height:170px">
<textarea class="textarea inbox" rows="4" cols="83" id="tweat" name="tweat" autofocus ng-model="tweat" 
  maxlength="{$tweat_max_size}" placeholder=
  "--Type your Tweat here (limit: {$tweat_max_size} characters) and then click the Post button or press Enter.--">
  </textarea><br />
<button type="submit" class="btn btn-success">Post&nbsp;<span class="glyphicon glyphicon-send"></span></button>
<span style="font-family:Courier New, monospace">
<span ng-bind="('0000' + ({$tweat_max_size} - tweat.length)).slice(-3)"></span> characters left
</span>
<span><button type="button" class="btn btn-warning" onclick="textErase();">Erase <span style='color:black;background-color:red'>&nbsp;X&nbsp;</span>
</button>
<button type="button" class="btn btn-success" style="width:90px" onclick="textLarger();">Text Size<span class="glyphicon glyphicon-zoom-in"></span></button>
<button type="button" class="btn btn-primary" style="padding-left:2px;padding-right:2px;width:84px" onclick="textSmaller();">Text Size
<span class="glyphicon glyphicon-zoom-out"></span></button>
<button type="button" class="btn btn-info" onclick="fontEntry();">Font</button>
<button type="button" class="btn btn-primary" style="padding-left:2px;padding-right:2px;width:47px" onclick="toggleBW();">B/W</button>
<button type="button" class="btn btn-info" style="position:relative;left:-1px" onclick="tweatWidth();">Width</button>&nbsp;
<button type="submit" style="padding-left:3px;padding-right:3px;width:70px" class="btn btn-{$chat_button}" onclick="chatToggle({$chat_toggle})" 
 style="position:relative;left:-6px">{$chat_button_action} Chat</button>
<input type="hidden" class="form-control" name="name" value="{$esc_name}" /><br />
<span style="position:relative;top:3px">Hashtag Search: #</span><input type="text" id="hashtag_search" style="font-size:{$fontsize};width:450px;position:relative;top:5px"
  name="hashtag_search" maxlength="30" placeholder="To search Tweats, type the hashtag here and press--&gt;" />
  <button type="button" class="btn btn-primary" onclick="hashtagSearch();" style="margin:2px">Hashtag <span class="glyphicon glyphicon-search"></span></button>&nbsp;
<button type="button" class="btn btn-warning" onclick="shownLimit();" style="padding-left:3px;padding-right:3px">Limit: {$shown_limit}</button>
</span><br /></div></fieldset></div></form>
<form action="user_search_results.php?admin={$status}" method="POST" role="form" target="_blank" id="user_search_form"><br />
<span style="position:relative;top:-22px">User Search: </span><input type="text" id="search_any" name="search_any" size="72" maxlength="250" 
  style="position:relative;top:-19px;height:26px;font-size:{$fontsize}" placeholder="To search by interests, info or names, type them here and press--&gt;" />&nbsp;<button type="submit" class="btn btn-info" style="position:relative;top:-22px">User <span class="glyphicon glyphicon-user"></span>
<span class="glyphicon glyphicon-search"></span></button><br />
</form>
<form action="boolean_user_search_results.php?admin={$status}" method="POST" role="form" target="_blank"><br />
<nobr><span style="position:relative;top:-46px">Boolean Search: 
  <input type="text" style="position:relative;top:3px" placeholder="First Search Term" id="search_one" 
  name="search_one" maxlength="30" size="26" />
  <select class="inbox" id="search_type" name="search_type" style="position:relative;left:-5px;top:1px">
    <option value="AND" default>AND</option>
    <option value="OR">OR</option>
    <option value="NOT">NOT</option>
  </select>
  <input type="text" style="position:relative;top:3px;left:-6px" placeholder="Second Search Term" id="search_two" name="search_two" value="" maxlength="30" size="26" />
  <button type="submit" class="btn btn-warning" style="padding-left:2px;padding-right:2px;position:relative;top:-2px;left:-6px">Boolean User <span class="glyphicon glyphicon-search"></span></button></nobr>
</span>
</form>
</span></div>
<div class='row'>
EODF;

// Display Tweats
  if ($chat == 'true') {
// Display Tweats as Chat in iframe
    $name = strtr($name, " ", "+");
    echo "<iframe id='tweats_iframe' src='get_tweats.php?name={$name}' style='width:1250px;height:590px;position:absolute;" . 
      "left:0px'><p>Your browser doesn't support iframes!</p></iframe>";
    echo "<p style='position:relative;left:20px;top:590px'><i>Note:&nbsp;&nbsp;The creator of this website " . 
        "doesn't assume responsibility for its usage by others.</i></p>";
    echo "<br /><img id='picture' src='pictures/{$picture_url}' style='position:relative;top:570px' />";
    echo "<p style='position:relative;top:590px'>&nbsp;</p>";
  } else {
// Display Tweats as non-Chat using Angular.js and JSON
$endScript = '</' . 'script>';
echo '<div><div ng-repeat="tw in allTweats"><p ng-class-odd="\'stripedodd\'" ng-class-even="\'stripedeven\'" style="position:relative;left:20px;">
{{ tw.Name.replace(\'quotmk\',\'"\').replace(\'quotmk\',\'"\') }}:&nbsp;
{{ tw.Tweat.replace(\'quotmk\',\'"\').replace(\'quotmk\',\'"\') }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
</div>';
//{{ tw.Tid }}
    echo "<div id='pic_top' style='position:relative;left:7px;top:-12px'><img id='top' src='transparent.gif' onload='startPic();' /></div></div></div>";
// Get Tweats from followed users and signed-in user for non-Chat Mode
    echo "</div>";
// Disclaimer    
  echo "<div style='text-align:center'><br /><i>Note:&nbsp;&nbsp;The creator of this website " . 
    "doesn't assume responsibility for its usage by others.</i><br /><br />" . 
    "<div class='row' style='color:black'><div class='col-md-3 text-right'>" . 
    "<div id='pic_bottom' style='position:absolute;left:7px'>";
  echo "<img id='bottom' src='transparent.gif' />";
  echo '</div><div class="col-md-9"></div></div>&nbsp;<br />&nbsp;<br />&nbsp;</div>
  <script>
var app = angular.module("myApptw", []);
app.controller("twCtrl", ["$scope", "$http", function($scope, $http) {
  $http.get("http://crandall.altervista.org/tweater/angular_tweats_JSON.php").then(function (response) {$scope.allTweats = response.data.records;});
}]);' . $endScript . '</div></div></body></html>';
  }
  exit();
