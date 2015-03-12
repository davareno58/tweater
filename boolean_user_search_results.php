<?php

// Search for other users by any information and interests

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
  if (isset($_COOKIE['user_name'])) {
    $user_name = trim($_COOKIE['user_name']);
  } else {
    $user_name = "";
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
    $stmt->bind_param('ss', $user_name, crypt($password,"pling515"));
    $stmt->execute();
    $result = $stmt->get_result();
    $row = mysqli_fetch_array($result);
    $admin_status = $row['admin_status'];
    if ($admin_status == 1) {
      $admin = true;
    } else {
      $admin = false;
    }
    mysqli_close($con);
  }
  
  echo <<<EODH
<!DOCTYPE html><html><head><meta charset='utf-8' /><title>Boolean User Search Results</title>
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

  if (isset($_POST['search_one'])) {
    $search_one = $_POST['search_one'];
    if (!isset($_POST['search_two'])) {
      $search_two = "";
    } else {
      $search_two = $_POST['search_two'];
    }
    if ($search_two == NULL) {
      $search_two = "";
    }
    if (($search_one == "") || ($search_one == NULL)) {
      echo "Error:  At least the first search term must be given for a Boolean Search.</body></html>";
      exit();
    } else {
      $search_one = str_replace("-", " ", substr(strtolower($search_one), 0, 250));
      $search_one = strtr($search_one, '_%?*', '  _%');
      $search_one = trim(strtr($search_one, '"(),-/:;<=>[]!^\`{|}~' . 
        '¡¦©«¬­®¯´¶¸»¿', '                                          '));
      $search_one = str_replace("   ", " ", $search_one);
      $search_one = str_replace("  ", " ", $search_one);
      $search_one_wild = strtr($search_one, '_%', '?*');
      $search_one = "% " . $search_one . " %";

      if ($search_two != "") {
        $search_two = str_replace("-", " ", substr(strtolower($search_two), 0, 250));
        $search_two = strtr($search_two, '_%?*', '  _%');
        $search_two = trim(strtr($search_two, '"(),-/:;<=>[]!^\`{|}~' . 
          '¡¦©«¬­®¯´¶¸»¿', '                                          '));
        $search_two = str_replace("   ", " ", $search_two);
        $search_two = str_replace("  ", " ", $search_two);
        $search_two_wild = strtr($search_two, '_%', '?*');
        $search_two = "% " . $search_two . " %";
      }
      $search_type = $_POST['search_type'];
      if (($search_type == "") || ($search_type == NULL)) {
        $search_type = "AND";
      }
      
      $con = mysqli_connect(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
      if (!$con) {
        die('Could not connect: ' . mysqli_error($con));
      }
      mysqli_select_db($con,DATABASE_TABLE);
      $stmt = $con->stmt_init();
      $stmt->prepare("SET NAMES 'utf8'");
      $stmt->execute();
      $stmt = $con->stmt_init();
      if ($search_two == "") {
        $stmt->prepare("SELECT name, user_name, id as uid FROM users WHERE (interests_words LIKE ?) " . 
          "AND (user_name != ?) ORDER BY ? LIMIT ?");
        $stmt->bind_param('ssss', $search_one, $user_name, $search_one, $shown_limit);
      } else {
        if ($search_type == "NOT") {
          $stmt->prepare("SELECT name, user_name, id as uid FROM users WHERE ((interests_words LIKE ?) " . 
            "AND (interests_words NOT LIKE ?)) AND (user_name != ?) ORDER BY ? LIMIT ?");
        } else {
          $stmt->prepare("SELECT name, user_name, id as uid FROM users WHERE ((interests_words LIKE ?) " . 
          $search_type . " (interests_words LIKE ?)) AND (user_name != ?) ORDER BY ? LIMIT ?");
        }
        $stmt->bind_param('sssss', $search_one, $search_two, $user_name, $search_one, $shown_limit);
      }
      $stmt->execute();
      $result = $stmt->get_result();

      if (! $result) {
        echo "Error: " . mysqli_error($con) . "</body></html>";
        mysqli_close($con);
        exit();
      }
      $num_rows = mysqli_num_rows($result);
      $find_count = 0;
      if ($search_one != $search_two) {
        echo "<h2>{$num_rows} Boolean Interests and Information Search Results (Limit {$shown_limit})<br />" . 
          "for \"{$search_one_wild}\" {$search_type} \"{$search_two_wild}\" (Note: Wildcards ? and * may be used):</h2><ul>";
      } else {
        echo "<h2>{$num_rows} Boolean Interests and Information Search Results (Limit {$shown_limit})<br />" . 
          "for \"{$search_one_wild}\" (Note: Wildcards ? and * may be used):</h2><ul>";    
      }
      $message = $_GET['message'];
      if (strlen($message) > 0) {
        echo "<div class='container'><p style='font-size:{$bigfont}px;color:red'>{$message}</p></div>";
        $message = "";
      }
    
      if ($myrow = $result->fetch_assoc()) {
        do {
          $vname = $myrow['name'];
          $vuname = $myrow['user_name'];
          $uid = $myrow['uid'];
          if ($user_name != $vuname) {
            $find_count = $find_count + 1;
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
        } while ($myrow = $result->fetch_assoc());
      }
      if ($find_count == 0) {
        if ($search_one != $search_two) {
          echo "<h2>No user with both of the given terms was found:&nbsp;&nbsp;" . $search_one_wild . " and " . $search_two_wild . ".</h2>";
        } else {
          echo "<h2>No user with the given term was found:&nbsp;&nbsp;" . $search_one_wild . ".</h2>";
        }
        echo "<h2>You may want to check your spelling, or use other forms<br />of the words, such as singulars or plurals. (Don't worry about capitalizing.)</h2>";
      }
      mysqli_close($con);
    }
  } else {
    echo "No search term was given.<br /><br />";
  }
  echo "</ul><br /><br /></body></html>";  
  exit();
  