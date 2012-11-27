<?php
/**
 * Description of BlogPingPro
 *
 * @version  2.3
 * @version $Id$
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
 * This file is part of blogpingpro.
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

// import library dependencies
jimport('joomla.plugin.plugin');

class plgSystemBlogpingpro extends JPlugin {
    private $_config;
    private $_JArticle;
    private $_Logger;
    private $_Checker;
    private $_Message;
    private $_Pinger;
    private $_XMLResponseHandler;
    private $_Debugger;
    private $_articleClasses;
    private $_pingServers;
    private $_categoryIDs;
    private $_pluginPath;
    private $_isSuccessful;
    const HTTPCODE_NOT_FOUND = '404';
    const HTTPCODE_OK = '200';
    const HTTPCODE_COMMUNICATION_ERROR = '0';
    const PINGRESPONSE_NO_ERROR = '0';
    const PINGRESPONSE_ERROR = '1';

    function plgSystemBlogpingpro ( &$subject, $config ) {
        parent::__construct( $subject, $config );
        $language = JFactory::getLanguage();
        $language->load('plg_system_blogpingpro', JPATH_ADMINISTRATOR, 'en-GB', true);
        $language->load('plg_system_blogpingpro', JPATH_ADMINISTRATOR, null, true);
        $this->_config = array(
            'pluginName'        => 'blogpingpro',
            'pluginType'        => 'system',
            'logTableName'      => '#__blogpingpro_log',
            'langPrefix'        => "PLG_SYSTEM_BLOGPINGPRO_",
            'delay'             => $this->params->def('delay'),
            'timeout'           => $this->params->def('timeout'),
            'extendedPing'      => $this->params->def('extendedPing'),
            'allwaysPingOnSave' => $this->params->def('allwaysPingOnSave'),
            'rssurl'            => $this->params->def('rssurl')
        );
        $this->_pingServers = explode("\n", $this->params->def('pingServers'));
        $categories = $this->params->def('catID');
        if(is_array($categories) && !empty($categories)){
            $categories = implode(', ', $categories);
        }
        $this->_categoryIDs = $categories;
        $this->_articleClasses = array(
            'com_article' => 'BPArticle',
            'com_content' => 'BPArticle',
            'com_k2' =>  'BPk2Article',
            'com_zoo' =>  'BPzooArticle',
            'com_virtuemart' => 'BPvmArticle'
        );
    }
    
    public function onContentAfterSave($context, &$article, $isNew) {
        $this->_prepareToPing($article);
        $errors = $this->_Checker->getErrors();
        if(empty($errors)){
            $this->_preSaveLog();
            $this->_Pinger->ping();
            $this->_handlePingResponse();
            $this->_logPing();
            $this->_debug();
        }else{
            $messages = array();
            $this->_setIsSuccessful(FALSE);
            if(count($errors) > 1){
                foreach ($errors as $error) {
                    $messages[] = JText::_($error['message']);
                }
            }else{
                $messages = JText::_($errors[0]['message']);
            }
            $this->_Message->notice($messages);
            //$this->_logPing();
            $this->_debug();
        }
    }
    
    private function _prepareToPing($article){
        $this->_initClasses($article);
        $this->_doChecks();
        $this->_Pinger->setPingServerUrls($this->_pingServers);
        $articleObject = $this->_JArticle->getArticleObj();
        $blogName = $articleObject->title;
        $blogURL = JURI::root();
        $blogPostURL = $articleObject->url;
        $feedURL = $this->_getRssUrl();
        $tags = $articleObject->tags;
        $this->_Pinger->setBlog($blogName,$blogPostURL , $blogPostURL, $feedURL, $tags);
    }
    
    private function _preSaveLog(){
        $config =& JFactory::getConfig();
        $tzoffset = $config->getValue('config.offset');
        $date =& JFactory::getDate('', $tzoffset);
        $currentDate = $date->toMySQL(true);
        $articleObject = $this->_JArticle->getArticleObj();
        $fields = array(
            'article_id' => $articleObject->id,
            'cat_id' => $articleObject->catid,
            'articlelink' => $articleObject->url,
            'option' => JRequest::getVar('option'),
            'date' => $currentDate,
            'language' => $articleObject->language
        );
        $this->_Logger->save($fields);    
    }
    
    private function _handlePingResponse(){
        $results = $this->_Pinger->getResults();
        foreach ($results as $result) {
            if($result['http_code'] == self::HTTPCODE_OK && $this->_XMLResponseHandler->isXML($result['response'])){
                $this->_XMLResponseHandler->setXMLString($result['response']);
                $this->_XMLResponseHandler->processResponse();
                $response = $this->_XMLResponseHandler->getResponse();
                $response['server'] = $result['server'];
                $this->_processPingSuccess($response);
            }else{
                $this->_processPingError($result);
            }
        }
    }
    
    private function _processPingSuccess($response){
        $responseCode = $response[0];
        $server = $response['server'];
        $responseMessage = $server.': '.$response[1];
        switch ($responseCode) {
            case self::PINGRESPONSE_NO_ERROR:
                $this->_Message->info($responseMessage);
                $this->_setIsSuccessful(TRUE);
                break;
            case self::PINGRESPONSE_ERROR:
                $this->_Message->notice($responseMessage);
                //$this->_setIsSuccessful(FALSE);
                break;
            default:
                break;
        }
    }
    
    private function _processPingError($response){
        $message = '';
        $httpCode = $response['http_code'];
        switch ($httpCode) {
            case self::HTTPCODE_NOT_FOUND :
                $message = JText::sprintf('PLG_SYSTEM_BLOGPINGPRO_SERVER_NOTFOUND', $response['server']);
                break;
            case self::HTTPCODE_COMMUNICATION_ERROR :
                $message = JText::sprintf('PLG_SYSTEM_BLOGPINGPRO_SERVER_NORESPOND', $response['server']);
                break;
            case self::HTTPCODE_OK :
                $message = JText::sprintf('PLG_SYSTEM_BLOGPINGPRO_FOUND_SERVER_NORESPOND', $response['server']);
                break;
            default:
                $message = JText::sprintf('PLG_SYSTEM_BLOGPINGPRO_SERVER_ERROR_UNKNOWN', $response['server']);
                $message .= ' > ResponseCode='.$response['http_code'].', ResponseMessage'.$response['message'];
                break;
        }
        $this->_Message->warning($message);
        $this->_setIsSuccessful(FALSE);
    }
    
    private function _initClasses($article){
        $pluginFilesFolderPath = $this->_buildPluginPath();
        $classesFolderPath = $pluginFilesFolderPath.'classes'.DS;
        JLoader::register('BPLogger', $classesFolderPath.'bplogger.php');
        JLoader::register('BPArticle', $classesFolderPath.'BPArticle.php');
        JLoader::register('BPMessage', $classesFolderPath.'bpmessage.php');
        JLoader::register('BPArticleChecker', $classesFolderPath.'bparticlechecker.php');
        JLoader::register('BPCommunicator', $classesFolderPath.'bpcommunicator.php');
        JLoader::register('BPPinger', $classesFolderPath.'bppinger.php');
        JLoader::register('BPXMLResponseHandler', $classesFolderPath.'bpxmlresponsehandler.php');
        JLoader::register('BPDebugger', $classesFolderPath.'bpdebugger.php');
        $this->_Message = new BPMessage();
        $logTable = $this->_config['logTableName'];
        $sqlFilePath = $pluginFilesFolderPath.'sql'.DS.'install.sql';
        $this->_Logger = new BPLogger($logTable, $sqlFilePath);
        $this->_JArticle = new BPArticle($article);
        $articleObject = $this->_JArticle->getArticleObj();
        $config = array(
            'logTable' => $logTable,
            'categoriesToShare' => $this->_categoryIDs,
            'shareDelay' => $this->_config['delay'],
            'langPrefix' => $this->_config['langPrefix']
        );
        $this->_Checker = new BPArticleChecker($articleObject, $config);
        $this->_Pinger = new BPPinger();
        $this->_XMLResponseHandler = new BPXMLResponseHandler();
        $this->_Debugger = new BPDebugger();
    }
    
    private function _buildPluginPath(){
        $pluginName = $this->_config['pluginName'];
        $pluginType = $this->_config['pluginType'];
        $this->_pluginPath = JPATH_PLUGINS.DS.$pluginType.DS.$pluginName.DS.$pluginName.DS;
        $isLessThanJ16 = version_compare(JVERSION, '1.6.0', '<');
        if($isLessThanJ16){
            $this->_pluginPath = JPATH_PLUGINS.DS.$pluginType.DS.$pluginName.DS;
        }
        return $this->_pluginPath;
    }
    
    private function _doChecks(){
        //$this->CheckClass->isServerSupportingRequiredFunctions();
        //$this->CheckClass->isPingServersEntered();
        $this->_Checker->isArticleObjectIncluded();
        $article = $this->_JArticle->getArticleObj();
        $articleID = $article->id;
        if($this->_Logger->isArticleIdFoundInLog($articleID) && $this->params->def('allwaysPingOnSave') != 1){
            $this->_Checker->setError('PLG_SYSTEM_BLOGPINGPRO_ALREADY_POSTED');
        }
        $this->_Checker->isItemActive();
        $this->_Checker->isItemPublished();
        $this->_Checker->isItemPublic();
        $this->_Checker->isCategoryToShare();
        if(!$this->_Logger->isLogTableExisting()){
            if(!$this->_Logger->createLogTable()){
                $this->_Checker->setError(JText::_('PLG_SYSTEM_BLOGPINGPRO_LOGCREATE_FAILURE'), 'error');
            }
        }
        $this->_Checker->isSharingToEarly(); 
    }
    
    private function _getRssUrl(){
        $rssQuery = "?format=feed&type=rss";
        $rssURL = JURI::root()."index.php".$rssQuery;
        return $rssURL;
    }
    
    private function _debug(){
        if(JDEBUG){
            $debugInfoArray = $this->_Debugger->getDebugInfo();
            $debugInfo = implode('</li><li>', $debugInfoArray);
            $debugMessage = '';
            foreach ($debugInfoArray as $$debugInfo) {
                if(is_array($debugInfo)){
                    $debugInfo .= implode('</li><li>', $debugInfo);
                }
                $debugMessage .= $debugInfo;
            }
            $this->_Message->notice($debugMessage);
        }
    }
        
    private function _logPing(){
        if($this->_isSuccessful === TRUE){
            return;
        }else{
            $field = array(
                'article_id' => $this->_JArticle->articleObj->id,
                'option' => JRequest::getVar('option'),
                'language' => $this->_JArticle->articleObj->language
            );
            $this->_Logger->delete($field);
        }
    }
    
    private function _setIsSuccessful($isSuccessful){
        if($this->_isSuccessful === TRUE){
            return;
        }
        $this->_isSuccessful = $isSuccessful;
    }

} //End Class