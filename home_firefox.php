<?php
  $ret = '_firefox'; // Firefox browser version
  $header = '_header' . $ret . '.php';
  $title_position = "right: -77px;";
  $sign_in_width = "width:506px;";
  $margin_left = "margin-left: -43px;";
  $interests_position = "left:3px;";
  $interests_width = "width:310px;position:relative;top:2px";

  require_once 'app_config.php';
  require_once 'main.php';

// Display Interests and Information Entry and Tweat Entry and various function buttons
  echo <<<EODF
<form action="{$self_name}" method="POST" role="form" id="intinfo">
<span>
<div>
<fieldset class="fieldset-auto-width" style="float:left"><b>Interests and Information:&nbsp;&nbsp;&nbsp;</b>
<button type="submit" id="intsubmit" name="intsubmit" class="btn btn-info" style="margin-left:-9px;position:relative;left:3px">
Update</button><input type="hidden" name="message" value="Updated Interests and Information! (Limit: {$tweat_max_size} bytes.)"></input>
<div class="span3 form-group">
<textarea class="textarea inbox" rows="4" cols="36" id="interests" name="interests" maxlength="{$tweat_max_size}" 
  placeholder="You may type your interests and information here and press Update."
  style="font-size:{$fontsize};height:80px;width:310px;position:relative;top:2px">{$interests}</textarea>
</div>
</fieldset>
</div>
</span>
</form>
</div>
<div class='col-md-9' style='background-color:#9999FF;margin-left: 0px;margin-right: 6px;border: 4px outset 
  darkblue;padding:10px;height:259px;width:869px'>
<form action="{$self_name}" method="POST" role="form" id="tweatform"><span><div ng-app="">
<fieldset class="fieldset-auto-width" style="float:left">
<div class="span9 form-group" style="height:170px">
<textarea class="textarea inbox" style="width:840px;height:89px" rows="3" cols="104" id="tweat" name="tweat" ng-model="tweat" 
  onkeyup="showCharsLeftAndLimit(this);" maxlength="{$tweat_max_size}" placeholder=
  "--Type your Tweat here (limit: {$tweat_max_size} characters) and then click the Post Tweat button or press Enter.--">
  </textarea><br />
<button type="submit" class="btn btn-success" style="position:relative;top:-2px">Post Tweat</button>
<span style="font-family:Courier New, monospace;position:relative;top:-3px">
<span ng-bind="('0000' + ({$tweat_max_size} - tweat.length)).slice(-3)"></span> characters left
</span>
<span style="position:relative;top:6px"><button type="button" class="btn btn-warning" onclick="textErase();" style="position:relative;top:-8px">Erase</button>
<button type="button" class="btn btn-success" onclick="textLarger();" style="position:relative;top:-8px;width:100px">Text Size+</button>
<button type="button" class="btn btn-primary" onclick="textSmaller();" style="position:relative;top:-8px;padding-left:2px;padding-right:2px;width:80px">Text Size-</button>
<button type="button" class="btn btn-info" onclick="fontEntry();" style="position:relative;top:-8px">Font</button>
<button type="button" class="btn btn-primary" onclick="toggleBW();" style="position:relative;top:-8px;padding-left:2px;padding-right:2px;width:47px">B/W</button>
<button type="button" class="btn btn-info" style="position:relative;top:-8px;width:49px;padding-left:3px;padding-right:3px" onclick="tweatWidth();">Width</button>&nbsp;
<button type="submit" class="btn btn-{$chat_button}" onclick="chatToggle({$chat_toggle})" style="position:relative;left:-6px;top:-8px">{$chat_button_action} Chat</button>
<input type="hidden" class="form-control" name="name" value={$esc_name}><br /></form></span><span>
<form><span style="position:relative">Hashtag Search: #</span><input type="text" id="hashtag_search" style="font-size:{$fontsize};width:450px;height:26px;position:relative;top:1px"
  name="hashtag_search" maxlength="30" placeholder="To search Tweats, type the hashtag here and press--&gt;"></input>
  <button type="button" class="btn btn-primary" onclick="hashtagSearch();" style="margin:2px;position:relative;top:-2px">Hashtag Search</button>&nbsp;
<button type="button" class="btn btn-warning" onclick="shownLimit();" style="position:relative;top:-2px">Limit: {$shown_limit}</button>
</span></span><br /></div></fieldset></div></form>
<form action="user_search_results.php?admin={$status}&return=_firefox" method="POST" role="form" id="user_search_form" target="_blank"><br />
<nobr><span style="position:relative;top:-22px">User Search: </span><textarea class="textarea inbox" rows="1" cols="84" id="search_any" name="search_any" maxlength="250" 
  style="font-size:{$fontsize};position:relative;top:-19px;width:620px;height:26px" placeholder="To search by interests, info or names, type them here and press--&gt;"></textarea>
&nbsp;<button type="submit" class="btn btn-info" style="position:relative;top:-24px;left:-4px">User Search</button></nobr><br />
</form>
<form action="boolean_user_search_results.php?admin={$status}&return=_firefox" method="POST" role="form" target="_blank"><br />
<nobr><span style="position:relative;top:-45px;left:-33">Boolean Search: <input type="text" 
  style="position:relative;top:0px;width:250px;height:26px" placeholder="First Search Term" id="search_one" 
  name="search_one" maxlength="30" size="26">
<select class="inbox" id="search_type" name="search_type" style="position:relative;left:-5px">
          <option value="AND" default>AND</option>
          <option value="OR">OR</option>
          <option value="NOT">NOT</option>
</select><input type="text" style="position:relative;top:0px;left:-6px;width:250px;height:26px" placeholder="Second Search Term" id="search_two" 
  name="search_two" maxlength="30" size="26">
<button type="submit" class="btn btn-warning" style="position:relative;top:-2px;left:-6px">Boolean Search</button>
</span></nobr></form></div></div></div><div class='row'>
EODF;

// Display Tweats
  if ($chat == 'true') {
// Display Tweats as Chat in iframe
    $name = strtr($name, " ", "+");
    echo "<iframe id='tweats_iframe' src='get_tweats.php?name={$name}&return=_firefox' style='width:1250px;height:590px;position:absolute;" . 
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
      echo "<div class='row' style='color:black'><div class='col-md-3 text-right' " . 
      "style='word-wrap: break-word; margin-right: 1em; position:relative; left:46px'><b>" . 
        wordwrap($myrow_name, 40, '<br />', true) . 
        "</b>:</div><div class='col-md-9' style='margin-left: -2em; position:relative; left:46px'><p>" . 
        wordwrap($myrow_tweat, $tweat_width, '<br />', true);
        if ($myrow_name == $name) {
          $no_quote_tweat = strtr(substr($myrow_tweat,0,80), "\"'\t\r\n\f", "      ");
// X button to delete Tweat
          echo "&nbsp;&nbsp;<img src='xdel.png' style='position:relative;top:-1px' onclick='if (confirm(\"Are you sure you want to delete this Tweat?:  " . 
            $no_quote_tweat . "...\")) {location.replace(\"{$self_name}?delete_tweat=\" + {$tid});}' />";
        }
        echo "</p></div></div>";
    }
    $stmt->close();
    $mysqli2->close();
    echo "</div>";
// Disclaimer    
  echo "<div style='text-align:center'><br /><i>Note:&nbsp;&nbsp;The creator of this website " . 
    "doesn't assume responsibility for its usage by others.</i><br /><br />" . 
    "<div class='row' style='color:black'><div class='col-md-3 text-right'>" . 
    "<div id='pic_bottom' style='position:absolute;left:7px'>";
  echo "<img id='bottom' src='transparent.gif' />";
  echo "</div></div><div class='col-md-9'></div></div>";
  }
  echo "&nbsp;<br />&nbsp;<br />&nbsp;</div></body></html>";
  exit();