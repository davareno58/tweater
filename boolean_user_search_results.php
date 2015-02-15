<?php

// Search for other users by any information and interests

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
  if (isset($_COOKIE['user_name'])) {
    $user_name = trim($_COOKIE['user_name']);
  } else {
    $user_name = "";
  }
  
  echo <<<EODH
<!DOCTYPE html><html><head><meta charset='utf-8' /><title>Boolean User Search Results</title>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
<style>.user{vertical-align:middle}</style>
  </head><body style='color:black;background-color:#C0C0F0;padding:8px;
  font-family:{$font};font-size:{$font_size}px'>
EODH;
  
  if ((isset($_REQUEST['search_one'])) && (isset($_REQUEST['search_two']))){
    $search_one = $_REQUEST['search_one'];
    $search_two = $_REQUEST['search_two'];

    if (($search_one == "") || ($search_one == NULL) || ($search_two == "") || ($search_two == NULL)) {
      echo "Error:  Both search terms must be given for a Boolean Search.</body></html>";
      exit();
    } else {
    $search_one = str_replace("-", " ", substr(strtolower($search_one), 0, 250));
    $search_one = trim(strtr($search_one, '!"#%&()*+,-./:;<=>?@[\]^_`{|}~' . 
    '¡¦©«¬­®¯´¶¸»¿', '                                                  ' . 
    '                                       '));
    $search_one = str_replace("   ", " ", $search_one);
    $search_one = str_replace("  ", " ", $search_one);
    $search_one_cap = ucwords($search_one);
    $search_one = "% " . $search_one . " %";

    $search_two = str_replace("-", " ", substr(strtolower($search_two), 0, 250));
    $search_two = trim(strtr($search_two, '!"#%&()*+,-./:;<=>?@[\]^_`{|}~' . 
    '¡¦©«¬­®¯´¶¸»¿', '                                                  ' . 
    '                                       '));
    $search_two = str_replace("   ", " ", $search_two);
    $search_two = str_replace("  ", " ", $search_two);
    $search_two_cap = ucwords($search_two);
    $search_two = "% " . $search_two . " %";
  
    $con = mysqli_connect(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
    if (!$con) {
      die('Could not connect: ' . mysqli_error($con));
    }
    mysqli_select_db($con,DATABASE_TABLE);
    $stmt = $con->stmt_init();
    $stmt->prepare("SET NAMES 'utf8'");
    $stmt->execute();
    $stmt = $con->stmt_init();
    $stmt->prepare("SELECT name, user_name FROM users WHERE ((interests_words LIKE ?) AND " . 
      "(interests_words LIKE ?)) AND (user_name != ?) ORDER BY ? LIMIT ?");
    $stmt->bind_param('sssss', $search_one, $search_two, $user_name, $search_one, $shown_limit);
    $stmt->execute();
    $result = $stmt->get_result();

    if (! $result) {
      echo "Error: " . mysqli_error($con) . "</body></html>";
      mysqli_close($con);
      exit();
    }
    $num_rows = mysqli_num_rows($result);
    $find_count = 0;
    echo "<h2>{$num_rows} Boolean Interests and Information Search Results (Limit {$shown_limit})<br />" . 
      "for \"{$search_one_cap}\" AND \"{$search_two_cap}\"<br />(Note: Both terms must be found in a user's Interests and<br />Information to be included in these results):</h2><ul>";
    $message = $_GET['message'];
    if (strlen($message) > 0) {
      echo "<div class='container'><p style='font-size:{$bigfont}px;color:red'>{$message}</p></div>";
      $message = "";
    }
    
    if ($myrow = $result->fetch_assoc()) {
      do {
        $vname = $myrow['name'];
        $vuname = $myrow['user_name'];
        if ($user_name != $vuname) {
          $find_count = $find_count + 1;
          echo <<<EODL
<li><img src="follow.png" class='user' onclick="location.replace(
      'follow.php?followed_one={$vuname}&followed_name={$vname}');" />&nbsp;&nbsp;
      <img src="unfollow.png" class='user' onclick="location.replace(
      'unfollow.php?followed_one={$vuname}&followed_name={$vname}');" />&nbsp;&nbsp;
      <a style='a:link{color:#000000};a:vlink{color:#990099};a:alink{color:#999900};a:hlink{color:#000099};' href='home.php?view_user_name={$vuname}' target='_blank'>{$vname} (Username: {$vuname})</a>
      </li>
EODL;
        }
      } while ($myrow = $result->fetch_assoc());
    } else {
      echo "<h2>Boolean Search \"{$search_one_cap}\" AND \"{$search_two_cap}\" not found.</h2>";
    }
    }
    if ($find_count == 0) {
      echo "No user with both of the given words was found: " . $search_one_cap . " and " . $search_two_cap . ".<br /><br />";
      echo "You may want to check your spelling, or use other forms of the words, " . 
      "such as singulars or plurals.";
    }
    mysqli_close($con);
  } else {
    echo "No search term was given.<br /><br />";
  }
  echo "</ul><br /><br /></body></html>";  
  exit();