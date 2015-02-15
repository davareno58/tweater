<?php
  require_once 'app_config.php';

  if (isset($_COOKIE['font_size'])) {
    $font_size = $_COOKIE['font_size'];
  } else {
    $font_size = FONTSIZE;
  }

  if (isset($_COOKIE['font_family'])) {
    $font = $_COOKIE['font_family'] . ", Helvetica";
  } else {
    $font = "Helvetica";
  }
  echo <<<EOF
<!DOCTYPE html><html>
  <head><title>Password Change</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
<body background='pictures/backviolet.png' 
    style='color:black;background-color:#c0c0f0;padding:8px;font-family:{$font},Courier New;font-size:{$font_size}px'>
<form action="new_password.php" method="POST">
<span>
<div>
<fieldset class="fieldset-auto-width" style="float:left">
<legend>Password Change:</legend>
<div class="input-group"><input type="password" class="form-control" placeholder="Old Password" name="old_password" size="32"></div>
<div class="input-group"><input type="password" class="form-control" placeholder="New Password" name="new_password" size="32"></div>
<div class="input-group"><input type="password" class="form-control" placeholder="Confirm New Password" name="password_confirm" size=32></div>
<button type="submit" class="btn btn-success">Change Password</button>
</fieldset>
</div>
</span>
</form>
</body>
</html>
EOF;
  exit();