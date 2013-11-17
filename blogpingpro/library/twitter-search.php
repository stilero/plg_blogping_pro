<?php

/**
 * Class Twitter Search
 *
 * @version  1.0
 * @package Stilero
 * @subpackage Class Twitter
 * @author Daniel Eliasson (joomla@stilero.com)
 * @copyright  (C) 2013-aug-01 Stilero Webdesign (www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */
class TwitterSearch extends OauthServer{
    
    const API_BASE_URL = 'https://api.twitter.com/1.1/search/';
    const API_SEARCH_TWEETS_URL  = 'tweets.json';
    
    public function __construct($OauthClient, $OauthUser) {
        parent::__construct($OauthClient, $OauthUser);
    }
    
    public function search($query){
        $apiUrl = self::API_BASE_URL.self::API_SEARCH_TWEETS_URL;
        $params = array('q='.$query);
        $this->request($apiUrl, $params, self::REQUEST_METHOD_GET);
        return $this->getResponse();
    }
}
?>
