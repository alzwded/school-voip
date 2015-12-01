<?php
// Author: Vlad Mesco
// The file is part of school-voip, a school project on VoIP

include 'utils.php';

session_start();

if(!isset($_POST["username"]) || $_POST["username"] == "") {
    http_response_code(403);
    echo "no user name given";
    exit;
}

opendb();
cleanup();

$users = get_users();
foreach($users as $u) {
    echo $u->name."\n";
}

$_SESSION["username"] = $_POST["username"];
add_user($_SESSION["username"]);

$users = get_users();
foreach($users as $u) {
    echo $u->name."\n";
}

?>
