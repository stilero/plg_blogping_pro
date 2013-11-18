<?php
/**
 * This class checks an article to see if it's ready to be shared.
 *
 * @version  1.0
 * @author Daniel Eliasson - joomla at stilero.com
 * @copyright  (C) 2012-sep-01 Stilero Webdesign http://www.stilero.com
 * @category Plugins
 * @license	GPLv2
 * 
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * 
 * This file is part of checker.
 * 
 * BlogPingPro is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * BlogPingPro is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with BlogPingPro.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class BPArticleChecker{
    private $_jArticleObj;
    private $_config;
    private $_errors = array();
    
    public function __construct($article, $config) {
        $defaultConfig = array(
            'categoriesToShare' => '',
            'shareDelay' => '',
            'langPrefix' => 'PLG_SYSTEM_MYPLUGIN_'
        );
        if(isset($config) && !empty($config)){
            $defaultConfig = array_merge($defaultConfig, $config);
        }
        $this->_config = $defaultConfig;
        $this->_jArticleObj = $article;
    }

    public function isArticleObjectIncluded() {
        return $this->_assertNotEmpty($this->_jArticleObj->id, 'NOT_OBJECT', 'error');
    }
    
    public function isItemActive(){
        return $this->_assertEqual($this->_jArticleObj->isPublished, 1, 'NOTACTIVE', 'error');
    }
    
    public function isItemPublished() {
        $date = JFactory::getDate();
        $currentDate = $date->toSql();
        $itemPublishDate = $this->_jArticleObj->publish_up;
        return $this->_assertBiggerThan(strtotime($itemPublishDate), strtotime($currentDate), 'NOTACTIVE');
    }
    
    public function isItemNewEnough() {
        $postItemsNewerThanDate = $this->_config['articlesNewerThan'];
        $itemPublishDate = $this->_jArticleObj->publish_up;
        if($this->_config['articlesNewerThan'] != ''){
            return $this->_assertBiggerThan($postItemsNewerThanDate, $itemPublishDate, 'ITEM_OLD');
        }
        return true;
    }
    
    public function isItemPublic() {
        $publicAccessCode = ($this->isJoomla15())?0:1;
        $articleAccess = $this->_jArticleObj->isPublic;
        return $this->_assertEqual($articleAccess, $publicAccessCode, 'RESTRICT');
    }
    
    public function isCategoryToShare() {
        if(!empty($this->_errors)){
            return;
        }
        $categoriesToShare = $this->_config['categoriesToShare'];
        if($categoriesToShare == "" ){
            return TRUE;
        }
        $categories = explode(",", $categoriesToShare);
        $itemCategID = $this->_jArticleObj->catid;
        if ( !in_array( $itemCategID, $categories ) ){
            $this->setError($this->_config['langPrefix'].'NOTSECTION');
            return FALSE;
        }
    }
    
    public function isSharingToEarly(){
        if(!empty($this->_errors)){
            return;
        }
        $shareDelay = $this->_config['shareDelay'];
        $delayInMinutes = (!is_numeric($shareDelay) || $shareDelay < 0 )? 0 : $shareDelay;
        $app = JFactory::getApplication();
        $tzoffset = $app->getCfg('config.offset');
        //$config = JFactory::getConfig();
        //$tzoffset = $config->getValue('config.offset');
        $date =& JFactory::getDate('', $tzoffset);
        $currentDate = $date->toSql(true);
        $tableName = $this->_config['logTable'];
        $db = &JFactory::getDbo();
        $query = "SELECT ".$db->qn('id').
            " FROM ".$db->qn( $tableName ).
            " WHERE date > SUBTIME('".$currentDate."','0 0:".$delayInMinutes.":0.0')";
        $db->setQuery($query);
        $isPostedDuringDelayTime = $db->loadObject();
        if($isPostedDuringDelayTime){
            $this->setError($this->_config['langPrefix'].'DELAYED');
            return TRUE;
        }
        return FALSE;
    }
    
    public function isJoomla15() {
        if( version_compare(JVERSION,'1.5.0','ge') && version_compare(JVERSION,'1.6.0','lt') ) {
            return TRUE;
        }
        return FALSE;
    }

    public function isJoomla16() {
        if( version_compare(JVERSION,'1.6.0','ge') && version_compare(JVERSION,'1.7.0','lt') ) {
            return TRUE;
        }
        return FALSE;
    }

    public function isJoomla17() {
        if(version_compare(JVERSION,'1.7.0','ge')) {
            return TRUE;
        }
        return FALSE;
    }

    
    private function _assertEqual($actual, $expected, $errorSuffix, $errorType=""){
        if(!empty($this->_errors)){
            return;
        }
        if($actual != $expected){
            $this->setError($this->_config['langPrefix'].$errorSuffix, $errorType="");
            return false;
        }
        return true;
    }
    
    private function _assertNotEmpty($actual, $errorSuffix, $errorType){
        if(!empty($this->_errors)){
            return;
        }
        if(!$actual){
            $this->setError($this->_config['langPrefix'].$errorSuffix, $errorType);
            return false;
        }
        return true;
    }
    
    private function _assertBiggerThan($actual, $expected, $errorSuffix, $errorType=""){
        if(!empty($this->_errors)){
            return;
        }
        
        if($actual > $expected){
            $this->setError($this->_config['langPrefix'].$errorSuffix, $errorType="");
            return false;
        }
        return true;
    }
    
    public function setError($message, $type=''){
        $error = array(
            'message' => $message,
            'type' => $type
        );
        $this->_errors[] = $error;
    }
    
    public function getErrors(){
        return $this->_errors;
    }
}