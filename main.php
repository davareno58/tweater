<?php
  $tweat_max_size = TWEATMAXSIZE;
  $site_root = SITE_ROOT;
  $self_name = $_SERVER['PHP_SELF'];

  if (isset($_REQUEST['message'])) {
    $message = strtr($_REQUEST['message'], "+", " ");
  } else {  
    $message = "";
  }
  
// Get various cookies data
  if (isset($_COOKIE['pic_scale'])) {
    $pic_scale = $_COOKIE['pic_scale']; // Uploaded image scale multiplier
    if ($pic_scale > 16) {
      $pic_scale = 16; // Scale upper limit
    }
    if ($pic_scale <= 0.01) {
      $pic_scale = 1/64; // Scale lower limit
    }
  } else {
    $pic_scale = 1; // Default scale is full-size
  }

  if (isset($_COOKIE['pic_position'])) { // Uploaded image position
    $pic_position = $_COOKIE['pic_position'];
  } else {
    $pic_position = "Top"; // Default is above Tweats
  }
  
  if (isset($_COOKIE['pic_visible'])) { // Uploaded image visibility
    $pic_visible = $_COOKIE['pic_visible'];
  } else {
    $pic_visible = "Show"; // Default is visible
  }
    
  if (isset($_COOKIE['text_color'])) { // Text color can be changed to white for contrast with image in background
    $text_color = $_COOKIE['text_color'];
  } else {
    $text_color = "black";
  }
  
  if (isset($_COOKIE['font_size'])) { // Text size can be adjusted, especially for the vision-impaired
    $font_size = $_COOKIE['font_size'];
  } else {
    $font_size = FONTSIZE;
  }
  $bigfont = $font_size * 1.5; // Large text size

  if (isset($_COOKIE['tweat_width'])) { // Display width of Tweats is adjustable
    $tweat_width = $_COOKIE['tweat_width'];
  } else {
    $tweat_width = floor(1600 / $font_size);
  }
   
  if (isset($_COOKIE['shown_limit'])) { // Maximum number of Tweats and Search Results is adjustable
    $shown_limit = $_COOKIE['shown_limit'];
  } else {
    $shown_limit = 50;
  }
  
  if (isset($_COOKIE['font_family'])) { // Font is adjustable
    $font = $_COOKIE['font_family'] . ", Helvetica";
  } else {
    $font = "Helvetica";
  }

  $chat = 'false';
  if (isset($_COOKIE['chat'])) { // Chat mode refreshes Tweat display every 10 seconds for real-time conversation
    $chat = $_COOKIE['chat'];
  }

// Process unsubscribe request
  if (isset($_COOKIE['unsub']) && isset($_COOKIE['user_name']) && isset($_COOKIE['password'])) {
    $user_name = trim($_COOKIE['user_name']);
    $password = trim($_COOKIE['password']);
    
    $con = mysqli_connect(DATABASE_HOST,USERNAME,'',DATABASE_NAME); // Connect to database
    if (!$con) {
      die('Could not connect: ' . mysqli_error($con));
    }
    mysqli_select_db($con,DATABASE_TABLE); // Use users table
    $stmt = $con->stmt_init();
    $stmt->prepare("DELETE FROM " . DATABASE_TABLE . 
      " WHERE ((user_name = ?) OR (email = ?)) AND (binary password_hash = ?)");
    $stmt->bind_param('sss', $user_name, $user_name, crypt($password,CRYPT_SALT));
    $stmt->execute(); // Delete user's data (cascades to delete data from other tables)
    mysqli_close($con);
    setcookie('user_name', "", time() - 7200, "/"); // Set cookies to be deleted
    setcookie('password', "", time() - 7200, "/");
    setcookie('unsub', "", time() - 7200, "/");
// Display good-bye page
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
<BODY style='background-color:#99D9EA;font-family:{$font};font-size:{$font_size}px' LINK="#C00000" VLINK="#800080" alink="#FFFF00" bgcolor="00D0C0" onLoad="openit();">
<h1 style='text-align:center'>Tweater: You are now unsubscribed to Tweater. Sorry to see you go!<br />(Actually I'm a computer and have no human feelings!)</h1>
<h2 style='text-align:center'><a href="{$self_name}">Click here to sign in another user or register a new user.</a></h2>
<img src='tweatysad.png' /></BODY>
</HTML>
EODU;
    exit();
  }

  if (isset($_COOKIE['user_name']) && isset($_COOKIE['password']) && (strlen($_COOKIE['password']) > 0)) {
// Get automatic signin data with username and password stored in cookies
    $user_name = trim($_COOKIE['user_name']);
    $password = trim($_COOKIE['password']);
    $stay_logged_in = $_POST['stay_logged_in'];
  } else {
// Get manual signin data
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
      $hashtag_pos = strpos($tweat, "#"); // Look for hashtag (Tweat subject marker)
      if ($hashtag_pos === false) {
        $hashtag = NULL;
      } else {
        $hashtag_pos = $hashtag_pos + 1;
        $start = $hashtag_pos;
        while (($hashtag_pos < strlen($tweat)) && (strpos(" ,.?!:;*/()-+{}[]|\"<>\\\`", 
          substr($tweat, $hashtag_pos, 1)) === false)) {
          $hashtag_pos = $hashtag_pos + 1; // Find end of hashtag
        }
        $hashtag = trim(strtolower(substr($tweat, $start, $hashtag_pos - $start)));
      }
      if ($chat == "true") {
        $hashtag = "DEL" . (time() + 86400); // In Chat Mode, store delete time instead of hashtag
      }
      if ($stmt = $mysqli3->prepare("INSERT INTO tweats (id, user_name, tweat, hashtag) values(NULL,?,?,?)")) {
        $mysqli3->set_charset('utf8mb4');
        $stmt->bind_param('sss', $user_name, $tweat, $hashtag);
        $stmt->execute(); // Store Tweat and hashtag in database
        if (mysqli_connect_errno()) {
          $message = "ERROR: Tweat not posted! Sorry, but something went wrong.<br />" . 
            "You may try to post the Tweat again. ";
        } else {
          $stmt = $mysqli3->prepare("SELECT user_name, name, tweat_notify, email FROM users WHERE user_name IN " . 
            "(SELECT user_name FROM followed_ones WHERE followed_one = ? AND user_name != followed_one)");
          $mysqli3->set_charset('utf8mb4');
          $stmt->bind_param('s', $user_name);
          $stmt->execute(); // Check if email Tweat notification desired by user
          $result = $stmt->get_result();
          while ($row = mysqli_fetch_array($result)) {
            $email = $row['email'];
// Send Email Tweat Notification(s)
            if (($chat != "true") && ($row['tweat_notify'] == 1) && (strpos($email, "@") > 0)) { // Send email
              $email_header = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\n";
              mail($email, 'Tweat Notification: ' . $name . ' (' . $user_name . ') just posted this Tweat',
                'Hello ' . $row['name'] . ',<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $name . ' (' . 
                $user_name . ') just posted this Tweat:<br /><br />' . wordwrap($tweat, 70, '<br />', true) . 
                '<br /><br />If you don\'t want to receive Tweat Notifications, please ' . 
                'sign in to your Tweat account at http://crandall.altervista.org/tweater<br />' . 
                'and click on the Tweat Notifications button at the left. A pop-up prompt ' . 
                'will appear. Type the word No and click on OK.<br /><br />' . 
                '<a href="http://crandall.altervista.org/tweater" style="font-size:40px;color:red;background-color:#990099">' . 
                '<b>&nbsp;Tweater&nbsp;</b></a><br /><br />', $email_header);
            } else if ($chat == "true") {
              setcookie('chat_timeout', time() + 300, time() + 7200, "/"); // Reset Chat Mode timeout after Tweat
            }
          }
        }
      }
    }
    $stmt->close();
    $mysqli3->close();
  }
// Check administrator status with username and password hash of case-sensitive password
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
        $message = "The Tweat was deleted.";
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
// Forgot password, so email password reset code if email address exists or username appears to be email address
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
      echo "<p style='color:red'>Sorry, but I don't have an email address to send the password reset " .
        "code to.<br />Suggestion: Register as a new user and enter an email address at the bottom of " . 
        "the home page,<br />in case you forget your password again.</p>";
    } else {
// Generate pseudo-random 10-character password reset code and store it in database and email it to user
      $password_reset_code = chr(rand(97, 122)) . chr(rand(97, 122)) . chr(rand(97, 122)) .
        chr(rand(97, 122)) . chr(rand(97, 122)) . chr(rand(97, 122)) . chr(rand(97, 122)) . 
        chr(rand(97, 122)) . chr(rand(97, 122)) . chr(rand(97, 122));
      $email_header = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\n";
      mail($email, 'Password reset code for ' . $row['name']. '\'s Tweater account',
        '<html><body>' . $row['name'] . ',<br /><br />Here is the requested password reset code for your Tweater account: ' . 
        $password_reset_code . '<br /><br />' . 
        '<a href="http://crandall.altervista.org/tweater" style="font-size:40px;color:red;background-color:#990099">' . 
        '<b>&nbsp;Tweater&nbsp;</b></a><br /><br /></html></body>', $email_header);
      $stmt->prepare("update " . DATABASE_TABLE . " SET password_reset_hash = ? where (user_name = ?) or (email = ?)");
      $stmt->bind_param('sss', crypt($password_reset_code,CRYPT_SALT), $user_name, $user_name);
      $stmt->execute();
// Display password reset page with Turing test
      echo <<<EOD
<!DOCTYPE html><html>
  <head><title>Password Reset</title>
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
      require_once '_shim.php'; // Shim for MSIE
      echo "</head><body style='background-color:#99D9EA;padding:8px;font-family:{$font};" . 
        "font-size:{$font_size}px' onload='turingsetup();'>";
      require_once $header; // Menu buttons at top of page
      if (strlen($message) > 0) { // Display message if any
        echo "<div class='container'><p style='font-size:{$bigfont}px;color:red'>{$message}</p></div>";
        $message = "";
      }
// Enter password reset code and choose new password
      echo <<<EOD3
<img src='tweatyquestion.png' style='float:right' />
A password reset code has been sent by the Apache server to your email address<br />
(or to the email address in your username). If you don't see it there, be sure to<br />
check your spam folder. Please enter it here, along with the new password that<br />
you would like to use:<br />
<br />
<form action="forgot_password.php?return={$ret}" method="POST">
<span>
<div>
<fieldset class="fieldset-auto-width" style="float:left">
<legend>Password Reset:</legend>
<input type="text" style="display:none">
<input type="password" style="display:none">
<div class="input-group"><input type="text" class="form-control" placeholder="Password Reset Code" 
  name="given_password_reset_code" autocomplete="off" maxlength="20" size=20></div>
<div class="input-group"><input type="password" class="form-control" placeholder="New Password" 
  name="password" autocomplete="off" maxlength="32" size="32"></div>
<div class="input-group"><input type="password" class="form-control" placeholder="Confirm New Password" 
  autocomplete="off" name="password_confirm" maxlength="32" size=32></div>
<div class="input-group"><img src="qtblue.png" /><span id="firstnumber" name="firstnumber"> </span>
<img src="sablue.png" /> 
<span id="secondnumber" name="secondnumber"> </span>? <input type="text" name="given_added" 
  autocomplete="off" size="5">
<input type="hidden" class="form-control" id="added" name="added" value="101" size="5"></div>
<div class="input-group"><input type="hidden" class="form-control" name="given_user_name" 
  value={$user_name}></div>
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
// Sign in
  $stmt->prepare("SELECT * FROM " . DATABASE_TABLE . 
    " WHERE ((user_name = ?) OR (email = ?)) AND (binary password_hash = ?)");
  $stmt->bind_param('sss', $user_name, $user_name, crypt($password,CRYPT_SALT));
  $stmt->execute();
  $result = $stmt->get_result();
  $num_rows = $result->num_rows;
  if ((!$result) || ($num_rows == 0)) { // Sign In failure, so display Sign In/Registration with error message
    echo <<<EOD
<!DOCTYPE html><html>
  <head><title>Tweater</title>
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
    alert("Tweater is an app created by David Crandall, to show his programming skills using PHP, MySQL, " +
      "Bootstrap, Angular.js, JavaScript, HTML and CSS.");
  };
 
  function contact() {
    alert("David Crandall's email is crandadk@aol.com");
  };
-->
</SCRIPT>
EOD;
    require_once '_shim.php';
    echo "</head><body style='background-color:#99D9EA;padding:8px;font-family:{$font};
      font-size:{$font_size}px' onload='turingsetup();'>";
    require_once $header;
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
<div style="margin-left: auto; margin-right: auto;"><p style="text-align:center"><img src='tweaty.png' style='width:15%;height:15%' />
<a href="{$self_name}" style="font-size:72px;color:red;background-color:violet"><b>
&nbsp;Tweater&nbsp;</b></a><img src='tweatyleft.png' style='width:15%;height:15%' />
</p></div>
<div style="margin-left: auto; margin-right: auto;position: relative;{$title_position}">
<form action="{$self_name}" method="POST" id="action">
<span>
<div>
<fieldset class="fieldset-auto-width" style="float:left;background-color:#A0C0A0;{$sign_in_width}">
<legend>Sign In:</legend>
<div class="input-group"><input type="text" class="form-control" placeholder="Username or Email" 
  name="user_name" id="user_name" maxlength="50" size="60"></div>
<div class="input-group"><input type="password" class="form-control" placeholder="Password" 
  name="password" maxlength="32" size="32"></div>
<div class="checkbox"><label><input type="checkbox" name="forgot_password" 
  unchecked>I forgot my password.</label></div>
<div class="checkbox"><label><input type="checkbox" name="stay_logged_in" 
  unchecked>Remain signed in.</label></div>
<button type="submit" class="btn btn-success">Sign In</button>
</fieldset>
</div>
</span>
</form>
<div style="float:left">
<br /><br /><br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
OR&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</div>
<form action="register.php?return={$ret}" method="POST" autocomplete="off">
<span>
<div>
<fieldset class="fieldset-auto-width" style="float:left;background-color:#A0A0C0">
<legend style="background-color:#A0A0C0;{$sign_in_width}">Register New User:</legend>
<input type="text" style="display:none">
<input type="password" style="display:none">
<div class="input-group"><input type="text" class="form-control" autocomplete="off" 
  placeholder="Desired Username" name="user_name" value="{$user_name}" maxlength="50" size="50"></div>
<div class="input-group"><input type="password" class="form-control" autocomplete="off" 
  placeholder="Password: Minimum 6 Characters" name="new_user_password" maxlength="32" size="32"></div>
<div class="input-group"><input type="password" class="form-control" autocomplete="off" 
  placeholder="Confirm Password" name="password_confirm" maxlength="32" size="32"></div>
<div class="input-group"><input type="text" class="form-control" autocomplete="off" 
  placeholder="Name" name="name" value="{$name}" maxlength="60" size="60"></div>
<div class="input-group"><input type="text" class="form-control" autocomplete="off" 
placeholder="Optional: Your Email for Tweat Notifications" name="email" value="{$email}" 
  autocomplete="off" maxlength="50" size="50"></div>
<div class="input-group"><img src="qt.png" /><span id="firstnumber" name="firstnumber"> </span>
  <img src="sa.png" /> 
<span id="secondnumber" name="secondnumber"> </span>? <input type="text" name="given_added" 
  autocomplete="off" size="3"><br />
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
// Set signed-in cookie if not yet set
  if (!isset($_COOKIE['user_name']) || !isset($_COOKIE['password'])) {
    setcookie('user_name', $user_name, 0, "/");
    setcookie('password', $password, 0, "/");
  }
// Show another user's Public Page (profile)
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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script src= "http://ajax.googleapis.com/ajax/libs/angularjs/1.2.26/angular.min.js"></script>
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
    alert("Tweater is an app created by David Crandall, to show his programming skills using PHP, MySQL, " +
      "Bootstrap, Angular.js, JavaScript, HTML and CSS.");
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
    echo "</head><body style='background-color:#99D9EA;padding:8px;font-family:{$font};font-size:{$font_size}px'>";
    require_once $header;
    
    if (strlen($message) > 0) {
      echo "<div class='container'><p style='font-size:{$bigfont}px;color:red'>{$message}</p></div>";
      $message = "";
    }
  
    mysqli_close($con);

    if (strlen($view_name) < 1) {
      $view_name = "Unknown Username";
      $view_user_name = "Not much to see here!";
    }

    echo <<<EODT
<h1><a href="{$self_name}" style="font-size:{$bigfont}px;color:red;background-color:violet"><b>
&nbsp;&nbsp;&nbsp;Tweater&nbsp;&nbsp;&nbsp;</b></a>
<div style="text-shadow: 5px 5px 5px #007F00;">{$view_name}'s Tweater Page ({$view_user_name})&nbsp;&nbsp;
<button type="button" class="btn btn-success" onclick="location.replace(
'follow.php?followed_one={$view_user_name}&followed_name={$view_name}&return={$ret}');">Follow</button>
<button type="button" class="btn btn-danger" onclick="location.replace(
'unfollow.php?followed_one={$view_user_name}&followed_name={$view_name}&return={$ret}');">Unfollow</button>
</div></h1><br />
<b>Interests and Information:&nbsp;&nbsp;</b>{$view_interests}<br /><br />
<img id='picture' src='pictures/{$picture_url}' /><br /><br />
<b>Tweats:</b><br /><br />
EODT;

    $mysqli2 = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);

    $mysqli2->set_charset("utf8");
    if ($stmt = $mysqli2->prepare("SELECT t.id as tid, t.user_name, t.tweat, u.id, u.name, u.picture_ext " . 
      "FROM tweats AS t INNER JOIN users AS u ON t.user_name = u.user_name WHERE t.user_name = ? " . 
      "ORDER BY t.id DESC LIMIT ?")) {
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
        echo "&nbsp;&nbsp;<img src='xdel.png' style='position:relative;top:-2px' " . 
          "onclick='if (confirm(\"Are you sure you want to delete this Tweat?:  " . 
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
  echo "<!DOCTYPE html><html><head><meta charset='utf-8' />";

  echo "<title>" . $name . "'s Tweater Page (Username: " . $row['user_name'] . ")</title>";
// Get icon, Bootstrap, Angular and jQuery
  echo <<<EOD
    <link rel='shortcut icon' href='favicon.png' type='image/png'>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script src= "http://ajax.googleapis.com/ajax/libs/angularjs/1.2.26/angular.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script> 
EOD;

  require_once '_shim.php';
  echo "<script language='JavaScript'>\n<!--\n";
  
  if ($stay_logged_in == "on") { // Stay signed in
    echo "  staySignedIn();\n\n";
  }
  
  echo "var fontsize = {$font_size};";

  $unsubscribe_password = crypt($password,CRYPT_SALT);
    
  echo <<<EODJ
  var saveWidth = $("#picture").width(); // Save image size
  var picHtml = "<img id='picture' src='pictures/{$picture_url}' />"; // Image tag for above Tweats
  var picHtmlBottom = "<img id='picture' src='pictures/{$picture_url}' " +
    "style='position:relative;top:-20px;padding-bottom:20px' />"; // Image tag for below Tweats
  var color = "{$text_color}";
  var pic_scale = {$pic_scale};
  var pic_position = "{$pic_position}";
  var pic_visible = "{$pic_visible}";

  function startPic() { // Initialize image size and position
    if (color == "white") {
      color = "black";
      toggleBW();
    }
    if (pic_position == "Bottom") {
      $("body").attr("background", "pictures/backviolet.png");
      $("#pic_top").html("");
      $("#pic_bottom").html(picHtmlBottom);
    }
    if (pic_position == "Top") {
      $("body").attr("background", "pictures/backviolet.png");
      $("#pic_bottom").html("");
      $("#pic_top").html(picHtml);
    }
    if (pic_position == "Background") {
      $("#pic_top").html("");
      $("#pic_bottom").html("");
      $("body").attr("background", "pictures/{$picture_url}");
      $("body").css("background-size", "cover");
    }
    if (pic_position == "Tile") {
      $("#pic_top").html("");
      $("#pic_bottom").html("");
      $("body").attr("background", "pictures/{$picture_url}");
      $("body").css("background-size", "auto");
      $("body").css("background-repeat", "repeat");
    }
    if (pic_scale != 1) {
      $("#picture").width($("#picture").width() * pic_scale);
    }
  }
  
  $(document).ready(function(){ // Change image size or position
    $("#selsize").change(function(){
      if ($("#picture").width() == 0) {
        $("#picture").width(saveWidth);
      }
      var date = new Date();
      date.setTime(date.getTime() + (86400 * 365 * 67));

      $("body").attr("background", "pictures/backviolet.png");
      $("body").css("background-size", "auto");
      $("body").css("background-repeat", "repeat");
      
      if ($("#selsize").val() == "Top") {
        $("#pic_bottom").html("");
        $("#pic_top").html(picHtml);
        $("#picture").width($("#picture").width() * pic_scale);
        document.cookie = "pic_position=Top; expires=" + date.toGMTString() + "; path=/";
      }
      if ($("#selsize").val() == "Bottom") {
        $("#pic_top").html("");
        $("#pic_bottom").html(picHtmlBottom);
        $("#picture").width($("#picture").width() * pic_scale);
        document.cookie = "pic_position=Bottom; expires=" + date.toGMTString() + "; path=/";
      }
      if ($("#selsize").val() == "Background") {
        $("#pic_top").html("");
        $("#pic_bottom").html("");
        $("body").attr("background", "pictures/{$picture_url}");
        $("body").css("background-size", "cover");
        document.cookie = "pic_position=Background; expires=" + date.toGMTString() + "; path=/";
      }
      if ($("#selsize").val() == "Tile") {
        $("#pic_top").html("");
        $("#pic_bottom").html("");
        $("body").attr("background", "pictures/{$picture_url}");
        $("body").css("background-size", "auto");
        $("body").css("background-repeat", "repeat");
        document.cookie = "pic_position=Tile; expires=" + date.toGMTString() + "; path=/";
      }
      if ($("#selsize").val() == "Double") {
        $("#picture").width($("#picture").width() * 2);
        pic_scale = pic_scale * 2;
        document.cookie = "pic_scale=" + pic_scale + "; expires=" + date.toGMTString() + "; path=/";
      }
      if ($("#selsize").val() == "Half") {
        $("#picture").width($("#picture").width() / 2);
        pic_scale = pic_scale / 2;
        document.cookie = "pic_scale=" + pic_scale + "; expires=" + date.toGMTString() + "; path=/";
      } 
      if ($("#selsize").val() == "Hide") {
        saveWidth = $("#picture").width();
        $("#picture").width(0);
        pic_visible = "Hide";
        document.cookie = "pic_visible=" + pic_visible + "; expires=" + date.toGMTString() + "; path=/";
      }
      if ($("#selsize").val() == "Show") {
        $("#picture").width(saveWidth);
        pic_visible = "Show";
        document.cookie = "pic_visible=" + pic_visible + "; expires=" + date.toGMTString() + "; path=/";
      }      
      $("#selsize").val("Caption");
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
 
  function textErase() { // Erase input fields
    $("#tweat").val("");
    $("#hashtag_search").val("");
    $("#search_any").val("");
    $("#search_one").val("");
    $("#search_two").val("");
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

  function fontEntry() { // Choose font
    var newfont = prompt("Current font: {$font}. Enter desired font: ", "Helvetica");
    if ((newfont != "") && (newfont != "{$font}")) {
      var date = new Date();
      date.setTime(date.getTime() + (86400 * 365 * 67));
      document.cookie = "font_family=" + newfont + "; expires=" + date.toGMTString() + "; path=/";
      location.replace("{$self_name}");
    }
  }
// Text color for contrast
  function toggleBW() { // Toggle font color black/white
    var date = new Date();
    date.setTime(date.getTime() + (86400 * 365 * 67));
    if (color == "black") {
      $("body").css("color", "white");
      $("body").css("background-color", "black");
      $(".row").css("color", "white");
      $(".inbox").css("background-color", "black");      
      color = "white";
      document.cookie = "text_color=white; expires=" + date.toGMTString() + "; path=/";
    } else {
      $("body").css("color", "black");
      $("body").css("background-color", "white");      
      $(".row").css("color", "black");
      $(".inbox").css("background-color", "white");      
      color = "black";
      document.cookie = "text_color=black; expires=" + date.toGMTString() + "; path=/";
    }
  }

  function shownLimit() { // Change maximum number of Tweats and Search Results
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
  
  function tweatWidth() { // Change maximum width of Tweats display
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
  
  function viewUser(user) { // Show another user's Public Page (profile)
    window.open("{$self_name}?view_user_name=" + user);
  }

  function settings() { // Change password or email address
    var chosen = prompt("Would you like to change your password or your email address? (password or email)", "");
    if (chosen.toLowerCase() == "password") {
      window.open("change_password.php?return={$ret}");
    } else if (chosen.toLowerCase().substring(0,5) == "email") {
      var emailAddress = prompt("Enter your new email address or just press OK to have no email address:", "");
      if (emailAddress == "") {
        emailAddress = null;
      }
      location.replace("{$self_name}?new_email_address=" + emailAddress);
    }
  }

  function notifications() { // Set email Tweat Notifications preference
    var notify = prompt("Would you like email Tweat Notifications of Tweats posted by people " + 
      "you're following (Add apache@crandall.altervista.org to your contact list)? (Yes or No)", "");
    if (notify.trim().toLowerCase().substr(0,1) == "y") {
      location.replace("{$self_name}?notify=1");
    } else {
      location.replace("{$self_name}?notify=0");  
    }
  }
  
  function hashtagSearch() { // Search Tweats by hashtag (subject), e.g. #music
    var hashtag = $("#hashtag_search").val();
    hashtag = hashtag.trim().toLowerCase();
    if (hashtag.substr(0,1) == "#") {
      hashtag = hashtag.substr(1);
    }
    hashtag = hashtag.replace(/\*/g, "%2A");
    hashtag = hashtag.replace(/\?/g, "%3F");
    hashtag = hashtag.replace(/ /g, "");
    window.open("hashtag_search_results.php?hashtag_search=" + hashtag + "&admin={$status}&return={$ret}");
  }
  
  function chatToggle(mode) { // Toggle Chat Mode for 10-second Tweats refresh
    var date = new Date();
    // 5 minute timeout if user doesn't send a Tweat
    var chatTimeout = Math.floor(date.getTime()/1000) + 300;
    date.setTime(date.getTime() + (86400 * 365 * 67));
    if (mode == true) {
      if ($("#picture").width() == 0) {
        $("#picture").width(saveWidth);
      }
      $("#pic_top").html("");
      $("#pic_bottom").html(picHtml);
      $("#picture").width($("#picture").width() * pic_scale);
      document.cookie = "pic_position=Bottom; expires=" + date.toGMTString() + "; path=/";
      document.cookie = "chat_timeout=" + chatTimeout + "; expires=" + date.toGMTString() + "; path=/";
    }
    document.cookie = "chat=" + mode + "; expires=" + date.toGMTString() + "; path=/";
    location.replace("{$self_name}?chat=" + mode);
  }
  
  $(document).ready(function() { // Set up Enter key for sending Tweat
    $('#tweat').keypress(function(e) {
      if (e.which == 13) {
        $('#tweatform').submit();
        e.preventDefault();
      }
    });
  });
  
  $(document).ready(function() { // Set up Enter key for sending hashtag search
    $('#hashtag_search').keypress(function(e) {
      if (e.which == 13) {
        hashtagSearch();
        e.preventDefault();
      }
    });
  });
  
  $(document).ready(function() {
    $('#search_any').keypress(function(e) { // Set up Enter key for sending user search by names/info/interests
      if (e.which == 13) {
        $('#user_search_form').submit();
        e.preventDefault();
      }
    });
  });
  
//-->
</script>
EODJ;
// Display user's Home Page
  echo "</head><body background='pictures/backviolet.png' 
    style='color:black;background-color:#99D9EA;padding:8px;font-family:{$font};font-size:{$font_size}px'>";
  require_once $header; // Menu buttons at top
  
  if (strlen($message) > 0) { // Display message if any
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
      $stmt->prepare("UPDATE " . DATABASE_TABLE . " SET interests = NULL, interests_words = ? " . 
        "WHERE ((user_name = ?) OR (email = ?)) AND (binary password_hash = ?)");
      $stmt->bind_param('ssss', $interests_names, $user_name, $user_name, crypt($password,CRYPT_SALT));
      $stmt->execute();
      $stmt->prepare("DELETE FROM interests WHERE user_name = ? AND ? NOT LIKE CONCAT('% ', interest, ' %')");
      $stmt->bind_param('ss', $user_name, $interests_names);
      $stmt->execute();
    } else {
    if ($row_interests != $interests) {
      if (mb_check_encoding($interests, 'UTF-8' ) === true ) {
        $stmt->prepare("SET NAMES 'utf8'");
        $stmt->execute();
      }
      $stmt->prepare("UPDATE " . DATABASE_TABLE . " SET interests = ? " . 
        "WHERE ((user_name = ?) OR (email = ?)) AND (binary password_hash = ?)");
      $stmt->bind_param('ssss', $interests, $user_name, $user_name, crypt($password,CRYPT_SALT));
      $stmt->execute();
      $stmt->prepare("SELECT * FROM " . DATABASE_TABLE . 
        " WHERE ((user_name = ?) OR (email = ?)) AND (binary password_hash = ?)");
      $stmt->bind_param('sss', $user_name, $user_name, crypt($password,CRYPT_SALT));
      $stmt->execute();
      $result = $stmt->get_result();
      $rowi = $result->fetch_assoc();
      $interests = $rowi['interests'];
      $name = $rowi['name'];
      if ($user_name == $rowi['email']) {
        $user_name = $rowi['user_name'];
      }
    }

// Build list of old interests for deleting and list of new interests for adding
    $old_interests = str_replace("-", " ", substr(strtolower($row_interests), 0, 250));
    $old_interests = trim(strtr($old_interests, '!"#%&()*+,-./:;<=>?[\]^_`{|}~' . 
    '¡¦©«¬­®¯´¶¸»¿', '                                                  ' . 
    '                                       '));
    $new_interests = str_replace("-", " ", substr(strtolower($interests), 0, 250));
    $new_interests = trim(strtr($new_interests, '!"#%&()*+,-./:;<=>?[\]^_`{|}~' . 
    '¡¦©«¬­®¯´¶¸»¿', '                                                  ' . 
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
    
// Add username and name to lists of interests
    $old_interests = strtolower($user_name) . " " . strtolower($name) . " " . $old_interests;
    $new_interests = strtolower($user_name) . " " . strtolower($name) . " " . $new_interests;

// Create arrays of unique words from interests
    $old_interests_array = array_unique(explode(" ", $old_interests));
    $new_interests_array = array_unique(explode(" ", $new_interests));
    $old_interests = "  " . $old_interests . " ";
    $new_interests = "  " . $new_interests . " ";

    if (mb_check_encoding($old_interests, 'UTF-8' ) === true ) { // Use UTF-8 character encoding
        $stmt->prepare("SET NAMES 'utf8'");
        $stmt->execute();
    }
    if (mb_check_encoding($new_interests, 'UTF-8' ) === true ) {
        $stmt->prepare("SET NAMES 'utf8'");
        $stmt->execute();
    }
    
    foreach ($new_interests_array as $new_item) { // Add new updated interests to database
      if ((strlen($new_item) > 0) && (strpos($old_interests, " " . $new_item . " ") == false)) {
        $stmt->prepare("INSERT INTO interests (id, user_name, interest) values(NULL, ?,?)");
        mysqli_set_charset('$con', 'utf8mb4');
        $stmt->bind_param('ss', $user_name, $new_item);
        $stmt->execute();
      }
    }
    foreach ($old_interests_array as $old_item) { // Delete old obsolete interests from database
      if ((strlen($old_item) > 0) && (strpos($new_interests, " " . $old_item . " ") == false)) {
        $stmt->prepare("DELETE FROM interests WHERE user_name = ? AND interest = ?");
        mysqli_set_charset('$con', 'utf8mb4');
        $stmt->bind_param('ss', $user_name, $old_item);
        $stmt->execute();
      }
    }

// Store interests list in database
    $stmt->prepare("UPDATE " . DATABASE_TABLE . " SET interests_words = ? " . 
      "WHERE ((user_name = ?) OR (email = ?)) AND (binary password_hash = ?)");
    $stmt->bind_param('ssss', $new_interests, $user_name, $user_name, crypt($password,CRYPT_SALT));
    $stmt->execute();
    }
  } else {
    $interests = $row_interests; // No update of interests
  }
  if ($interests == "    ") {
    $interests = "";
  }
  
  mysqli_close($con);
  
  if ($font_size == FONTSIZE) { // Adjust input width by font size
    $input_width = 113;
  } else {
    $input_width = floor(2000 / $font_size);
  }
  $bigfont = $font_size * 1.5;
// Enable/disable image adjustment
  if ($picture_ext == NULL) {
    $disable_photo_adjust = "disabled";
  } else {
    $disable_photo_adjust = "";
  }

// Display image adjustment drop-down
  echo <<<EODT
<div class="container" style="position:relative;top:-16px">
  <div class='row'>
    <div class='col-md-3' style="background-color:#6644CC;text-align:center;height:259px;width:334px;
    {$margin_left}margin-right: 4px;padding: 10px;border: 4px outset violet">
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
    </form>
  </div>
      <form role="form">
      <div class="form-group" style="text-align:center">
        <select id="selview" class="inbox" onchange='viewUser(this.value)'>
EODT;

// Get followers count 
  $mysqli4 = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
  $stmt4 = $mysqli4->prepare("SELECT COUNT(DISTINCT user_name) AS followers_count FROM followed_ones " .
    "WHERE followed_one = ?");
  $stmt4->bind_param('s', $user_name);
  $stmt4->execute();
  $result4 = $stmt4->get_result();
  if ($myrow4 = $result4->fetch_assoc()) {
    $followers_count = $myrow4['followers_count'] - 1; // Don't count self
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

  echo "<option>Followed Users:</option>";
  while ($myrow3 = $result3->fetch_assoc()) {
    if ($myrow3['followed_one'] != $user_name) {
      echo "<option value='" . $myrow3['followed_one'] . "'>" . 
        wordwrap($myrow3['name'], 30, '<br />', true) . " (" . 
        wordwrap($myrow3['followed_one'], 30, '<br />', true) . ")</option>";
    }
  }
  $stmt3->close();
  $mysqli3->close();

// Display email Tweat notification button
  echo <<<EODF2
        </select>
        <div style="text-align:center"><button type="button" class="btn btn-warning" 
          onclick="notifications();">Notifications</button>&nbsp;{$followers_count} Followers</div></div>
EODF2;

  $esc_name = str_replace(" ", "+", $name);
  if ($chat == 'true') { // Adjust Chat Mode start/stop button and its action
    $chat_button = 'danger';
    $chat_button_action = 'Stop';
    $chat_toggle = 'false';
  } else {
    $chat_button = 'success';
    $chat_button_action = 'Start';
    $chat_toggle = 'true';
  }
