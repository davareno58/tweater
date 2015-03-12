<?php
  require_once 'app_config.php';

  $user_name = trim($_COOKIE['user_name']);
  $password = trim($_COOKIE['password']);
  $followed_one = $_GET['followed_one'];
  $followed_name = $_GET['followed_name'];
  $error_later = "There was an error and the user was not added. You may try again later. ";
  $ret = $_GET['return'];
  
  if ((strlen($user_name) > 0) && (strlen($password) > 0 ) && (strlen($followed_one) > 0)) {
    $mysqli2 = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
    $stmt = $mysqli2->prepare("SELECT * FROM " . DATABASE_TABLE . " WHERE (user_name = ?) and 
      (binary password_hash = ?)");
    $stmt->bind_param('ss', $user_name, crypt($password,"pling515"));
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = mysqli_fetch_array($result)) {
      if ($stmt = $mysqli2->prepare("SELECT * FROM followed_ones WHERE user_name = ? AND followed_one = ?")) {
        $stmt->bind_param('ss', $user_name, $followed_one);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($myrow2 = $result->fetch_assoc()) {
          $stmt->close();
          $mysqli2->close();
          if ($user_name == $followed_one) {
            echo "<script>alert('Hey, you can\'t follow YOURSELF ! (I tried it once, and kept going in circles...)');window.close();</script>";
          } else {
            echo "<script>alert('{$followed_name} ({$followed_one}) is already on your list of followed users.');window.close();</script>";
          }
          exit();
        } else {
          $stmt = $mysqli2->prepare("INSERT INTO followed_ones (id, user_name, followed_one) VALUES (NULL, ?, ?)");
          $stmt->bind_param('ss', $user_name, $followed_one);
          $stmt->execute();
        }
      } else {
        $stmt->close();
        $mysqli2->close();
        echo "<script>alert('{$error_later}');window.close();</script>";
        exit();
      }
    } else {
      $stmt->close();
      $mysqli2->close();
      echo "<script>alert('{$error_later}');window.close();</script>";
      exit();
    }
  } else {
    $stmt->close();
    $mysqli2->close();
    echo "<script>alert('{$error_later}');window.close();</script>";
    exit();
  }
  $stmt->close();
  $mysqli2->close();
  echo "<script>disableFollowedOnes = 0;alert('{$followed_name} ({$followed_one}) is now added to your list of followed users.');window.close();</script>";
  exit();
  