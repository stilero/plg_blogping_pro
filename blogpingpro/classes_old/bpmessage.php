<?php
/**
 * Description of BlogPingPro
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
 * This file is part of message.
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

class BPMessage {
    private $_isActive = true;
    private $_message;
    
    public function __construct($isActive = '', $config="") {
        $defaultConfig = array(
            'isDebugging' => FALSE
        );
        if(isset($config) && !empty($config)){
            $defaultConfig = array_merge($defaultConfig, $config);
        }
        $this->_config = $defaultConfig;
        $this->_isActive = $isActive;
    }
    
    public function info($message){
        $this->setMessage($message);
        JFactory::getApplication()->enqueueMessage( $message);
    }
    
    public function warning($message){
        $this->setMessage($message);
        JError::raiseWarning( '100', $message );
    }
    
    public function notice($message){
        $this->setMessage($message);
        JError::raiseNotice('0', $message );
    }
    
    public function error($message){
        $this->setMessage($message);
        JFactory::getApplication()->enqueueMessage( $message, 'error');
    }
    
    public function setMessage($message){
        if(is_array($message)){
            if(count($message) > 1){
                $messages = array();
                foreach ($message as $messageParts) {
                    $messages[] = $messageParts['message'];
                }
                $message = implode('</li><li>', $messages);
            }else{
                $message = $message[0]['message'];
            }
        }
        $this->_message = $message;
    }
}