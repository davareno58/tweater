<?php
  require_once 'app_config.php';

  if (isset($_COOKIE['font_size'])) {
    $font_size = $_COOKIE['font_size'];
  } else {
    $font_size = FONTSIZE;
  }
  
  echo <<<EOD
<!DOCTYPE html><html>
  <head><title>Tweater</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
<SCRIPT LANGUAGE="JavaScript">
<!--
  function about() {
    alert("Tweater is an app created by David Crandall, to show his programming skills using PHP, MySQL, Bootstrap, Angular.js, JavaScript, HTML and CSS.");
  };
 
  function contact() {
    alert("David Crandall's email is crandadk@aol.com");
  };
-->
</SCRIPT>
<body style='background-color:#C0C0F0;font-size:{$font_size}px'>
EOD;
  require_once '_header.php';
  echo "<ul><!--li>In Conversation Mode, your page will reload every few seconds, so the Tweats of the person(s)
  you're talking with<br />will appear. They should also click on their Conversation Mode button so they will see 
  your Tweats within a few seconds.</li--><li>To show a list of all users, just click the User Search button at the right.</li>
  <li>The picture file upload function is temporarily disabled for 
  security reasons.</li><li>Click the Home button above to return to the main page, or your browser's Back 
  button<br />to go back to previous page(s).</li><li>To add a hashtag to a Tweat, just include the # sign 
  followed by the hashtag,<br />such as #popmusic (no spaces between multiple words). Only one hashtag is 
  allowed per Tweat,<br />but you could post the same Tweat twice with different hashtags, theoretically...</li>
  </ul></body></html>";