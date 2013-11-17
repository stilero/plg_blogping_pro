<?php
/**
 * Class for making necessary checks. Dependent on stileroTT helpers.
 *
 * @version  1.0
 * @package Stilero
 * @subpackage plg_twittertweet
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-aug-13 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroBPPShareCheck{
    
    protected $_Article;
    protected $_Table;
    protected $_minBetweenPosts;
    protected $_dateLimit;
    protected $_catList;
    protected $_isOverridingDelayCheck;
    protected $_isBackend;
    
    /**
     * Class for checking before posts
     * @param Object $Article Article object returned from the JArticle class
     * @param StileroBPPShareTable $Table Table object
     * @param int $minBetweenPosts Minutes between posts
     * @param date $dateLimit A date to only post newer than this date, for example 2013-08-13.
     * @param string $catList Comma separated list of categories to post, for example 2,3,5,7
     * @param boolean $isOverridingDelayCheck Set this to allways post on save
     * @param boolean $isBackend True if called from backend
     */
    public function __construct($Article, StileroBPPShareTable $Table, $minBetweenPosts=5, $dateLimit='', $catList='', $isOverridingDelayCheck=false, $isBackend=true) {
        $this->_Article = $Article;
        $this->_Table = $Table;
        $this->_minBetweenPosts = $minBetweenPosts;
        $this->_dateLimit = $dateLimit;
        $this->_catList = $catList;
        $this->_isOverridingDelayCheck = $isOverridingDelayCheck;
        $this->_isBackend = $isBackend;
    }
    
    /**
     * Checks if a value is found in a list
     * @param string $commaSepList Comma separated list of values
     * @param string $needle The string to search for in the list
     * @return boolean true on success
     */
    public static function isFoundInList($commaSepList, $needle){
        $items = explode(",", $commaSepList);
        if( (in_array($needle, $items))){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    /**
     * Checks if date A is newer than date B
     * @param date $dateA
     * @param date $dateB
     * @return boolean
     */
    public static function isANewerThanB($dateA, $dateB){
        if( ($dateA > $dateB)){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * Checks if all checks are passing before a post
     * @return boolean true if all checks are OK
     * @throws Exception On error
     */
    public function hasFullChecksPassed(){
        $isSuccessful = true;
        if(!StileroBPPServerRequirementHelper::hasCurlSupport()) {
            throw new Exception('TwitterTweet: Server Missing Curl support.'); 
            $isSuccessful = false;
        }
        if(!StileroBPPServerRequirementHelper::hasFileGetSupport()){
            throw new Exception('TwitterTweet: Server Missing Support for file_get_contents');
            $isSuccessful = false;
        } 
        if(!$this->_Article->isPublished) {
            $isSuccessful = false;
        } 
        if(!$this->_Article->isPublic) {
            $isSuccessful = false;
        } 
        if( (!self::isANewerThanB($this->_Article->publish_up, $this->_dateLimit)) && ($this->_dateLimit != '') ){
            $isSuccessful = false;
        }
        if ( (!self::isFoundInList($this->_catList, $this->_Article->catid)) && ($this->_catList != '')) {
            $isSuccessful = false;
        }
        if(!$this->_Table->isTableFound()){
            $this->_Table->createTable();
        }
        if( (!$this->_isOverridingDelayCheck)){
            if( $this->_Table->isTooEarly($this->_minBetweenPosts) ) {
                $message = JText::_(plgContentTwittertweet::LANG_PREFIX.'TOO_EARLY');
                if($this->_isBackend){
                    StileroBPPMessageHelper::show($message, StileroBPPMessageHelper::TYPE_NOTICE);
                }
                $isSuccessful = false;
            }
            if( $this->_Table->isLogged($this->_Article->id, $this->_Article->component) ){
                $message = JText::_(plgContentTwittertweet::LANG_PREFIX.'DUPLICATE_TWEET');
                if($this->_isBackend){
                    StileroBPPMessageHelper::show($message, StileroBPPMessageHelper::TYPE_NOTICE);
                }
                $isSuccessful = false;
            }
        }
        return $isSuccessful;
    }
}
