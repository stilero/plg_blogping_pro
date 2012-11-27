<?php
/**
* Description of BlogPingPro
*
* @version  1.0
* @author Daniel Eliasson - joomla at stilero.com
* @copyright  (C) 2012-sep-01 Stilero Webdesign http://www.stilero.com
* @category Custom Form field
* @license    GPLv2
*
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* This file is part of tablecheck.
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

$loggerfile = JPATH_PLUGINS.DS.'system'.DS.'blogpingpro'.DS.'classes'.DS.'logger.php';
JLoader::register('Logger', $loggerfile);

if(version_compare(JVERSION, '1.6.0', '<')){
    /**
    * @since J1.5
    */
    class JElementTablecheck extends JElement{

        function fetchElement($name, $value, &$node, $control_name){
            $sqlScriptFilePath = JPATH_PLUGINS.DS.'system'.DS.'blogpingpro'.DS.'sql'.DS.'install.sql';
            $logger = new Logger('#__blogpingpro_log', $sqlScriptFilePath, array('isDebugging' => true));
            if(!$logger->isLogTableExisting()){
                if($logger->createLogTable()){
                    JError::raiseNotice(0, JText::_('PLG_SYSTEM_BLOGPINGPRO_LOGCREATE_SUCCESS'));
                }else{
                    JFactory::getApplication()->enqueueMessage( JText::_('PLG_SYSTEM_BLOGPINGPRO_LOGCREATE_FAILURE'), 'error');
                }
            }
                JFactory::getApplication()->enqueueMessage(implode('</li><li>',$logger->getDebugInfo()));
            $htmlCode = '<input type="hidden" id="'.$control_name.$name.'" name="'.$control_name.'['.$name.']'.'" value="'.$value .'"/>';
            return $htmlCode;
        }
        function fetchTooltip ( $label, $description, &$xmlElement, $control_name='', $name=''){
            return;        
        }
    }//End Class J1.5
}