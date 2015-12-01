<?php

define("BASEPATH", "scratch/");

function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }

    }

    return rmdir($dir);
}

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
                try {
                    remove_user($u->name);
                    echo "removed".$u->name."\n";
                } catch(Exception $e) {
                    // sqlite doesn't like multiple users (as stated on their front page)
                }
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
        deleteDirectory(constant("BASEPATH").$name);
    }
}

function get_user($name)
{
    global $dbh;
    $again = true;
    while($again) {
        $again = false;
        try {
            $getUsers = $dbh->prepare("SELECT name,last FROM users WHERE name = :name");
            $getUsers->bindValue(":name", $name, SQLITE3_TEXT);
            $result = $getUsers->execute();
        } catch(Exception $e) {
            $again = true;
        }
    }

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
    $again = true;
    while($again) {
        $again = false;
        try {
            $getUsers = $dbh->prepare("SELECT name,last FROM users");
            $result = $getUsers->execute();
        } catch(Exception $e) {
            $again = true;
        }
    }

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
    $again = true;
    while($again) {
        $again = false;
        try {
            $getUsers = $dbh->prepare("UPDATE users SET last = :last WHERE name = :name");
            $getUsers->bindValue(":name", $username, SQLITE3_TEXT);
            $getUsers->bindValue(":last", time(), SQLITE3_INTEGER);
            $result = $getUsers->execute();
        } catch(Exception $e) {
            // do the unspeakable
            $again = true;
        }
    }
}

function add_user($username)
{
    if($user = get_user($username)) 
    {
        echo "found $username\n";
        touch_user($username);
        return;
    }
    if(!is_dir(constant("BASEPATH").$username)) {
        if(!mkdir(constant("BASEPATH").$username)) {
            http_response_code("500");
            echo "failed to create user\n";
        }
    }

    global $dbh;
    $again = true;
    while($again) {
        $again = false;
        try {
            $getUsers = $dbh->prepare("INSERT INTO users (name,last) VALUES(:name,:last)");
            $getUsers->bindValue(":name", $username, SQLITE3_TEXT);
            $getUsers->bindValue(":last", time(), SQLITE3_INTEGER);
            $result = $getUsers->execute();
        } catch(Exception $e) {
            // do the unthinkable
            $again = true;
        }
    }
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
