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
 * This file is part of tablecreator.
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

class BPLogger{
    private $_config;
    private $_sqlScript;
    private $_tableName;
    private $_debugInfo = array();
    
    /**
     * Logger handles the log (of course)
     * @param String $tableName Table name in Joomla style '#__tablename'
     * @param String $filePathToSqlScript Absolute path to script
     */
    public function __construct($tableName, $filePathToSqlScript="", $config="") {
        $defaultConfig = array(
            'sqlInstallFilePath' => $filePathToSqlScript,
            'isDebugging' => FALSE
        );
        if(isset($config) && !empty($config)){
            $defaultConfig = array_merge($defaultConfig, $config);
        }
        $this->_config = $defaultConfig;
        $this->_tableName = $tableName;
    }
    
    /**
     * Checks if the log tables exist and returns true on success.
     * @return boolean 
     */
    public function isLogTableExisting(){
        $db =& JFactory::getDbo();
        $table = $db->nameQuote($this->_tableName);
        $query = 'DESC '.$table;
        $db->setQuery($query);
        $isTableFound = $db->query();
        if($this->_config['isDebugging']){
            $this->_debugInfo[] = 'TableExisting Query: '.$query;
        }
        return $isTableFound;    
    }
    
    /**
     * Creates tables according to sql script file
     * @return boolean 
     */
    public function createLogTable(){ 
        $sqlFile = $this->_config['sqlInstallFilePath'];
        if($sqlFile == ''){
            if($this->_config['isDebugging']){
                $this->_debugInfo[] = 'CreateTable Faild due to missing sql file.';
            }
            return false;
        }
        $this->_sqlScript = file_get_contents($sqlFile);
        $dbObject =& JFactory::getDbo();
        $dbObject->setQuery($this->_sqlScript);
        $queryResult = $dbObject->query();
        if($this->_config['isDebugging']){
            $this->_debugInfo[] = 'CreateTable Query: '.$this->_sqlScript;
        }
        return $queryResult;
    }
    
    /**
     * Saves log to db. 
     * Input format: array('fieldName' => 'fieldValue')
     * @param Array $fields 
     */
    public function save($fields){
        $db =& JFactory::getDbo();
        $preparedFields = $this->_prepareQuery($fields);
        $tableName = $db->nameQuote($this->_tableName);
        $query = 'INSERT INTO '.$tableName.' (';
        $query .= implode(', ', $preparedFields['keys']);
        $query .= ') VALUES (';
        $query .= implode(', ', $preparedFields['values']);
        $query .= ');';
        $db->setQuery($query);
        $queryResult = $db->query();
        if($this->_config['isDebugging']){
            $this->_debugInfo[] = 'Save Query: '.$query;
        }
        return $queryResult;
    }
    
    /**
     * Delete row from Log
     * @param Array $fields (array('key' => 'fieldKey', 'value' => 'fieldValue'))
     * @return boolean 
     */
    public function delete($fields){
        $db =& JFactory::getDbo();
        $preparedFields = $this->_prepareQuery($fields);
        $tableName = $db->nameQuote($this->_tableName);
        $fields = array();
        $query = 'DELETE FROM '.$tableName.' WHERE ';
        for($i=0;$i<count($preparedFields['keys']);$i++){
            $fields[] = $preparedFields['keys'][$i].' = '.$preparedFields['values'][$i];
        }
        $query .= implode(' AND ', $fields).';';
        $db->setQuery($query);
        $queryResult = $db->query();
        if($this->_config['isDebugging']){
            $this->_debugInfo[] = 'Delete Query: '.$query;
        }
        return $queryResult;
    }
    
    private function _prepareQuery($fields){
        $db =& JFactory::getDbo();
        $keys = array_map(array($db, 'nameQuote'), array_keys($fields));
        $values = array_map(array($db, 'quote'), array_values($fields));
        $preparedFields = array('keys' => $keys, 'values' => $values);
        return $preparedFields;
    }
    
    public function isArticleIdFoundInLog($articleId){
        $db = &JFactory::getDbo();
        $query = 'SELECT '.$db->nameQuote('id').
            ' FROM '.$db->nameQuote( $this->_tableName ).
            ' WHERE '.$db->nameQuote('article_id').'='.$db->Quote($articleId);
        $db->setQuery($query);
        $result = $db->loadObject();
        if($result){
            return TRUE;
        }
        return FALSE;
    }
    
    public function getDebugInfo(){
        return $this->_debugInfo;
    }
    
    public function getTableName(){
        return $this->_tableName;
    }
}