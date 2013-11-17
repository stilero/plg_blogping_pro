<?php
/**
 * Class for handling Twitter responses
 *
 * @version  1.0
 * @package Stilero
 * @subpackage plg_twittertweet
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-aug-14 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroBPPTwitterResponse{
    
    protected $_jsonResponse;
    protected $_Response;
    protected $_isError;
    public $errorMsg;
    public $errorCode;
    public $id;
    
    /**
     * Decodes Twitter responses.
     * @param string $jsonResponse JSON string from the Twitter Response
     * @throws Exception on Fail
     */
    public function __construct($jsonResponse) {
        $this->_jsonResponse = $jsonResponse;
        try {
           $this->_Response = json_decode($jsonResponse); 
        } catch (Exception $exc) {
            throw new Exception('not json string: '.$exc->getMessage());
        }
        $this->hasID();
        $this->hasError();
    }
    
    /**
     * Checks if the response contains an ID = successful response.
     * @return boolean true on success
     */
    public function hasID(){
        if( isset($this->_Response->id) ){
            $this->id = $this->_Response->id;
            $this->_isError = false;
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * Checks if the response contains an error, and returns true if so.
     * @return boolean true if error is found
     */
    public function hasError(){
        if(isset($this->_Response->errors)){
            $this->_isError = true;
            if(isset($this->_Response->errors[0]->code)){
                $this->errorCode = $this->_Response->errors[0]->code;
            }
            if(isset($this->_Response->errors[0]->message)){
                $this->errorMsg = $this->_Response->errors[0]->message;
            }
            return true;
        }else{
            return false;
        }
    }
}
