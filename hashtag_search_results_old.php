<?php

// Display Hashtag Search Results

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
  $hashtag = $_GET['hashtag_search'];
  
  echo <<<EODH
<!DOCTYPE html><html><head><meta charset='utf-8' /><title>Hashtag Search Results for "{$hashtag}"</title>
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
  
  if (($_GET['hashtag_search'] == NULL) || ($_GET['hashtag_search'] == "")) {
    $message = "No+hashtag+was+given+to+search+for.";
    header("Location: home.php?message=" . $message);
    exit();
  } else {
    $mysqli4 = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
    $mysqli4->set_charset("utf8");
    $stmt4 = $mysqli4->prepare("SELECT t.id, t.user_name, t.tweat, t.hashtag, u.name FROM tweats AS t " . 
      "INNER JOIN users AS u ON t.user_name = u.user_name WHERE t.hashtag = ? ORDER BY t.id DESC LIMIT ?");
    $stmt4->bind_param('ss', $hashtag, $shown_limit);
    $stmt4->execute();
    $result4 = $stmt4->get_result();

    echo "<h2>Hashtag Search Results for \"{$hashtag}\":</h2><ul>";
    if (!$myrow4 = $result4->fetch_assoc()) {
      echo "<p>No Tweats found with the hashtag \"{$hashtag}\". <br />
        Are you sure you spelled it right? (Don't worry about capitalizing.) <br />
        You might try some other form of the word, such as a singular or plural.</p>";
    } else {
      do {
        $vname = $myrow4['name'];
        $vuname = $myrow4['user_name'];
        $tweat = $myrow4['tweat'];
        echo <<<EODL
<li><button type="button" class="btn btn-success" onclick="location.replace(
      'follow.php?followed_one={$vuname}&followed_name={$vname}');">Follow</button>&nbsp;&nbsp;
      <a href='home.php?view_user_name={$vuname}' target='_blank'>{$vname}:</a>&nbsp;&nbsp;{$tweat}</li>
EODL;
      } while ($myrow4 = $result4->fetch_assoc());
    }
    $stmt4->close();
    $mysqli4->close();
    
    echo "</ul></body></html>";
  }
  exit();