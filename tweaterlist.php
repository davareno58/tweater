<?php
// Get and format updated Tweats in Converse Mode
  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json; charset=UTF-8");
  
  require_once 'app_config.php';

  if (isset($_COOKIE['font_size'])) {
    $font_size = $_COOKIE['font_size'];
  } else {
    $font_size = FONTSIZE;
  }

  if ($font_size == FONTSIZE) {
    $input_width = 113;
    $tweat_width = 91;
  } else {
    $input_width = floor(2000 / $font_size);
    $tweat_width = floor(1600 / $font_size);
  }
  $bigfont = $font_size * 1.5;

  if (isset($_COOKIE['shown_limit'])) {
    $shown_limit = 49; //$_COOKIE['shown_limit'];
  } else {
    $shown_limit = 49;
  }
  $user_name = trim($_COOKIE['user_name']);
  $password = trim($_COOKIE['password']);

  $conn = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
  
  $conn->set_charset("utf8");
  if ($stmt = $conn->prepare("SELECT t.id, t.user_name, t.tweat, u.name FROM tweats AS t INNER JOIN " . 
    "users AS u ON t.user_name = u.user_name WHERE t.user_name IN (SELECT followed_one " . 
    "FROM followed_ones AS f WHERE f.user_name = ?) ORDER BY t.id DESC LIMIT ?")) {
    $stmt->bind_param('ss', $user_name, $shown_limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $outp = [];
    while($rs = $result->fetch_array(MYSQLI_ASSOC)) {
      $outp2 = [];
      $outp2[name] = '"' . $rs[name] . '"';
      $outp2[tweat] = '"' . $rs[tweat] . '"';
      $outp2[tid] = '"' . $rs[id] . '"';
      $outp[$rs[id]] = $outp2;
    }
  } else {
    echo "Error: " . $conn->error;
  }
  $conn->close();

  $retn = "<div id='tweatlist'>";
  foreach ($outp as $myro) {
    if ($myro[name]) {
      $myrow_name = substr($myro[name], 1, strlen($myro[name]) - 2);
      $myrow_tweat = substr($myro[tweat], 1, strlen($myro[tweat]) - 2);
      $tid = substr($myro[tid], 1, strlen($myro[tid]) - 2);
    } else {
      $myrow_name = "";
      $myrow_tweat = "";      
    }
    $retn .= "<div class='row' style='color:black'><div class='col-md-3 text-right' " . 
      "style='word-wrap: break-word;'><b>" . wordwrap($myrow_name, 40, '<br />', true) . 
      "</b>:</div><div class='col-md-9 text-left' style='margin-left: -17px;'><p>" . 
      wordwrap($myrow_tweat, $tweat_width, '<br />', true);
    if ($myrow_name == $name) {
      $no_quote_tweat = strtr(substr($myrow_tweat,0,80), "\"'\t\r\n\f", "      ");
// X button to delete Tweat
      $retn .= "&nbsp;&nbsp;<img src='xdel.png' " . 
        "onclick='if (confirm(\"Are you sure you want to delete this Tweat?:  " . $no_quote_tweat . 
        "...\")) {location.replace(\"home.php?delete_tweat=\" + {$tid});}' />";
    }
    $retn .= "</p></div></div>";
  }
  echo $retn;
  