<?php

/**
 * @file
 * Index file.
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'model/User.php';
require 'model/Asset.php';
require 'model/Group.php';
require 'db/sample_data.php';

/**
 * Help message to assist guest.
 */
function printHelpMessage($users) {
    echo "You must inform a User ID as parameter, with endpoint 'assets'. (e.g.: try /assets/1)<br />";
    echo "List of users available: <br /><br />";
    foreach ($users as $user) {
        echo "[Id: ".$user->getId()."] ".$user->getFirstName() . " " . $user->getLastName() . "<br />";
    }
}

/**
 * If User ID is not informed, print instructions.
 */
if (empty($_GET['user_id'])) {
    printHelpMessage($users);
    exit();
}

/**
 * Get user data by user id which was informed.
 */
$user = User::getUser($_GET['user_id'], $users);

/**
 * If user does not exist, print instructions.
 */
if (is_null($user)) {
    echo "The User ID you informed is not valid (this user does not exist).<br /><br />";
    printHelpMessage($users);
    exit();
}

/**
 * "Assets Endpoint result"
 *
 * Print assets result (JSON).
 */
$result = Group::getAssetsOfUser($user, $groups);
echo $result;
