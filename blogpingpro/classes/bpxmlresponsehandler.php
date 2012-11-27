<?php
/**
 * Description of PingClass
 *
 * @version  1.0
 * @author Daniel Eliasson - joomla at stilero.com
 * @copyright  (C) 2012-aug-31 Stilero Webdesign http://www.stilero.com
 * @category Plugins
 * @license	GPLv2
 * 
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * 
 * This file is part of xmlparser.
 * 
 * PingClass is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * PingClass is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with PingClass.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class BPXMLResponseHandler{
    
    private $_xmlString;
    private $_xmlParser;
    private $_isCatching = false;
    private $_nodeName;
    private $_response;
    
    public function __construct($xmlString="") {
        $this->_xmlString = $xmlString;
        if($xmlString != ""){
            $this->processResponse();
        }
    }
    
    public function processResponse(){
        $this->_cleanXMLString();
        if(!$this->_isReadyToProcess()){
            return false;
        }
        $this->_handleXMLResponse();
    }
    
    private function _cleanXMLString(){
        $partsOfXMLString = preg_split( '/<\?xml.*?\?'.'>/', $this->_xmlString);
        $this->_xmlString = isset($partsOfXMLString[1]) ? trim($partsOfXMLString[1]) : '';
    }
    
    private function _isReadyToProcess(){
        if($this->_xmlString == ''){
            return false;
        }
        return true;
    }
    
    private function _resetResponse(){
        $this->_response = null;
    }
    
    private function _handleXMLResponse() {
        $this->_xmlParser = xml_parser_create();
        xml_set_object($this->_xmlParser, $this);
        xml_set_element_handler($this->_xmlParser, "_xmlStartTag", "_xmlEndTag");
        xml_set_character_data_handler($this->_xmlParser, "_xmlContentBetweenTags");
        $final = false;
        $chunk_size = 262144;
        do{
            if(strlen($this->_xmlString) <= $chunk_size) {
                $final = true;
            }
            $part = substr($this->_xmlString, 0, $chunk_size);
            $this->_xmlString = substr($this->_xmlString, $chunk_size);
            if(!xml_parse($this->_xmlParser, $part, $final)) {
                return false;
            }
            if($final) {
                break;
            }
        } while (true);
        xml_parser_free($this->_xmlParser);
    }
    
    private function _xmlStartTag($parser, $data){
        $tagName = strtolower($data);
        switch ($tagName) {
            case 'name':
                $this->_isCatching = true;
                break;
            case 'boolean':
                $this->_isCatching = true;
                break;
            case 'int':
                $this->_isCatching = true;
                break;
            case 'string':
                $this->_isCatching = true;
                break;
            case 'value':
                $this->_isCatching = ($this->_nodeName != "") ? true:false;
                break;
            default:
                break;
        }
    }
    private function _xmlEndTag($parser, $data) {
        return;
    }
    private function _xmlContentBetweenTags($parser, $data){
        if( ! $this->_isCatching ) {
            return;
        }
        switch (strtolower($data)) {
            case "flerror":
                $this->_nodeName = 'flerror';
                $this->_isCatching = false;
                break;
            case "faultcode":
                $this->_nodeName = 'flerror';
                $this->_isCatching = false;
                break;
            case "message":
                $this->_nodeName = 'message';
                $this->_isCatching = false;
                break;
            case "faultstring":
                $this->_nodeName = 'message';
                 $this->_isCatching = false;
                break;
            default:
                break;
        }
        if( $this->_isCatching && $this->_nodeName != "" ) {
            $this->_response[$this->_nodeName] = $data;
            $this->_isCatching = false;
        }
    }
    
    public function setXMLString($xmlString){
        $this->_xmlString = $xmlString;
    }
    
    public function getResponse() {
        return array($this->_response['flerror'], $this->_response['message']);
    }
    
    public function isXML($stringToTest){
        $isXMLTagFound = preg_match('/<\?xml.*?\?'.'>/', $stringToTest);
        return $isXMLTagFound;        
    }
}