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

$users_php_sucks = get_users();
$users = array_filter($users_php_sucks, function($el) {
    global $username;
    if($el->name === $username) return false;
    return $el;
});

$data = file_get_contents("php://input");

foreach($users as $u) {
    $intelligentFileName = time();
    if(is_file(constant('BASEPATH').$u->name.'/'.$intelligentFileName)) {
        $intelligentFileName = $intelligentFileName . "_1";
    }
    
    $tempFileName = "__$intelligentFileName";
    $intelligentFileName = constant('BASEPATH').$u->name.'/'.$intelligentFileName;
    $tempFileName = constant('BASEPATH').$u->name.'/'.$tempFileName;

    file_put_contents($tempFileName, $data);
    rename($tempFileName, $intelligentFileName);
    echo "wrote contents for $u->name from $username in $intelligentFileName\n";
}

?>
