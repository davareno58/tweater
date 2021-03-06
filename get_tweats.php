<?php
// Get Tweats from followed users and signed-in user to display in iframe
  require_once 'app_config.php';
  $user_name = $_COOKIE['user_name'];
  $name = $_GET['name'];
  $name = strtr($name, "+", " ");
  $ret = $_GET['return'];
  $shown_limit = 10;
  $timeout_message = "";
  if (isset($_COOKIE['chat_timeout'])) {
    $chat_timeout = $_COOKIE['chat_timeout'];
    if ($chat_timeout != 'end') {
      if (time() > $chat_timeout) {
// Automatically turn off chat mode
        setcookie('chat', false, time() + 7200, "/");
        setcookie('chat_timeout', 'end', time() + 7200, "/");
        $timeout_message = "<!DOCTYPE html><html><head><script>window.open(\"home{$ret}.php?message=Chat+Mode+has+timed+out+and+has+been+turned+off.+The+timeout+is+five+minutes.\", \"_parent\");</script></head><body></body></html>";
        //header("Location: home{$ret}.php?message=Chat+Mode+has+timed+out+and+has+been+turned+off.");
        //exit();
      } else if (time() >= $chat_timeout - 60) {
        $timeout_message = "<p style='color:red'>Timeout Warning: If you don't post a Tweat within one minute, Chat Mode will be turned off.</p>";    
      }
    }
  }
  if (isset($_COOKIE['font_size'])) {
    $font_size = $_COOKIE['font_size'];
  } else {
    $font_size = FONTSIZE;
    $bigfont = $font_size * 1.5;
  }
  if (isset($_COOKIE['font_family'])) {
    $font = $_COOKIE['font_family'] . ", Helvetica";
  } else {
    $font = "Helvetica";
  }
  if (isset($_COOKIE['tweat_width'])) {
    $tweat_width = $_COOKIE['tweat_width'];
  } else {
    $tweat_width = floor(1600 / $font_size);
  }
  $mysqli2 = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
  $mysqli2->set_charset("utf8");
  if ($stmt = $mysqli2->prepare("SELECT t.id, t.user_name, t.tweat, u.name FROM tweats AS t INNER JOIN " . 
    "users AS u ON t.user_name = u.user_name WHERE t.user_name IN " . 
    "(SELECT followed_one FROM followed_ones AS f WHERE f.user_name = ?) ORDER BY t.id DESC LIMIT ?")) {
    $stmt->bind_param('ss', $user_name, $shown_limit);
    $stmt->execute();
    $result = $stmt->get_result();

// Display Tweats in iframe with 10 second refresh for chat mode
    echo <<<EOD
<!DOCTYPE html><html><head><meta charset='utf-8' /><meta http-equiv="refresh" content="10">
<title>Tweats:</title></head>
<body background='pictures/backviolet.png' style='color:black;background-color:#c0c0f0;padding:8px;
font-family:{$font};font-size:{$font_size}px'>{$timeout_message}
<table>
EOD;
    $zebra = "#E0E0FF"; // Alternating Tweat row colors
    while ($myrow = $result->fetch_assoc()) {
      if ($myrow['name']) {
        $myrow_name = $myrow['name'];
        $myrow_tweat = $myrow['tweat'];
        $tid = $myrow['id'];
        $myrow_hashtag = $myrow['hashtag'];
      } else {
        $myrow_name = "";
        $myrow_tweat = "";
        $myrow_hashtag ="";
      }
      if (substr($myrow_hashtag, 0, 3) == "DEL") {
      $myrow_tweat .= " chat fd:" . time() . " " . substr($myrow_tweat, 3);
      // Delete old chat tweat
        if (time() > substr($myrow_hashtag, 3)) {
          $stmt->close();
          $stmt = $mysqli2->prepare("DELETE FROM tweats WHERE id = ?");
          $stmt->bind_param('i', $tid);
          $stmt->execute();
          continue;
        }
      }
      echo "<tr style='color:black;background-color:{$zebra}'><td style='vertical-align:top;text-align:right'><b>" . 
        wordwrap($myrow_name, 40, '<br />', true) . 
        "</b>:&nbsp;&nbsp;</td><td>" . 
        wordwrap($myrow_tweat, $tweat_width, '<br />', true);
      if ($myrow_name == $name) {
          $no_quote_tweat = strtr(substr($myrow_tweat,0,80), "\"'\t\r\n\f", "      ");
// X button to delete Tweat
          echo "&nbsp;&nbsp;&nbsp;<span style='color:black;background-color:red' onclick='if (confirm(\"Are you sure you want to delete this Tweat?:  " . 
            $no_quote_tweat . "...\")) {window.open(\"home{$ret}.php?delete_tweat=\" + {$tid}, \"_parent\");}'>&nbsp;X&nbsp;</span>";
      }
      echo "</td></tr>";
      if ($zebra == "#C0C0F0") {
        $zebra = "#E0E0FF";
      } else {
        $zebra = "#C0C0F0";
      }
    }
    $t = time();
    echo "</table>{$timeout_message}</body></html>";
  }
  $stmt->close();
  $mysqli2->close();
  exit();
  