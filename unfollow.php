<?php
  require_once 'app_config.php';

  $user_name = trim($_COOKIE['user_name']);
  $password = trim($_COOKIE['password']);
  $followed_one = $_GET['followed_one'];
  $followed_name = $_GET['followed_name'];

  if ((strlen($user_name) > 0) && (strlen($password) > 0 ) && (strlen($followed_one) > 0)) {
    $mysqli2 = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
    $stmt = $mysqli2->prepare("SELECT * FROM " . DATABASE_TABLE . " WHERE (user_name = ?) and 
      (binary password_hash = ?)");
    $stmt->bind_param('ss', $user_name, crypt($password,"pling515"));
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = mysqli_fetch_array($result)) {
      $stmt = $mysqli2->prepare("SELECT * FROM followed_ones WHERE user_name = ? AND followed_one = ?");
      $stmt->bind_param('ss', $user_name, $followed_one);
      $stmt->execute();
      $result = $stmt->get_result();
      if (($myrow2 = $result->fetch_assoc()) && ($user_name != $followed_one)) {
        $stmt = $mysqli2->prepare("DELETE FROM followed_ones WHERE user_name = ? AND followed_one = ? AND user_name != followed_one");
        $stmt->bind_param('ss', $user_name, $followed_one);
        $stmt->execute();
      } else {
        $stmt->close();
        $mysqli2->close();
        header("Location: home.php?message=" . strtr($followed_name . " (" . $followed_one . ") isn't on your list of followed users. ", " ", "+"));
        exit();
      }
    } else {
      $stmt->close();
      $mysqli2->close();    
      header("Location: home.php?message=" . strtr("There was an error and the user was not removed. You may try again later. ", " ", "+"));
      exit();
    }
  } else {
    $stmt->close();
    $mysqli2->close();
    header("Location: home.php?message=" . strtr("There was an error and the user was not removed. You may try again later. ", " ", "+"));
    exit();
  }
  $stmt->close();
  $mysqli2->close();
  header("Location: home.php?message=" . strtr($followed_name . " (" . $followed_one . ") is now removed from your list of followed users. ", " ", "+"));
  exit();