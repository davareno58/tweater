<?php
  require_once 'app_config.php';

  if (isset($_COOKIE['font_size'])) {
    $font_size = $_COOKIE['font_size'];
  } else {
    $font_size = FONTSIZE;
  }
  $bigfont = $font_size * 1.5;
  
  if (isset($_COOKIE['font_family'])) {
    $font = $_COOKIE['font_family'] . ", Helvetica";
  } else {
    $font = "Helvetica";
  }
  
  $ret = $_GET['return'];
  echo <<<EOF
<!DOCTYPE html><html>
  <head><title>Password Change</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <style>
      .center {
        margin-left: auto;
        margin-right: auto;
        width: 20%;
        background-color: #ee6600;
      }
    </style>
    </head>
<body background='pictures/backviolet.png' 
    style='color:black;background-color:#c0c0f0;padding:8px;font-family:{$font},Courier New;font-size:{$bigfont}px'>
<div class="center"><p>Password Change:</p><span>
<form action="new_password.php?return={$ret}" method="POST" autocomplete="off">
<div>
<fieldset class="fieldset-auto-width" style="float:left">
<input type="text" style="display:none">
<input type="password" style="display:none">
<div class="input-group"><input type="password" class="form-control" placeholder="Old Password" name="old_password" autocomplete="off" size="32"></div>
<div class="input-group"><input type="password" class="form-control" placeholder="New Password" name="new_password" autocomplete="off" size="32"></div>
<div class="input-group"><input type="password" class="form-control" placeholder="Confirm New Password" name="password_confirm" autocomplete="off" size=32></div>
<button type="submit" class="btn btn-success">Change Password</button>
</fieldset>
</div>
</span>
</form></div>
</body>
</html>
EOF;
  exit();
  