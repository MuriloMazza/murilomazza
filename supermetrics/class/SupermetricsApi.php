<?php

/**
 * @file
 * Custom API class for Supermetrics calls.
 */

namespace Api;
 
require_once 'Api.php';
require_once 'settings.php';
 
class SupermetricsApi extends Api
{
    const ENDPOINT_REGISTER = 'register';
    const ENDPOINT_POSTS = 'posts';
    
    private $url;
    
    /**
     * SupermetricsApi constructor.
     */
    public function __construct() {
        global $settings;
        parent::__construct($settings->supermetricsApi->url);
    }
    
    /**
     * Register client to API.
     *
     * @param array $clientData
     *   Data of client to register and authenticate.
     *   
     * @return string
     *   Result of register (json).
     */
    public function register($clientData) {
        return $this->call('POST', self::ENDPOINT_REGISTER, $clientData);
    }
    
    /**
     * Get social media posts of a page.
     * 
     * @param array $data
     *   Data to fetch the posts.
     *   
     * @return string
     *   Post results (json).
     */
    public function posts($data) {
        return $this->call('GET', self::ENDPOINT_POSTS, $data);
    }
}
