<?php
require_once 'app_config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

if (isset($_COOKIE['shown_limit'])) {
  $shown_limit = $_COOKIE['shown_limit'];
} else {
  $shown_limit = 50;
}
$user_name = trim($_COOKIE['user_name']);
$password = trim($_COOKIE['password']);

$conn = new mysqli(DATABASE_HOST,USERNAME,'',DATABASE_NAME);
    if (!$conn) {
      die('Could not connect: ' . mysqli_error($conn));
    }
$conn->set_charset("utf8");
$stmt = $conn->prepare("SELECT t.id, t.user_name, t.tweat, u.name FROM tweats AS t INNER JOIN " . 
    "users AS u ON t.user_name = u.user_name WHERE (u.user_name = ?) AND (binary password_hash = ?) AND (t.user_name IN " . 
    "(SELECT followed_one FROM followed_ones AS f WHERE f.user_name = ?)) ORDER BY t.id DESC LIMIT ?");
$stmt->bind_param('ssss', $user_name, crypt($password,"pling515"), $user_name, $shown_limit);
$stmt->execute();
$result = $stmt->get_result();
$outp = "";
while($rs = $result->fetch_array(MYSQLI_ASSOC)) {
  if ($outp != "") {
    $outp .= ", ";
  }
  $outp .= '{ "Name":"' . str_replace('"', 'quotmk', $rs["name"]) . '", ';
  $outp .= '"Tweat":"' . str_replace('"', 'quotmk', $rs["tweat"]) . '", ';
  $outp .= '"Tid":"'. $rs["id"] . '" }'; 
}
$outp ='{ "records":[ '.$outp.' ] }';
$conn->close();
echo($outp);
?>