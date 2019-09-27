<?php

/**
 * @file
 * Class to define an API URL execute calls.
 */

namespace Api;
 
class Api
{
    private $url;

    /**
     * Api constructor.
     *
     * @param string $url
     */
    public function __construct(string $url) {
        $this->url = $url;
    }

    /**
     * Execute an API call.
     *
     * @param string $method
     *   The method for CURL (Post, Get, Put, etc...).
     * @param array $data
     *   The data to send to API.
     *   
     * @return string
     *   The result from API (json).
     */
    public function call($method, $endpoint, $data=array()) {
        $url = $this->url . $endpoint;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        switch ($method) {
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, true);
                if (!empty($data)) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
                break;
            case 'PUT':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                if (!empty($data)) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);	
                }
                break;
            default:
                if (!empty($data)) {
                    $url = sprintf('%s?%s', $url, http_build_query($data));
                }
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        $result = curl_exec($curl); //Send the request, save the response.
        if (!$result) {
            die("Connection Failure");
        }
        curl_close($curl); //Close request.
        
        return json_decode($result);
    }
}
