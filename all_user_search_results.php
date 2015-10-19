<?php

// Display User Search Results

  require_once 'app_config.php';
  
  $ret = $_GET['return'];
  if (isset($_COOKIE['font_size'])) {
    $font_size = $_COOKIE['font_size'];
  } else {
    $font_size = FONTSIZE;
  }
  $bigfont = $font_size * 1.5;
  
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

  $status = $_GET['admin'];
  if ($status == 1) {
    $user_name = trim($_COOKIE['user_name']);
    $password = trim($_COOKIE['password']);
    $con = mysqli_connect(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
    if (!$con) {
      die('Could not connect: ' . mysqli_error($con));
    }
    mysqli_select_db($con,DATABASE_TABLE);
    $stmt = $con->stmt_init();
    $stmt->prepare("SET NAMES 'utf8'");
    $stmt->execute();
    $stmt = $con->stmt_init();
    $stmt->prepare("select * from " . DATABASE_TABLE . " where (user_name = ?) and (binary password_hash = ?)");
    $stmt->bind_param('ss', $user_name, crypt($password,CRYPT_SALT));
    $stmt->execute();
    $result = $stmt->get_result();
    $row = mysqli_fetch_array($result);
    if ($row['admin_status'] == 1) {
      $admin = true;
    } else {
      $admin = false;
    }
    mysqli_close($con);
  }

  echo <<<EODH
<!DOCTYPE html><html><head><meta charset='utf-8' /><title>All Users Search Results</title>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script>
    <!--
    $(document).ready(function(){
      $("img").mousedown(function(){
        $(this).animate({opacity: '0.5'},100);
      });
    });
    //-->
    </script> 
  <style>.user{vertical-align:middle}</style>
  </head><body style='color:black;background-color:#C0C0F0;padding:8px;
  font-family:{$font};font-size:{$font_size}px'>
EODH;
    
  $message = $_GET['message'];
  if (strlen($message) > 0) {
    echo "<div class='container'><p style='font-size:{$bigfont}px;color:red'>{$message}</p></div>";
    $message = "";
  }
  
  if (($_GET['criteria'] == NULL) || ($_GET['criteria'] == "")) {
    $mysqli4 = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
    $mysqli4->set_charset("utf8");
    $stmt4 = $mysqli4->prepare("SELECT name, user_name, id as uid FROM users ORDER BY name LIMIT " . $shown_limit);
    $stmt4->execute();
    $result4 = $stmt4->get_result();

    echo "<h2>All Users Search Results (Limit {$shown_limit}):</h2><ul>";
    while ($myrow4 = $result4->fetch_assoc()) {
      $vname = $myrow4['name'];
      $vuname = $myrow4['user_name'];
      $uid = $myrow4['uid'];
      echo <<<EODL
<li><img src="follow.png" class='user' onclick="window.open(
      'follow.php?followed_one={$vuname}&followed_name={$vname}');" />&nbsp;&nbsp;
      <img src="unfollow.png" class='user' onclick="window.open(
      'unfollow.php?followed_one={$vuname}&followed_name={$vname}');" />&nbsp;&nbsp;
      <a style='a:link{color:#000000};a:vlink{color:#990099};a:alink{color:#999900};a:hlink{color:#000099};' 
      href='home{$ret}.php?view_user_name={$vuname}' target='_blank'>{$vname} (Username: {$vuname})</a>
EODL;
// X button for administrator to delete Tweat
      if ($admin) {
        echo <<<EODD
&nbsp;&nbsp;<img src='xdel.png' class='user' onclick='if (confirm("Are you sure you want to delete this user?:  " + 
                  "{$vname} (Username: {$vuname}; User ID: {$uid})")) {window.open("home{$ret}.php?delete_listed_user={$uid}")}' />
EODD;
      }
      echo "</li>";
    }
    $stmt4->close();
    $mysqli4->close();
    
    echo "</ul></body></html>";
  }
  exit();
