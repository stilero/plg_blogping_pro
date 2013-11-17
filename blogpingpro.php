<?php
/**
 * plg_blogping_pro
 *
 * @version  1.0
 * @package Stilero
 * @subpackage plg_blogping_pro
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-nov-17 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 
if(!defined('DS')){
    define('DS',DIRECTORY_SEPARATOR);
}
jimport('joomla.event.plugin');
JLoader::discover('StileroBPP', dirname(__FILE__).DS.'blogpingpro'.DS.'library'.DS);
JLoader::discover('StileroBPP', dirname(__FILE__).DS.'blogpingpro'.DS.'helpers'.DS);
class plgSystemBlogpingpro extends JPlugin{
    /**
     * Article object
     * @var StileroBPPJarticle 
     */
    protected $_Article;
    protected $_ShareCheck;
    protected $_Table;
    protected $_Blog;
    protected $_delay;
    protected $_categoryList;
    protected $_doExtendedPing;
    protected $_isBackend;
    protected $_isK2 = false;
    protected $_servers = array();
    const TABLE_NAME = '#__blogpingpro_log';
    const LANG_PREFIX = 'PLG_SYSTEM_BLOGPINGPRO_';
    const EXTENSION = 'plg_system_blogpingpro';
    
    public function plgSystemBlogpingpro( &$subject, $config ) {
        parent::__construct($subject, $config);
        $language = JFactory::getLanguage();
        $language->load(self::EXTENSION, JPATH_ADMINISTRATOR, 'en-GB', true);
        $language->load(self::EXTENSION, JPATH_ADMINISTRATOR, null, true);
        $this->_delay = $this->params->def('delay');
        $this->_categoryList = $this->params->def('catID');
        $this->_doExtendedPing = $this->params->def('extendedPing');
        $this->setServers();
    }
    
    /**
     * Iterates the list of servers and creates server objects in an array
     */
    protected function setServers(){
        $urls = explode("\n", $this->params->def('pingServers'));
        foreach ($urls as $url) {
            if(isset($url)){
                $server = new StileroBPPServer($url);
                $this->_servers[] = $server;
            }
        }
    }
    
    /**
     * Initializes all oauth classes for the plugin
     */
    protected function _initializeClasses(){
        //$this->_Table = new StileroBPPShareTable(self::TABLE_NAME);
    }
    
    /**
     * Initializes all before posting
     * @param boolean $inBackend True if posted from backend
     * @param Object $article Joomla article Object
     */
    protected function _initializePosting($article){
        $this->_Table = new StileroBPPSharetable(self::TABLE_NAME);
        if(!$this->_isK2){
            $this->_Article = new StileroBPPJarticle($article);
        }else{
            $this->_Article = new StileroBPPK2Article($article);
        }
        $this->_ShareCheck = new StileroBPPSharecheck($this->_Article->getArticleObj(), $this->_Table, $this->_delay, '', $this->_categoryList, TRUE, $this->_isBackend);
        $app = JFactory::getApplication();
        $blogName = $app->getCfg('sitename');
        $url = JUri::root();
        $Article = $this->_Article->getArticleObj();
        $itemUrl = $Article->url;
        $rss = JURI::root()."index.php". "?format=feed&type=rss";
        $tags = $Article->metakey;
        $this->_Blog = new StileroBPPBlog($blogName, $url, $itemUrl, $rss, $tags);
    }
    
    /**
     * Displays a Joomla message in backend.
     * @param string $message The message to display
     * @param string $type The type of message
     */
    protected function _showMessage($message, $type='message'){
        if($this->_isBackend){
            StileroBPPMessageHelper::show($message, $type);
        }
    }
    
    /**
     * Prepares and sends a ping. Displays messages after pinging.
     * @param Object $article Joomla article Object
     */
    protected function _sendTweet($article){
        $this->_initializePosting($article);
        $Article = $this->_Article->getArticleObj();
        $hasChecksPassed = $this->_ShareCheck->hasFullChecksPassed();
        $isInLog = $this->_Table->isLogged($Article->id, $Article->component);
        if(!$isInLog){
            if($hasChecksPassed && !$isInLog){
                $Pinger = new StileroBPPPinger($this->_Blog, $this->_servers);
                $Pinger->ping();
                $response = $Pinger->getResponse();
                var_dump($response);exit;
                
                $status = StileroTTTweetHelper::buildTweet($Article, 5, $this->_defaultTag, $this->_useMetaAsHash);
                $response = $this->_Tweet->update($status);
                $TwitterResponse = new StileroTTTwitterResponse($response);
                if($TwitterResponse->hasID()){
                    $message = JText::_(self::LANG_PREFIX.'SUCCESS').$status;
                    $this->_showMessage($message);
                    $this->_Table->saveLog($Article->id, $Article->catid, $Article->url, $Article->lang, $Article->component);
                }else if($TwitterResponse->hasError()){
                    $message = JText::_(self::LANG_PREFIX.'ERROR').'('.$TwitterResponse->errorCode.') '.$TwitterResponse->errorMsg;
                    $this->_showMessage($message, StileroTTMessageHelper::TYPE_ERROR);
                }else{
                    $message = JText::_(self::LANG_PREFIX.'UNKNOWN_ERROR');
                    $this->_showMessage($message, StileroTTMessageHelper::TYPE_ERROR);
                }
            }else{
                $message = JText::_(self::LANG_PREFIX.'FAILED_CHECKS');
                //$this->_showMessage($message, StileroTTMessageHelper::TYPE_ERROR);
            }
        }else {
            $message = JText::_(self::LANG_PREFIX.'DUPLICATE_TWEET');
            $this->_showMessage($message, StileroTTMessageHelper::TYPE_NOTICE);
        }
    }
    
    /**
     * Method called after saving an article
     * @param string $context
     * @param Object $article
     * @param boolean $isNew
     */
    public function onContentAfterSave($context, $article, $isNew) {
        $this->_isBackend = true;
        if($context == StileroBPPContextHelper::K2_ITEM){
            $this->_isK2 = TRUE;
        }
        if(StileroBPPContextHelper::isArticle($context)){
            $this->_sendTweet($article);
        }
        //return;
    }
}
