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
<!DOCTYPE html><html><head><meta charset='utf-8' /><title>User Search Results</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
  </head><body style='color:black;background-color:#C0C0F0;padding:8px;
  font-family:{$font};font-size:{$font_size}px'>
EODH;
  echo "<h2>Interests and Information Search Results (Limit {$shown_limit}):</h2><ul>";
  $message = $_GET['message'];
  if (strlen($message) > 0) {
    echo "<div class='container'><p style='font-size:{$bigfont}px;color:red'>{$message}</p></div>";
    $message = "";
  }

  if (isset($_REQUEST['search_any'])) {
    $search_any = $_REQUEST['search_any'];
    if (($search_any == "") || ($search_any == NULL)) {
      header("Location: all_user_search_results.php");
      exit();
    }
    $search_any = str_replace("-", " ", substr(strtolower($search_any), 0, 250));
    $search_any = trim(strtr($search_any, '!"#%&()*+,-./:;<=>?@[\]^_`{|}~' . 
    '¡¦©«¬­®¯´¶¸»¿', '                                                  ' . 
    '                                       '));
    $search_any = str_replace("   ", " ", $search_any);
    $search_any = str_replace("  ", " ", $search_any);
    $search_any_array = array_unique(explode(" ", $search_any));
    $search_any = "  " . $search_any . " ";

    $con = mysqli_connect(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
    if (!$con) {
      die('Could not connect: ' . mysqli_error($con));
    }
    mysqli_select_db($con,DATABASE_TABLE);
    $stmt = $con->stmt_init();
    if (mb_check_encoding($search_any, 'UTF-8' ) === true ) {
        $stmt->prepare("SET NAMES 'utf8'");
        $stmt->execute();
    }
    
    $find_count = 0;
    foreach ($search_any_array as $search_item) {
      if (strlen($search_item) > 0) {
        $search_item_cap = ucfirst($search_item);
        mysqli_set_charset('$con', 'utf8mb4');
        $stmt->prepare("SELECT name, user_name FROM interests WHERE interest = ?");
        $stmt->bind_param('s', $search_item);
        $stmt->prepare("SELECT i.id, i.user_name, i.interest, u.name FROM interests AS i " . 
          "INNER JOIN users AS u ON i.user_name = u.user_name WHERE i.interest = ? " . 
          "ORDER BY i.interest LIMIT ?");
        $stmt->bind_param('ss', $search_item, $shown_limit);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($myrow = $result->fetch_assoc()) {
          echo "<h3>Search word \"{$search_item_cap}\":</h3>";
        
          do {
            $vname = $myrow['name'];
            $vuname = $myrow['user_name'];
            if ($user_name != $vuname) {
              $find_count += 1;
              echo <<<EODL
<li><button type="button" class="btn btn-success" onclick="location.replace(
      'follow.php?followed_one={$vuname}&followed_name={$vname}');">Follow</button>&nbsp;&nbsp;
      <button type="button" class="btn btn-danger" onclick="location.replace(
      'unfollow.php?followed_one={$vuname}&followed_name={$vname}');">Unfollow</button>&nbsp;&nbsp;
      <a style='color:black' href='home.php?view_user_name={$vuname}' target='_blank'>{$vname} (Username: {$vuname})</a>
      </li>
EODL;
            }
          } while ($myrow = $result->fetch_assoc());
        } else {
          echo "<h3>Search word \"{$search_item_cap}\": Not found.</h3>";
        }
      }
    }
    if ($find_count == 0) {
      echo "None of the given words were found:<br />" . ucwords($search_any) . "<br /><br />";
      echo "You may want to check your spelling, or use other forms of the words,<br />" . 
      "such as singulars or plurals.";
    }
    mysqli_close($con);
  } else {
    echo "No search term was given.<br /><br />";
  }
  echo "</ul><br /><br /></body></html>";  
  exit();