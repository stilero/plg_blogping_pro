<?php
/**
 * Class Twitter Calls
 *
 * @version  1.0
 * @package Stilero
 * @subpackage Class Twitter
 * @author Daniel Eliasson (joomla@stilero.com)
 * @copyright  (C) 2013-jan-06 Stilero Webdesign (www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

class TwitterCall extends OauthServer{

    public function __construct($OauthClient, $OauthUser, $url = "") {
        parent::__construct($OauthClient, $OauthUser, $url);
    }
    
    /**
     * Prepares an auth call
     * @param strimng $method GET/POST
     */
    public function prepareAuthCall($method){
        
    }
    
    /**
     * Prepares a non auth call
     * @param String $method GET/POST
     */
    public function prepareNonAuthCall($method){
        
    }
    
}
?>
