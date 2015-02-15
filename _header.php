<?php

$self_name = $_SERVER['PHP_SELF'];
$view_user_name = $row['user_name'];

echo <<<EOD
<nav class="navbar navbar-default">
    <ul class="nav nav-pills" style="background-color:#C0C0F0">
      <li role="presentation" class="btn btn-success"><a href="home.php" style="color:lightgray">Home</a></li>
      <li role="presentation" class="btn btn-primary"><a href="upload_picture.html" style="color:lightgray">
      Upload Picture</a></li>
      <li role="presentation" class="btn btn-success"><a href="{$self_name}" onclick="staySignedInWithAlert();" style="color:lightgray">
      Remain Signed In</a></li>
      <li role="presentation" class="btn btn-info"><a href="{$self_name}" onclick="about();">About</a></li>
      <li role="presentation" class="btn btn-success"><a href="{$self_name}" onclick="contact();" style="color:lightgray">Contact</a></li>
      <li role="presentation" class="btn btn-warning"><a href="{$self_name}" onclick="unsubscribe();">
      Unsubscribe</a></li>
      <li role="presentation" class="btn btn-info"><a href="help.php" target="_blank">Help</a></li>      
      <li role="presentation" class="btn btn-primary"><a onclick="settings();" style="color:lightgray">
      Settings</a></li>
      <li role="presentation" class="btn btn-danger"><a href="signout.html" onclick="signOut();" style="color:lightgray">Sign Out</a></li>     
      <li role="presentation" class="btn btn-info">
      <a href="home.php?view_user_name={$user_name}">Public Page</a></li>
    </ul>
</nav>
EOD;
?>