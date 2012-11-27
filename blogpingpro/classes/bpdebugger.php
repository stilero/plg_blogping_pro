<?php
/**
 * Description of BlogPingPro
 *
 * @version  1.0
 * @author Daniel Eliasson - joomla at stilero.com
 * @copyright  (C) 2012-sep-02 Stilero Webdesign http://www.stilero.com
 * @category Plugins
 * @license	GPLv2
 * 
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * 
 * This file is part of debugger.
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
