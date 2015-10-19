<?php
  require_once 'app_config.php';

  if (isset($_COOKIE['font_size'])) {
    $font_size = $_COOKIE['font_size'];
  } else {
    $font_size = FONTSIZE;
  }
  $bigfont = $font_size * 1.5;
  
  echo <<<EOD
<!DOCTYPE html><html>
  <head><title>Tweater Help</title>
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
<body style='background-color:#99D9EA;font-size:{$font_size}px'>
<div><a href="{$self_name}" style="font-size:{$bigfont}px;color:red;background-color:#990099"><b>
&nbsp;Tweater Help&nbsp;</b></a></div>
EOD;
  echo "<img src='tweatyquestion.png' style='float:right' /><ul><li>To show a list of all users, just click the User Search button at the right.</li>
  <li>The picture file upload function is temporarily disabled for 
  security reasons.</li><li>Click your browser's Back button to go back to previous page(s).</li>
  <li>To update your page or to remove red messages, click on Home at the top left (or your browser's Refresh button).</li>
  <li>Cookies and JavaScript must be enabled for some functions.</li><li>In a Boolean Search, 
  at least the first term must be filled in.</li><li>Wildcards may be used in Hashtag Searches and Boolean Searches:<br />
  ? for any one character, and * for zero or more characters.</li>
  <li>The Limit button at the right sets the number of Tweats shown and the number of Search Results.</li>
  <li>To turn on Chat Mode, click the green Start Chat button at the right.<br />
  It will turn into a red Stop Chat button. In Chat Mode, the Tweats will be<br />
  redisplayed every ten seconds, so any Tweats sent by someone<br />
  you're following will appear automatically without having to click Home<br />
  to reload the page. If the person you're following is also following you,<br />
  and he's in Chat Mode, your new Tweats should appear automatically<br />
  every ten seconds on his page as well, so you can have a real-time<br />
  text conversation in Chat Mode. Actually, several people who are all following each other<br />
  and are all in Chat Mode can have a multi-person conversation! In Chat Mode, any picture<br />
  will be moved to the bottom of the page, and only the ten most recent Tweats are displayed.<br />
  If you don't send a Tweat for five minutes, Chat Mode will be turned off automatically,<br />
  and you would have to click Start Chat to restart it. Tweats sent in Chat Mode will be deleted<br />
  automatically after 24 hours, so they can't have hashtags, and no email notifications<br />
  are sent with these Tweats.</li>
  <li>Passwords are case-sensitive, but usernames are not. Passwords must have at least<br />six characters.</li>
  <li>To add a hashtag to a Tweat, just include the # sign followed by the hashtag,<br />
  such as #popmusic (with no spaces between multiple words). Only one hashtag<br /> 
  can be used in each Tweat, but you could post the same Tweat twice<br />with different hashtags, theoretically...</li>
  </ul></body></html>";
  exit();
  
