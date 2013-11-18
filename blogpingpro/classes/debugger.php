<?php
/**
 * Description of BlogPingPro
 *
 * @version  1.0
 * @author Daniel Eliasson - joomla at stilero.com
 * @copyright  (C) 2012-sep-02 Stilero Webdesign http://www.stilero.com
 * @category Plugins
 * @license	GPLv2
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class BPDebugger{
    private $_config;
    private $_debugInfo = array();
    
    public function __construct($config="") {
        $defaultConfig = array(
            'isDebugging' => TRUE
        );
        if(isset($config) && !empty($config)){
            $defaultConfig = array_merge($defaultConfig, $config);
        }
        $this->_config = $defaultConfig;
    }
    
    public function addDebugInfo($message=""){
        if($message != ''){
            $this->_debugInfo[] = $this->_processMessage($message);
        }else{
            $this->addBackTrace();
        }
    }
    
    public function addBackTrace(){
        $this->_debugInfo[] = debug_backtrace();
    }
    
    public function getDebugInfo(){
        return $this->_debugInfo;
    }
    
    private function _processMessage($message){
        if(is_array($message)){
            $message = implode('</li><li>', $message);
        }
        return $message;
    }
}
