<?php
  $ret = '_edge'; // MS Edge browser version
  $header = '_header' . $ret . '.php';
  $title_position = "right: -77px;";
  $sign_in_width = "width:506px;";
  $margin_left = "margin-left: -24px;";
  $interests_position = "left:0px;";
  $interests_width = "width:310px;position:relative;top:2px";

  require_once 'app_config.php';
  require_once 'main.php';

// Display Interests and Information Entry and Tweat Entry and various function buttons
  echo <<<EODF
<form action="{$self_name}" method="POST" role="form" id="intinfo">
<span>
<div>
<fieldset class="fieldset-auto-width" style="float:left"><b>Interests and Information:&nbsp;&nbsp;&nbsp;</b>
<button type="submit" id="intsubmit" name="intsubmit" class="btn btn-info" style="margin-left:-9px;position:relative;{$interests_position}">
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
<div class="col-sm-8 col-md-9" style='background-color:#9999FF;margin-left: 0px;margin-right: 2px;border: 2px outset 
  darkblue;padding:4px;height:259px;width:854px'>
<form action="{$self_name}" method="POST" role="form" id="tweatform"><span><div ng-app="">
<fieldset class="fieldset-auto-width" style="float:left">
<div class="span9 form-group" style="height:170px">
<textarea class="textarea inbox" style="width:840px" rows="3" cols="104" id="tweat" name="tweat" ng-model="tweat" 
  maxlength="{$tweat_max_size}" placeholder=
  "--Type your Tweat here (limit: {$tweat_max_size} characters) and then click the Post button or press Enter.--">
  </textarea><br />
<button type="submit" class="btn btn-success" style="position:relative;top:0px">Post&nbsp;<span class="glyphicon glyphicon-send"></span></button>
<span style="font-family:Courier New, monospace;position:relative;top:0px">
<span ng-bind="('0000' + ({$tweat_max_size} - tweat.length)).slice(-3)"></span> characters left
</span>
<span><button type="button" class="btn btn-warning" onclick="textErase();" style="position:relative;top:0px">Erase <span style='color:black;background-color:red'>&nbsp;X&nbsp;</span>
</button>
<button type="button" class="btn btn-success" onclick="textLarger();" style="position:relative;top:0px;width:90px">Text Size<span class="glyphicon glyphicon-zoom-in"></span></button>
<button type="button" class="btn btn-primary" onclick="textSmaller();" 
style="position:relative;top:0px;padding-left:2px;padding-right:2px;width:84px">Text Size<span class="glyphicon glyphicon-zoom-out"></span></button>
<button type="button" class="btn btn-info" onclick="fontEntry();" style="position:relative;top:0px">Font</button>
<button type="button" class="btn btn-primary" onclick="toggleBW();" style="position:relative;top:0px;padding-left:2px;padding-right:2px;width:47px">B/W</button>
<button type="button" class="btn btn-info" style="position:relative;top:0px;width:49px;padding-left:3px;padding-right:3px" onclick="tweatWidth();">Width</button>&nbsp;
<button type="submit" class="btn btn-{$chat_button}" onclick="chatToggle({$chat_toggle})" style="position:relative;left:-6px;top:0px">{$chat_button_action} Chat</button>
<input type="hidden" class="form-control" name="name" value={$esc_name}><br /></form>
<form><span style="position:relative;top:1px">Hashtag Search: #</span><input type="text" id="hashtag_search" style="font-size:{$fontsize};width:450px;position:relative;top:2px"
  name="hashtag_search" maxlength="30" placeholder="To search Tweats, type the hashtag here and press--&gt;"></input>
  <button type="button" class="btn btn-primary" onclick="hashtagSearch();" style="margin:2px;position:relative;top:1px">Hashtag <span class="glyphicon glyphicon-search"></span></button>&nbsp;
<button type="button" class="btn btn-warning" onclick="shownLimit();" style="position:relative;top:1px;padding-left:3px;padding-right:4px">Limit: {$shown_limit}</button>
</span></span><br /></div></fieldset></div></form>
<form action="user_search_results.php?admin={$status}&return={$ret}" method="POST" role="form" id="user_search_form" target="_blank"><br />
<nobr><span style="position:relative;top:-27px">User Search: </span><textarea class="textarea inbox" rows="1" cols="75" id="search_any" name="search_any" maxlength="250" 
  style="font-size:{$fontsize};position:relative;top:-19px;width:613px" placeholder="To search by interests, info or names, type them here and press--&gt;"></textarea>
&nbsp;<button type="submit" class="btn btn-info" style="position:relative;top:-28px">User <span class="glyphicon glyphicon-user"></span>
<span class="glyphicon glyphicon-search"></span></button></nobr><br />
</form>
<form action="boolean_user_search_results.php?admin={$status}&return={$ret}" method="POST" role="form" target="_blank"><br />
<nobr><span style="position:relative;top:-49px;left:-40">Boolean Search: <input type="text" 
  style="position:relative;width:250px" placeholder="First Search Term" id="search_one" 
  name="search_one" maxlength="30" size="26">
<select class="inbox" id="search_type" name="search_type" style="position:relative;left:-5px">
          <option value="AND" default>AND</option>
          <option value="OR">OR</option>
          <option value="NOT">NOT</option>
</select><input type="text" style="position:relative;left:-5px;width:250px" placeholder="Second Search Term" 
  id="search_two" name="search_two" value="" maxlength="30" size="26">
<button type="submit" class="btn btn-warning" style="position:relative;top:-2px;left:-6px">Boolean User <span class="glyphicon glyphicon-search"></span></button>
</span></nobr></form></div></div></div><div class='row'>
EODF;

// Display Tweats
  require_once 'display_tweats.php';
  
  echo "&nbsp;<br />&nbsp;<br />&nbsp;</div></body></html>";
  exit();
  
