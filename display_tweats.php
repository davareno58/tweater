<?php
// Display Tweats
  if ($chat == 'true') {
// Display Tweats as Chat in iframe
    $name = strtr($name, " ", "+");
    echo "<iframe id='tweats_iframe' src='get_tweats.php?name={$name}&return={$ret}' style='width:1250px;height:590px;position:absolute;" . 
      "left:0px'><p>Your browser doesn't support iframes!</p></iframe>";
    echo "<p style='position:relative;left:20px;top:590px'><i>Note:&nbsp;&nbsp;The creator of this website " . 
        "doesn't assume responsibility for its usage by others.</i></p>";
    echo "<br /><img id='picture' src='pictures/{$picture_url}' style='position:relative;top:570px' />";
    echo "<p style='position:relative;top:590px'>&nbsp;</p>";
  } else {
// Display Tweats as non-Chat without iframe
    echo "<div id='pic_top' style='position:relative;left:7px;top:-12px'><img id='top' src='transparent.gif' onload='startPic();' /></div></div></div>";
// Get Tweats from followed users and signed-in user for non-Chat Mode
    $mysqli2 = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
    $mysqli2->set_charset("utf8");
    if ($stmt = $mysqli2->prepare("SELECT t.id, t.user_name, t.tweat, t.hashtag, u.name FROM tweats AS t INNER JOIN " . 
      "users AS u ON t.user_name = u.user_name WHERE t.user_name IN " . 
      "(SELECT followed_one FROM followed_ones AS f WHERE f.user_name = ?) ORDER BY t.id DESC LIMIT ?")) {
      $stmt->bind_param('ss', $user_name, $shown_limit);
      $stmt->execute();
      $result = $stmt->get_result();
    } else {
      echo "Error: " . $mysqli2->error . " & " . $mysqli3->error;
    }
    $zebra = "#E0E0FF"; // Alternating Tweat row colors
    while ($myrow = $result->fetch_assoc()) {
      if ($myrow['name']) {
        $myrow_name = $myrow['name'];
        $myrow_tweat = $myrow['tweat'];
        $tid = $myrow['id'];
        $myrow_hashtag = $myrow['hashtag'];
      } else {
        $myrow_name = "";
        $myrow_tweat = "";
        $myrow_hashtag = "";
      }
      if (substr($myrow_hashtag, 0, 3) == "DEL") {

      // Delete old chat tweat
        if (time() > substr($myrow_hashtag, 3)) {
          $stmt->close();
          $stmt = $mysqli2->prepare("DELETE FROM tweats WHERE id = ?");
          $stmt->bind_param('i', $tid);
          $stmt->execute();
          continue;
        }
      }
      echo "<div class='row' style='color:black;background-color:{$zebra}'><div class='col-sm-4 col-md-3 text-right' " . 
      "style='word-wrap: break-word; margin-right: 1em; position:relative; left:46px'><b>" . 
        wordwrap($myrow_name, 40, '<br />', true) . 
        "</b>:</div><div class='col-sm-8 col-md-9' style='margin-left: -2em; position:relative; left:46px'><p>" . 
        wordwrap($myrow_tweat, $tweat_width, '<br />', true);
      if ($myrow_name == $name) {
        $no_quote_tweat = strtr(substr($myrow_tweat,0,80), "\"'\t\r\n\f", "      ");
// X button to delete Tweat
        echo "&nbsp;&nbsp;<img src='xdel.png' style='position:relative;top:-1px' onclick='if (confirm(\"Are you sure you want to delete this Tweat?:  " . 
            $no_quote_tweat . "...\")) {location.replace(\"{$self_name}?delete_tweat=\" + {$tid});}' />";
      }
      echo "</p></div></div>";
      if ($zebra == "#C0C0F0") {
        $zebra = "#E0E0FF";
      } else {
        $zebra = "#C0C0F0";
      }
    }

    $stmt->close();
    $mysqli2->close();
    echo "</div>";
// Disclaimer    
    echo "<div style='text-align:center'><br /><i>Note:&nbsp;&nbsp;The creator of this website " . 
      "doesn't assume responsibility for its usage by others.</i><br /><br />" . 
      "<div class='row' style='color:black'><div class='col-sm-4 col-md-3 text-right'>" . 
      "<div id='pic_bottom' style='position:absolute;left:7px'>";
    echo "<img id='bottom' src='transparent.gif' />";
    echo "</div></div><div class='col-sm-8 col-md-9'></div></div>";
  }
