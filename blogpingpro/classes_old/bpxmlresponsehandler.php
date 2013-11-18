<?php
/**
 * Class for handling XML responses
 *
 * @version  1.01
 * @author Daniel Eliasson - joomla at stilero.com
 * @copyright  (C) 2012-aug-31 Stilero Webdesign http://www.stilero.com
 * @category Plugins
 * @license	GPLv2
 * 
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class BPXMLResponseHandler{
    /**
     * XML-string
     * @var string 
     */
    private $xml;
    /**
     * Resource for XML Parser
     * @var resource 
     */
    private $_xmlParser;
    /**
     * Flag for setting catching or not
     * @var boolean 
     */
    private $_isCatching = false;
    /**
     * The name of the XML node
     * @var string 
     */
    private $_nodeName;
    /**
     * Array of responses
     * @var array 
     */
    private $_response;
    
    public function __construct($xmlString="") {
        $this->xml = $xmlString;
        if($xmlString != ""){
            $this->processResponse();
        }
    }
    
    public function processResponse(){
        $this->_cleanXMLString();
        if($this->_isEmpty()){
            return false;
        }
        $this->_handleXMLResponse();
    }
    
    /**
     * Cleans out start en end tags from XML
     */
    private function _cleanXMLString(){
        $partsOfXMLString = preg_split( '/<\?xml.*?\?'.'>/', $this->xml);
        $this->xml = isset($partsOfXMLString[1]) ? trim($partsOfXMLString[1]) : '';
    }
    /**
     * Checks if the XML is empty or not
     * @return boolean
     */
    private function _isEmpty(){
        if($this->xml == ''){
            return true;
        }
        return false;
    }
    /**
     * Clears any previous response
     */
    private function _resetResponse(){
        $this->_response = null;
    }
    /**
     * Handles an parser the XML Response
     * @return boolean false on fail
     */
    private function _handleXMLResponse() {
        $this->_xmlParser = xml_parser_create();
        xml_set_object($this->_xmlParser, $this);
        xml_set_element_handler($this->_xmlParser, "_xmlStartTag", "_xmlEndTag");
        xml_set_character_data_handler($this->_xmlParser, "_xmlContentBetweenTags");
        $final = false;
        $chunk_size = 262144;
        do{
            if(strlen($this->xml) <= $chunk_size) {
                $final = true;
            }
            $part = substr($this->xml, 0, $chunk_size);
            $this->xml = substr($this->xml, $chunk_size);
            if(!xml_parse($this->_xmlParser, $part, $final)) {
                return false;
            }
            if($final) {
                break;
            }
        } while (true);
        xml_parser_free($this->_xmlParser);
    }
    /**
     * Method called by _handleXMLResponse to identify start tags. When a start
     * tag is identified, the catching flag is set to true.
     * @param resource $parser XML Parser
     * @param string $data
     */
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
    /**
     * Method for catching end tags
     * @param resource $parser XML Parser
     * @param type $data
     */
    private function _xmlEndTag($parser, $data) {
        return;
    }
    
    /**
     * Catches the content between the start and end tags
     * @param resource $parser XML Parser
     * @param string $data XML data
     */
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
    
    /**
     * Method for setting XML string
     * @param string $xml
     */
    public function setXML($xml){
        $this->xml = $xml;
    }
    
    /**
     * Returns an array of responses
     * @return array Responses
     */
    public function getResponse() {
        return array($this->_response['flerror'], $this->_response['message']);
    }
    /**
     * Tests if a string is XML or not
     * @param string $stringToTest
     * @return boolean
     */
    public function isXML($stringToTest){
        $isXMLTagFound = preg_match('/<\?xml.*?\?'.'>/', $stringToTest);
        return $isXMLTagFound;        
    }
}