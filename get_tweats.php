<?php
// Get Tweats from followed users and signed-in user
  require_once 'app_config.php';
  $user_name = $_COOKIE['user_name'];
  $name = $_GET['name'];
  $name = strtr($name, "+", " ");
  if (isset($_COOKIE['shown_limit'])) {
    $shown_limit = $_COOKIE['shown_limit'];
  } else {
    $shown_limit = 50;
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
// Display Tweats

// Change content 5 to 10
    echo <<<EOD
<!DOCTYPE html><html><head><meta charset='utf-8' /><meta http-equiv="refresh" content="5">
<title>Tweats:</title></head>
<body background='pictures/backviolet.png' style='color:black;background-color:#c0c0f0;padding:8px;
font-family:{$font};font-size:{$font_size}px'><h2>Tweats (Limit {$shown_limit}):</h2>
<table>
EOD;
    while ($myrow = $result->fetch_assoc()) {
      if ($myrow['name']) {
        $myrow_name = $myrow['name'];
        $myrow_tweat = $myrow['tweat'];
        $tid = $myrow['id'];
      } else {
        $myrow_name = "";
        $myrow_tweat = "";      
      }
      echo "<tr><td style='vertical-align:top;text-align:right'><b>" . 
        wordwrap($myrow_name, 40, '<br />', true) . 
        "</b>:&nbsp;&nbsp;</td><td>" . 
        wordwrap($myrow_tweat, $tweat_width, '<br />', true);
      if ($myrow_name == $name) {
          $no_quote_tweat = strtr(substr($myrow_tweat,0,80), "\"'\t\r\n\f", "      ");
// X button to delete Tweat
          echo "&nbsp;&nbsp;&nbsp;<span style='font-size:{$bigfont}px;color:black;background-color:red' onclick='if (confirm(\"Are you sure you want to delete this Tweat?:  " . 
            $no_quote_tweat . "...\")) {location.replace(\"home.php?delete_tweat=\" + {$tid});}'>&nbsp;X&nbsp;</span>";
      }
      echo "</td></tr>";
    }
    echo "</table></body></html>";
  }
  $stmt->close();
  $mysqli2->close();
?>