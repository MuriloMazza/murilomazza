<?php

require 'vendor/autoload.php';

require 'model/User.php';
require 'model/Asset.php';
require 'model/Group.php';
require 'db/sample_data.php';

use PHPUnit\Framework\TestCase;

class AssetsTest extends TestCase
{
	const BASE_URI = 'http://localhost:8888';
	const ASSETS_ENDPOINT = '/php-architrave/assets/';
	const READ_SIZE = 1024;
	
	protected $client;
	protected $normalUser;
	protected $adminUser;
	protected $groups;
	
	/**
	 * Set up client and data.
	 */
	protected function setUp() {
		global $groups;
		
		$this->client = new GuzzleHttp\Client([
            'base_uri' => self::BASE_URI
        ]);
		
		//Sample data from a normal user.
		$this->normalUser = new User(1, "Albert", "Einstein", [User::ROLE_NORMAL_USER]);
		//Sample data from an admin user.
		$this->adminUser = new User(2, "Till", "Lindemann (ADMIN)", [User::ROLE_ADMIN, User::ROLE_NORMAL_USER]);
		//Sample data for groups.
		$this->groups = $groups;
	}
	
	/**
	 * Test if normal user data got from endpoint is correct.
	 */
    public function testNormalUser() {
		$response = $this->client->get(self::ASSETS_ENDPOINT.$this->normalUser->getId());
	
		$this->assertEquals(200, $response->getStatusCode());
		
		$data = json_decode($response->getBody()->read(self::READ_SIZE), true);
		$expectedData = Group::getAssetsOfUser($this->normalUser, $this->groups, false);
		
		$this->assertEquals($data, $expectedData);
    }
		
	/**
	 * Test if admin user data got from endpoint is correct.
	 */
    public function testAdminUser() {
		$response = $this->client->get(self::ASSETS_ENDPOINT.$this->adminUser->getId());
	
		$this->assertEquals(200, $response->getStatusCode());
		
		$data = json_decode($response->getBody()->read(self::READ_SIZE), true);
		$expectedData = Group::getAssetsOfUser($this->adminUser, $this->groups, false);
		
		$this->assertEquals($data, $expectedData);
    }
}
