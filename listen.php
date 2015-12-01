<?php

include 'utils.php';

session_start();

$username = $_SESSION["username"];
if(!isset($username) || $username == "") {
    http_response_code(403);
    echo "$username is not connected. Press connect.\n";
    echo "Maybe something went wrong with the session, I don't know. Refresh browser 3 times.\n";
}

opendb();

$user = get_user($username);
if(!$user) {
    http_response_code(403);
    echo "$username is not connected. Press connect.\n";
    echo "Maybe you've timed out my friend. Refresh browser 5 times\n";
}

touch_user($username);

cleanup();

$d = opendir(constant('BASEPATH').$username);
$files = Array();
while(false !== ($entry = readdir($d))) {
    if(preg_match("/^\./", $entry)) {
        continue;
    }
    if(preg_match("/^__/", $entry)) {
        continue;
    }
    array_push($files, $entry);
}
asort($files);
if(count($files) >= 1) {
    $file = constant('BASEPATH').$username.'/'.$files[0];
    $data = file_get_contents($file);
    header('content-type: application/octet-stream');
    header('Content-Length: ' . filesize($file));
    unlink($file);
    echo $data;
}
?>
