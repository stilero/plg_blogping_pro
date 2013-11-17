<?php

/**
 * Class Twitter Status
 *
 * @version  1.0
 * @package Stilero
 * @subpackage Class Twitter
 * @author Daniel Eliasson (joomla@stilero.com)
 * @copyright  (C) 2013-jan-06 Stilero Webdesign (www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */
class StileroTTTweets extends StileroTTOauthServer{
    
    const API_BASE_URL = 'https://api.twitter.com/1.1/statuses/';
    const API_UPDATE_URL  = 'update.json';
    const API_DESTROY_URL  = 'destroy/';
    const API_RETWEET_URL  = 'retweet/';
    const API_SHOW_URL  = 'show/';
    const API_SHOW_RETWEETS_URL  = 'retweets/';
    const API_URL_ENDING  = '.json';
    
    public function __construct($OauthClient, $OauthUser) {
        parent::__construct($OauthClient, $OauthUser);
    }
    
    /**
     * Update twitter status
     * @param string $message
     * @return string JSON response
     */
    public function update($message) {
        $params = array('status' => $message);
        $apiUrl = self::API_BASE_URL.self::API_UPDATE_URL;
        $this->request($apiUrl, $params, self::REQUEST_METHOD_POST);
        return $this->getResponse();
    }
    
    /**
     * Deletes a twitter status
     * @param string $tweetID A numerical ID for the Tweet to delete
     * @return string JSON response
     */
    public function destroy($tweetID){
        $apiUrl = self::API_BASE_URL.self::API_DESTROY_URL.$tweetID.self::API_URL_ENDING;
        $this->request($apiUrl, array(),self::REQUEST_METHOD_POST);
        return $this->getResponse();
    }
    
    /**
     * Retweet a certain tweet
     * @param string $tweetID A numerical Tweet ID
     * @return string JSON response
     */
    public function retweet($tweetID){
        $apiUrl = self::API_BASE_URL.self::API_RETWEET_URL.$tweetID.self::API_URL_ENDING;
        $this->request($apiUrl);
        return $this->getResponse();
    }
    
    /**
     * Show a certain tweet
     * @param string $tweetID a numerical tweet id
     * @return string JSON response
     */
    public function show($tweetID){
        $apiUrl = self::API_BASE_URL.self::API_SHOW_URL.$tweetID.self::API_URL_ENDING;
        $this->request($apiUrl, array(), self::REQUEST_METHOD_GET);
        return $this->getResponse();
    }
    
    /**
     * Get up to 100 of the first retweets of a certain tweet
     * @param string $tweetID a numerical tweet ID
     * @return string JSON response
     */
    public function showRetweets($tweetID){
        $apiUrl = self::API_BASE_URL.self::API_SHOW_RETWEETS_URL.$tweetID.self::API_URL_ENDING;
        $this->request($apiUrl, array(), self::REQUEST_METHOD_GET);
        return $this->getResponse();
    }
}
?>
