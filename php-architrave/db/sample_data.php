<?php

/**
 * @file
 * Instanciating sample data.
 */

/**
 * Instantiating sample users.
 */
$user1 = new User(1, "Albert", "Einstein", [User::ROLE_NORMAL_USER]);
$user2 = new User(2, "Till", "Lindemann (ADMIN)", [User::ROLE_ADMIN, User::ROLE_NORMAL_USER]);
$user3 = new User(3, "Doro", "Pesch", [User::ROLE_NORMAL_USER]);
$user4 = new User(4, "Hansi", "Kursch", [User::ROLE_NORMAL_USER]);
$user5 = new User(5, "Marlene", "Dietrich (ADMIN)", [User::ROLE_ADMIN, User::ROLE_NORMAL_USER]);
$users = [$user1, $user2, $user3, $user4, $user5];

/**
 * Instantiating sample assets.
 */
$asset1 = new Asset(1, "Park Inn Berlin");
$asset2 = new Asset(2, "Treptowers");
$asset3 = new Asset(3, "Steglitzer Kreisel");
$asset4 = new Asset(4, "Upper West");
$asset5 = new Asset(5, "Atrium-Tower");
$asset6 = new Asset(6, "BfA-Hochhaus");
$asset7 = new Asset(7, "GSW-Hochhaus");
$asset8 = new Asset(8, "Telefunken-Hochhaus");

/**
 * Instantiating sample groups.
 */
$group = new Group(1, "Group1");
$group->addUser($user1);
$group->addUser($user2);
$group->addUser($user3);
$group->addAsset($asset1);
$group->addAsset($asset2);
$group->addAsset($asset3);
$groups[] = $group;

$group = new Group(2, "Group2");
$group->addUser($user3);
$group->addUser($user4);
$group->addUser($user5);
$group->addAsset($asset4);
$group->addAsset($asset5);
$groups[] = $group;

$group = new Group(3, "Group3");
$group->addUser($user1);
$group->addUser($user2);
$group->addUser($user5);
$group->addAsset($asset6);
$group->addAsset($asset7);
$group->addAsset($asset8);
$groups[] = $group;
