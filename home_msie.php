<?php
  $ret = '_msie'; // Internet Explorer browser version
  $header = '_header' . $ret . '.php';
  $title_position = "right: -153px;";
  $sign_in_width = "";
  $margin_left = "margin-left: -53px;";
  $interests_position = "left:2px;";
  $interests_width = "";

  require_once 'app_config.php'; // Get configuration data
  require_once 'main.php'; // Get main home page code

// Display Interests and Information Entry and Tweat Entry and various function buttons
  echo <<<EODF
<form action="{$self_name}" method="POST" role="form" id="intinfo" name="intinfo" class="intinfo">
<span>
<div>
<fieldset class="fieldset-auto-width" style="float:left"><b>Interests and Information:&nbsp;&nbsp;&nbsp;</b>
<button type="submit" id="intsubmit" name="intsubmit" class="btn btn-info" style="margin-left:-9px;position:relative;{$interests_position}" >
Update</button><input type="hidden" name="message" value="Updated Interests and Information! (Limit: {$tweat_max_size} bytes.)"></input>
<div class="span3 form-group">
<textarea class="textarea inbox" rows="4" cols="36" id="interests" name="interests" maxlength="{$tweat_max_size}" 
  placeholder="You may type your interests and information here and press Update."
  style="font-size:{$fontsize};height:80px;{$interests_width}">{$interests}</textarea>
</div>
</fieldset>
</div>
</span>
</form>
</div>
<div class='col-sm-8 col-md-9' style='background-color:#9999FF;margin-left: 0px;margin-right: 6px;border: 4px outset 
  darkblue;padding:10px;height:259px'>
<form action="{$self_name}" method="POST" role="form" id="tweatform">
<span>
<div ng-app="">
<fieldset class="fieldset-auto-width" style="float:left">
<div class="span9 form-group" style="height:170px">
<textarea class="textarea inbox" rows="4" cols="103" id="tweat" name="tweat" autofocus ng-model="tweat" 
  maxlength="{$tweat_max_size}" placeholder=
<<<<<<< HEAD
  "--Type your Tweat here (limit: {$tweat_max_size} characters) and then click the Post button or press Enter.--">
=======
  "--Type your Tweat here (limit: {$tweat_max_size} characters) and then click the Post Tweat button or press Enter.--">
>>>>>>> 78907f3280d6436513e9090ba83b76eacc27e842
  </textarea><br />
<button type="submit" class="btn btn-success">Post&nbsp;<span class="glyphicon glyphicon-send"></span></button>
<span style="font-family:Courier New, monospace">
<span ng-bind="('0000' + ({$tweat_max_size} - tweat.length)).slice(-3)"></span> characters left
</span>
<span><button type="button" class="btn btn-warning" onclick="textErase();">Erase <span style='color:black;background-color:red'>&nbsp;X&nbsp;</span>
</span></button>
<button type="button" class="btn btn-success" style="width:90px" onclick="textLarger();">Text Size<span class="glyphicon glyphicon-zoom-in"></span></button>
<button type="button" class="btn btn-primary" style="padding-left:2px;padding-right:2px;width:84px" onclick="textSmaller();">Text Size
<span class="glyphicon glyphicon-zoom-out"></span></button>
<button type="button" class="btn btn-info" onclick="fontEntry();">Font</button>
<button type="button" class="btn btn-primary" style="padding-left:2px;padding-right:2px;width:47px" onclick="toggleBW();">B/W</button>
<button type="button" class="btn btn-info" style="position:relative;left:-1px" onclick="tweatWidth();">Width</button>&nbsp;
<button type="submit" class="btn btn-{$chat_button}" onclick="chatToggle({$chat_toggle})" 
 style="position:relative;left:-6px">{$chat_button_action} Chat</button>
<input type="hidden" class="form-control" name="name" value={$esc_name}><br /></form>
<form><span style="position:relative;top:3px">Hashtag Search: #</span><input type="text" id="hashtag_search" style="font-size:{$fontsize};width:450px;position:relative;top:5px"
  name="hashtag_search" maxlength="30" placeholder="To search Tweats, type the hashtag here and press--&gt;"></input>
  <button type="button" class="btn btn-primary" onclick="hashtagSearch();" style="margin:2px">Hashtag <span class="glyphicon glyphicon-search"></span></button>&nbsp;
<button type="button" class="btn btn-warning" onclick="shownLimit();" style="padding-left:3px;padding-right:3px">Limit: {$shown_limit}</button>
</span></span><br /></div></fieldset></div></form>
<form action="user_search_results.php?admin={$status}&return={$ret}" method="POST" role="form" target="_blank" id="user_search_form"><br />
<nobr><span style="position:relative;top:-22px">User Search: </span><input type="text" id="search_any" name="search_any" size="72" maxlength="250" 
  style="position:relative;top:-19px;height:26px" placeholder="To search by interests, info or names, type them here and press--&gt;" 
  style="font-size:{$fontsize}"></input>&nbsp;<button type="submit" class="btn btn-info" style="position:relative;top:-22px">User <span class="glyphicon glyphicon-user"></span>
<span class="glyphicon glyphicon-search"></span></button></nobr><br />
</form>
<form action="boolean_user_search_results.php?admin={$status}&return={$ret}" method="POST" role="form" target="_blank"><br />
<nobr><span style="position:relative;top:-46px">Boolean Search: <input type="text" 
  style="position:relative;top:3px" placeholder="First Search Term" id="search_one" 
  name="search_one" maxlength="30" size="26">
<select class="inbox" id="search_type" name="search_type" style="position:relative;left:-5px;top:1px">
          <option value="AND" default>AND</option>
          <option value="OR">OR</option>
          <option value="NOT">NOT</option>
</select><input type="text" style="position:relative;top:3px;left:-6px" placeholder="Second Search Term" id="search_two" name="search_two" value="" maxlength="30" size="26">
<button type="submit" class="btn btn-warning" style="position:relative;top:-2px;left:-6px">Boolean User <span class="glyphicon glyphicon-search"></span></button></span></nobr></form>
</div></div></div><div class='row'>
EODF;

// Display Tweats
  require_once 'display_tweats.php';
  
  echo "&nbsp;<br />&nbsp;<br />&nbsp;</div></body></html>";
  exit();
  
