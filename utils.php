<?php

define("BASEPATH", "scratch/");

$dbh = false;

function opendb()
{
    global $dbh;
    $dbh = new SQLite3("db.sqlite3");
}

function cleanup()
{
    $Users = get_users();
    $now = time();
    foreach($Users as $u) {
        $then = $u->last;
        if($now - $then > 60) {
            echo "removed";
            remove_user($u->name);
        }
    }
}

function remove_user($name)
{
    global $dbh;
    $dropUsers = $dbh->prepare("DELETE FROM users WHERE name = :name");
    $dropUsers->bindValue(":name", $name, SQLITE3_TEXT);
    $dropUsers->execute();

    if(is_dir(constant("BASEPATH").$name)) {
        shell_exec("echo %CD% > log.txt");
        //shell_exec('DEL /Q /S "' . constant("BASEPATH").$name . '"');
    }
}

function get_user($name)
{
    global $dbh;
    $getUsers = $dbh->prepare("SELECT name,last FROM users WHERE name = :name");
    $getUsers->bindValue(":name", $name, SQLITE3_TEXT);
    $result = $getUsers->execute();

    $row = $result->fetchArray(SQLITE3_ASSOC);
    if($row) {
        $u = (object)$row;
        return $u;
    } else {
        return false;
    }
}

function get_users()
{
    global $dbh;
    $getUsers = $dbh->prepare("SELECT name,last FROM users");
    $result = $getUsers->execute();

    $users = Array();
    while($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $u = (object)$row;
        array_push($users, $u);
    }
    return $users;
}

function touch_user($username)
{
    global $dbh;
    $getUsers = $dbh->prepare("UPDATE users SET last = :last WHERE name = :name");
    $getUsers->bindValue(":name", $username, SQLITE3_TEXT);
    $getUsers->bindValue(":last", time(), SQLITE3_INTEGER);
    $result = $getUsers->execute();
}

function add_user($username)
{
    if($user = get_user($username)) 
    {
        echo "found him";
        touch_user($username);
        return;
    }
    if(!is_dir(constant("BASEPATH").$username)) {
        if(!mkdir(constant("BASEPATH").$username)) {
            http_response_code("500");
            echo "failed to create user";
        }
    }

    global $dbh;
    $getUsers = $dbh->prepare("INSERT INTO users (name,last) VALUES(:name,:last)");
    $getUsers->bindValue(":name", $username, SQLITE3_TEXT);
    $getUsers->bindValue(":last", time(), SQLITE3_INTEGER);
    $result = $getUsers->execute();
}

function read_users()
{
    $r = Array();
    $d = opendir( constant("BASEPATH") );
    if(!$d) {
        return false;
    }
    while(false !== ($entry = readdir($d))) {
        if(preg_match("/^\./", $entry)) {
            continue;
        }
        array_push($r, $entry);
    }
    return $r;
}

?>
