<?php

// Display User Search Results

  require_once 'app_config.php';
  
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

  echo <<<EODH
<!DOCTYPE html><html><head><meta charset='utf-8' /><title>User Search Results</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
  </head><body style='color:black;background-color:#C0C0F0;padding:8px;
  font-family:{$font};font-size:{$font_size}px'>
EODH;

  require_once '_header.php';
    
  $message = $_GET['message'];
  if (strlen($message) > 0) {
    echo "<div class='container'><p style='font-size:{$bigfont}px;color:red'>{$message}</p></div>";
    $message = "";
  }
  
  if (($_GET['criteria'] == NULL) || ($_GET['criteria'] == "")) {
    $mysqli4 = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
    $mysqli4->set_charset("utf8");
    $stmt4 = $mysqli4->prepare("SELECT name, user_name FROM users ORDER BY name LIMIT " . $shown_limit);
    $stmt4->execute();
    $result4 = $stmt4->get_result();

    echo "<h2>User Search Results:</h2><ul>";
    while ($myrow4 = $result4->fetch_assoc()) {
      $vname = $myrow4['name'];
      $vuname = $myrow4['user_name'];
      echo <<<EODL
<li><button type="button" class="btn btn-success" onclick="location.replace(
      'follow.php?followed_one={$vuname}&followed_name={$vname}');">Follow</button>&nbsp;&nbsp;
      <button type="button" class="btn btn-danger" onclick="location.replace(
      'unfollow.php?followed_one={$vuname}&followed_name={$vname}');">Unfollow</button>&nbsp;&nbsp;
      <a style='color:black' href='home.php?view_user_name={$vuname}'>{$vname} (Username: {$vuname})</a>
      </li>
EODL;
    }
    $stmt4->close();
    $mysqli4->close();
    
    echo "</ul></body></html>";
  }
  exit();