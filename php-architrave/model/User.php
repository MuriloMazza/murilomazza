<?php

/**
 * @file
 * Class for representing User.
 */

class User
{
    const ROLE_NORMAL_USER = 'User';
    const ROLE_ADMIN = 'Admin';
    
    private $id;
    private $firstName;
    private $lastName;
    private $roles = array();

    /**
     * User constructor.
     *
     * @param $id
     * @param $firstName
     * @param $lastName
     * @param $roles
     */
    public function __construct(int $id, string $firstName, string $lastName, array $roles) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->roles = $roles;
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
    public function getFirstName(): string {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void {
        $this->lastName = $lastName;
    }

    /**
     * @return array
     */
    public function getRoles(): array {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void {
        $this->roles = $roles;
    }
    
    /**
     * Check if user is an admin.
     *
     * @return bool
     *   Returs true if user is admin or false otherwise.
     */
    public function isUserAdmin() {
        return in_array(User::ROLE_ADMIN, $this->roles);
    }
    
    /**
     * Get user by user id if it exists.
     * @param int $userId
     *   User Id.
     * @param array $users
     *   List of users (db).
     *
     * @return array
     *   Return user data or null if it does not exist.
     */
    static public function getUser(int $userId, array $users) {
        foreach($users as $user) {
            if ($user->getId() == $userId) {
                return $user;
            }
        }
        return null; 
    }
}
