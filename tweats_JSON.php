<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once 'app_config.php';

$user_name = $_COOKIE['user_name'];
$name = $_GET['name'];
$name = strtr($name, "+", " ");
$ret = $_GET['return'];
if (isset($_COOKIE['shown_limit'])) {
  $shown_limit = $_COOKIE['shown_limit'];
} else {
  $shown_limit = 50;
}

$mysqli2 = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
$mysqli2->set_charset("utf8");
$stmt = $mysqli2->prepare("SELECT t.id, t.user_name, t.tweat, t.hashtag, u.name FROM tweats AS t " . 
  "INNER JOIN users AS u ON t.user_name = u.user_name WHERE t.user_name IN " . 
  "(SELECT followed_one FROM followed_ones AS f WHERE f.user_name = ?) ORDER BY t.id DESC LIMIT ?");
$stmt->bind_param('ss', $user_name, $shown_limit);
$stmt->execute();
$result = $stmt->get_result();
$outp = "[";
while($rs = $result->fetch_assoc()) {
  if ($outp != "[") {$outp .= ",";}
  $rs_name = $rs["name"];
  $rs_tweat = $rs["tweat"];
  $rs_id = $rs["id"];
  $rs_hashtag = $rs["hashtag"];

  $rs_name = str_replace("\\", "\\\\", $rs_name);
  $rs_name = str_replace('"', '\"', $rs_name);

  $rs_tweat = str_replace('\\', '\\\\', $rs_tweat);
  $rs_tweat = str_replace('"', '\"', $rs_tweat);

  $rs_hashtag = str_replace('\\', '\\\\', $rs_hashtag);
  $rs_hashtag = str_replace('"', '\"', $rs_hashtag);
    
  $outp .= '{"name":"' . $rs_name . '",';
  $outp .= '"tweat":"' . $rs_tweat . '",';
  $outp .= '"tid":"'. $rs_id . '",'; 
  $outp .= '"hashtag":"'. $rs_hashtag . '"}'; 
}
$outp .="]";
$stmt->close();
$mysqli2->close();
echo($outp);