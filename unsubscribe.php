<?php
  require_once 'app_config.php';
echo "u:" . $_COOKIE['user_name'] . $_COOKIE['password'] . "end";
sleep(1);
  $message = "";
  if (isset($_COOKIE['user_name']) && isset($_COOKIE['password'])) {
    $user_name = trim($_COOKIE['user_name']);
    $password = trim($_COOKIE['password']);
    mysqli_select_db($con,DATABASE_TABLE);
    $stmt = $con->stmt_init();
    $stmt->prepare("DELETE * FROM " . DATABASE_TABLE . 
      " WHERE ((user_name = ?) OR (email = ?)) AND (binary password_hash = ?)");
    $stmt->bind_param('sss', $user_name, $user_name, crypt($password,"pling515"));
    $stmt->execute();
    mysqli_close($con);
    setcookie('user_name', "", time() - 7200, "/");
    setcookie('password', "", time() - 7200, "/");
    echo <<<EOD
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE>TWEATER UNSUBSCRIBE</TITLE>
<META NAME="description" CONTENT="Tweater Social Site"> 
<META NAME="keywords" CONTENT="tweater, social site, tweats">
</HEAD>
<BODY LINK="#C00000" VLINK="#800080" alink="#FFFF00" bgcolor="00D0C0" onLoad="openit()">
<h1 style='text-align:center'>Tweater: You are now unsubscribed to Tweater.<br />Sorry to see you go! (Actually I'm a computer and have no human feelings.)</h1>
<h2 style='text-align:center'><a href="home.php">Click here to sign in another user or register a new user.</a></h2>
</BODY>
</HTML>
EOD;
    exit();
  }
?>