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
<!DOCTYPE html><html><head><meta charset='utf-8' /><title>User Search Results</title>
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
  echo "<h2>Interests and Information Search Results (Limit {$shown_limit}):</h2><ul>";
  $message = $_GET['message'];
  if (strlen($message) > 0) {
    echo "<div class='container'><p style='font-size:{$bigfont}px;color:red'>{$message}</p></div>";
    $message = "";
  }

  if (isset($_POST['search_any'])) {
    $search_any = $_POST['search_any'];
    if (($search_any == "") || ($search_any == NULL)) {
      header("Location: all_user_search_results.php?admin=" . $admin_status . "&return=" . $ret);
      exit();
    }
    $search_any = str_replace("-", " ", substr(strtolower($search_any), 0, 250));
    $search_any = trim(strtr($search_any, ',;+&/', '     '));
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
    $all_items = "";
    foreach ($search_any_array as $search_item) {
      if (strlen($search_item) > 0) {
        $all_items .= " / " . $search_item;
        $stmt->prepare("SELECT i.id, i.user_name, i.interest, u.name, u.id as uid FROM interests AS i " . 
          "INNER JOIN users AS u ON i.user_name = u.user_name WHERE i.interest = ? " . 
          "ORDER BY i.interest LIMIT ?");
        $stmt->bind_param('ss', $search_item, $shown_limit);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($myrow = $result->fetch_assoc()) {
          echo "<h3>Search word \"{$search_item}\":</h3>";
        
          do {
            $vname = $myrow['name'];
            $vuname = $myrow['user_name'];
            $uid = $myrow['uid'];
            if ($user_name != $vuname) {
              $find_count += 1;
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
        } else {
          echo "<h3>Search word \"{$search_item}\": Not found.</h3>";
        }
      }
    }
    
    if ((strpos($all_items, "?") != false) || (strpos($all_items, "*") != false)) {
        echo "<br />Note:&nbsp;&nbsp;The wildcards ? and * can only be used in a Boolean Search.<br />" . 
          "In a normal User Search like this one, the ? and * have their literal values,<br />" . 
          "since some usernames may include them.<br /><br />";
    }
    if ($find_count == 0) {
      echo "None of the given words were found:<br />" . $search_any . "<br /><br />";
      echo "You may want to check your spelling, or use other forms of the words,<br />" . 
      "such as singulars or plurals. (Don't worry about capitalizing.)";
    }
    mysqli_close($con);
  } else {
    echo "No search term was given.<br /><br />";
  }
  echo "</ul><br /><br /></body></html>";  
  exit();