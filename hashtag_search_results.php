<?php

// Display Hashtag Search Results

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
  $hashtag = $_GET['hashtag_search'];
  $hashtag_win = $hashtag;
  $hashtag = str_replace("*", "%", $hashtag);
  $hashtag = str_replace("?", "_", $hashtag);
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
    $stmt->bind_param('ss', $user_name, crypt($password,"pling515"));
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
<!DOCTYPE html><html><head><meta charset='utf-8' /><title>Hashtag Search Results for "{$hashtag_win}"</title>
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
  
  if (($hashtag == NULL) || ($hashtag == "")) {
    $message = "No+hashtag+was+given+to+search+for.";
    header("Location: home" . $ret . ".php?message=" . $message);
    exit();
  } else {

    $mysqli4 = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
    $mysqli4->set_charset("utf8");
    if ((strpos($hashtag, "%") !== false) || (strpos($hashtag, "_") !== false)) {
      $compare = "LIKE";
    } else {
      $compare = "=";
    }
    $stmt4 = $mysqli4->prepare("SELECT t.id, t.user_name, t.tweat, t.hashtag, u.name FROM tweats AS t " . 
      "INNER JOIN users AS u ON t.user_name = u.user_name WHERE (t.hashtag " . $compare . " ?) AND " . 
        "(SUBSTRING(t.hashtag FROM 1 FOR 3) != 'DEL') ORDER BY t.id DESC LIMIT ?");
    $stmt4->bind_param('ss', $hashtag, $shown_limit);
    $stmt4->execute();
    $result4 = $stmt4->get_result();
    
    echo "<h2>Hashtag Search Results for \"{$hashtag_win}\"<br />(Note: Wildcards ? and * may be used):</h2><ul>";
    if (!$myrow4 = $result4->fetch_assoc()) {
      echo "<p>No Tweats found with the hashtag \"{$hashtag_win}\". <br />
        Are you sure you spelled it right? (Don't worry about capitalizing.) <br />
        You might try some other form of the word, such as a singular or plural.</p>";
    } else {
      do {
        $vname = $myrow4['name'];
        $vuname = $myrow4['user_name'];
        $tweat = $myrow4['tweat'];
        $tid = $myrow4['id'];
        echo <<<EODL
<li><img src="follow.png" class='user' onclick="window.open(
      'follow.php?followed_one={$vuname}&followed_name={$vname}');" />&nbsp;&nbsp;
      <a style='a:link{color:#000000};a:vlink{color:#990099};a:alink{color:#999900};a:hlink{color:#000099};' 
      href='home{$ret}.php?view_user_name={$vuname}' target='_blank'>{$vname}:</a>&nbsp;&nbsp;{$tweat}
EODL;

// X button for administrator to delete Tweat
        if ($admin) {
          $no_quote_tweat = strtr(substr($tweat,0,80), "\"'\t\r\n\f", "      ");
          echo "&nbsp;&nbsp;<img src='xdel.png' style='position:relative;top:7px' onclick='if (confirm(\"Are you sure you want to delete this Tweat?:  " . 
            $no_quote_tweat . "...\")) {window.open(\"home{$ret}.php?delete_tweat=\" + {$tid});}' />";
        }

        echo "</li>";
      } while ($myrow4 = $result4->fetch_assoc());
    }
    $stmt4->close();
    $mysqli4->close();
    
    echo "</ul></body></html>";
  }
  exit();