<?php

/**
 * @file
 * Class for representing a Group.
 */

class Group
{
    private $id;
    private $name;
    private $assets = array(); //the assets that are managed by this group.
    private $users = array(); //the users that are member of this group.

    /**
     * Group constructor.
     *
     * @param $id
     * @param $name
     */
    public function __construct(int $id, string $name) {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId(): int {
      return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void {
      $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string {
      return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void {
      $this->name = $name;
    }

    /**
     * Add an asset.
     *
     * @param Asset $asset
     */
    public function addAsset(Asset $asset) {
        $this->assets[] = $asset;
    }

    /**
     * Add an user.
     *
     * @param User $uset
     */
    public function addUser(User $user) {
        $this->users[] = $user;
    }

    /**
     * Get array of Assets.
     *
     * @return array
     */
    public function getAssets(): array {
        return $this->assets;
    }

    /**
     * Get array of Users.
     *
     * @return array
     */
    public function getUsers(): array {
        return $this->users;
    }

    /**
     * Check if user is a member of the group.
     *
     * @param User $user
     *
     * @return bool
     *   Returns true if user is member of false otherwise.
     */
    public function isUserMember(User $user) {
        return in_array($user, $this->users);
    }
    
    /**
     * Get all assets of a user.
     *
     * @param User $user
     *   The user to get assets.
     * @param array $groups
     *   List of groups (db).
     * @param bool $json
     *   True to return structure in JSON format (or false for Array).
     *
     * @return array|string
     *   The list of assets.
     */
    static public function getAssetsOfUser(User $user, array $groups, $json = true) {
        $response = array();
        foreach ($groups as $group) {
            //If user is an admin or member of this group, get group assets.
            if ($user->isUserAdmin() || $group->isUserMember($user)) {
                foreach($group->getAssets() as $asset) {
                    $response[] = array('id' => $asset->getId(), 'name' => $asset->getName());
                }
            }
        }
        if ($json) {
            header('Content-Type: application/json');
            return json_encode($response);
        }
        return $response; 
    }
}
