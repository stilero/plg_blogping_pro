<?php

/**
 * Class Twitter Timelines
 *
 * @version  1.0
 * @package Stilero
 * @subpackage Class Twitter
 * @author Daniel Eliasson (joomla@stilero.com)
 * @copyright  (C) 2013-aug-01 Stilero Webdesign (www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */
class TwitterTimelines extends OauthServer{
    
    const API_BASE_URL = 'https://api.twitter.com/1.1/statuses/';
    const API_MENTIONS_URL  = 'mentions_timeline.json';
    const API_USER_TIMELINE_URL  = 'user_timeline.json';
    const API_HOME_TIMELINE_URL  = 'home_timeline.json';
    
    public function __construct($OauthClient, $OauthUser) {
        parent::__construct($OauthClient, $OauthUser);
    }
    
    public function mentions(){
        $apiUrl = self::API_BASE_URL.self::API_MENTIONS_URL;
        $this->request($apiUrl, array(), self::REQUEST_METHOD_GET);
        return $this->getResponse();
    }
    
    public function user(){
        $apiUrl = self::API_BASE_URL.self::API_USER_TIMELINE_URL;
        $this->request($apiUrl, array(), self::REQUEST_METHOD_GET);
        return $this->getResponse();
    }
    
    public function home(){
        $apiUrl = self::API_BASE_URL.self::API_HOME_TIMELINE_URL;
        $this->request($apiUrl, array(), self::REQUEST_METHOD_GET);
        return $this->getResponse();
    }
}
?>
