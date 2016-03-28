<?php
$self_name = $_SERVER['PHP_SELF'];
$view_user_name = $row['user_name'];

echo <<<EOD
<nav class="navbar navbar-default" style="width:1207px">
  <ul class="nav nav-pills" style="background-color:#C0C0F0">
    <li role="presentation" class="btn btn-success">
      <a href="{$self_name}" style="color:lightgray">Home</a>
    </li>
    <li role="presentation">
      <button type="button" class="btn btn-info" style="height:54px;width:90px" onclick="about();" 
        style="width:100px">About</button>
    </li>
    <li role="presentation" class="btn btn-success">
      <a href="upload_picture.html?return={$ret}" style="color:lightgray" target="_blank">Upload Picture</a>
    </li>
    <li role="presentation" class="btn btn-primary">
      <a href="{$self_name}" onclick="staySignedInWithAlert();" style="color:lightgray">Remain Signed In</a>
    </li>
    <li role="presentation" class="btn btn-success">
      <a href="{$self_name}" onclick="contact();" style="color:lightgray">Contact</a>
    </li>
    <li role="presentation" class="btn btn-warning">
      <a href="{$self_name}" onclick="unsubscribe();">Unsubscribe</a>
    </li>
    <li role="presentation" class="btn btn-info" style="width:96px">
      <a href="help.php" target="_blank">Help</a>
    </li>      
    <li role="presentation" class="btn btn-primary">
      <a onclick="settings();" style="color:lightgray">Settings</a>
    </li>
    <li role="presentation" class="btn btn-danger">
      <a href="signout.html" onclick="signOut();" style="color:lightgray">Sign Out</a>
    </li>     
    <li role="presentation" class="btn btn-info" style="width:137px">
      <a href="{$self_name}?view_user_name={$user_name}" target="_blank">Public Page</a>
    </li>
  </ul>
</nav>
EOD;
