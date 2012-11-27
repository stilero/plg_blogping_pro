<?php
/**
 * Pinger sends ping requests to update ping servers
 *
 * @version  1.0
 * @author Daniel Eliasson - joomla at stilero.com
 * @copyright  (C) 2012-aug-31 Stilero Webdesign http://www.stilero.com
 * @category Classes
 * @license	GPLv2
 * 
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * 
 * This file is part of pinger.
 * 
 * pinger is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * pinger is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with pinger.  If not, see <http://www.gnu.org/licenses/>.
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class BPPinger extends BPCommunicator{
    protected $_blog = array();
    protected $_pingServerUrls = array();
    protected $_pingServerUrl;
    protected $_pingMethod;
    protected $_request;
    protected $_requestCache = array();
    protected $_requestXML;
    protected $_requestHeader;
    protected $_doExtendedPing;
    protected $_results = array();
    protected $_errors = array();
    const ERROR_MISSING_BLOG  = '100';
    const ERROR_MISSING_PINGSERVERS = '200';
    const ERROR_INVALID_SERVERURL = '300';
    
    public function __construct($url="", $postArray = "", $config = "") {
        $config = array(
            'curlUserAgent' =>  'Pinger - www.stilero.com',
            'curlConnectTimeout' =>  10,
            'curlTimeout' =>  10,
            'curlUseCookies' =>  false,
        );
        parent::__construct($url, $postArray, $config);
        $defaultConfig = array(
            'useExtendedPing' => false,
            'endOfLine' => '\r\n',
        );
        if(is_array($config)) {
            $defaultConfig = array_merge($defaultConfig, $config);
        }
        $this->_config = array_merge($this->_config, $defaultConfig);
        $this->_doExtendedPing = $this->_config['useExtendedPing'];
    }
    
    public function ping() {
        if(!$this->_isReadyToPing()){
            return false;
        }
        foreach ($this->_pingServerUrls as $pingServerUrl) {
            $this->_activateNormalPing();
            if( $this->_doExtendedPing && $this->isExtendedPingInfoSpecified() ){
                $this->_activateExtendedPing();
            }
            $this->_pingServerUrl = $pingServerUrl;
            $this->setUrl($pingServerUrl);
            $this->_buildRequest();
            $this->setPostVars($this->_request);
            $this->query();
            $this->_handleResponse();
        }
    }
    
    private function _buildRequest(){
        $requestMethod = $this->_pingMethod;
        if(!isset($this->_requestCache[$requestMethod]) || $this->_requestCache[$requestMethod] == ""){
            $this->_buildRequestXML();
            $this->_requestCache[$requestMethod] = 
<<<EOD
<?xml version="1.0"?>
<methodCall>
<methodName>{$requestMethod}</methodName>
<params>
{$this->_requestXML}</params>
</methodCall>
EOD;
        } 
        $this->_request = $this->_requestCache[$requestMethod];
        $this->_buildHTTPHeader();
    }
    
    private function _buildRequestXML(){
        $params = array(
            'blogName' => $this->_blog['name'],
            'blogURL' => $this->_blog['url']
        );
       if($this->_doExtendedPing && $this->_isExtendedPingInfoSpecified()) {
            $params = array_merge(
                $params, 
                array(
                    'updateUrl' => $this->_blog['postURL'],
                    'rss' => $this->_blog['rss'],
                    'tags' => $this->_blog['tags']              
                )
            );
        }
        $xmlString = '';
        foreach ($params as $param) {
            $xmlString .= "<param><value><string>".htmlspecialchars($param)."</string></value></param>\n";
        }
        $this->_requestXML = $xmlString;
    } 
    
    protected function _buildHTTPHeader(){
        $contentLength  = strlen($this->_requestCache[$this->_pingMethod]);
        $header = array(
            //'Accept' => 'text/xml,application/xml,application/xhtml+xml,'.
            //    'text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5',
            //'Cache-Control' => 'max-age=0',
            //'Connection' => 'keep-alive',
            //'Keep-Alive' => '300',
            //'Accept-Charset' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            //'Accept-Language' => 'en-us,en;q=0.5',
            'Content-Type: text/xml',
            'Content-length: '.$contentLength,
            //'Pragma'
        ); 
        $this->setHeader($header);
    }

    protected function _isReadyToPing(){       
        if(empty($this->_blog)) {
            $this->_errors[] = array(self::ERROR_MISSING_BLOG, 'Missing Blog');
            return false;
        }
        if(empty($this->_pingServerUrls)){
            $this->_errors[] = array(self::ERROR_MISSING_PINGSERVERS, 'Missing Pingservers');
            return false;
        }
        return true;
    }
    
    private function _activateExtendedPing(){
        $this->_pingMethod = "weblogUpdates.extendedPing";
    }
    
    private function _activateNormalPing(){
        $this->_pingMethod = "weblogUpdates.ping";
    }
    
    private function _isExtendedPingInfoSpecified(){
        if( $this->_blog['rss']=="" || $this->_blog['postURL']=="" || $this->_blog['tags']=="" ) {
            return false;
        }
        return true;
    }
    
    protected function _handleResponse(){
        $this->_results[] = array(
            'http_code' => $this->getInfoHTTPCode(),
            'response' => $this->getResponse(),
            'server' => $this->getUrl()
        );
    }
    
    public function setBlog($blogName, $blogURL, $blogPostURL, $feedURL="", $tags="") {
        $this->_blog['name'] = $blogName;
        $this->_blog['url'] = $blogURL;
        $this->_blog['postURL'] = $blogPostURL;
        $this->_blog['rss'] = $feedURL;
        $this->_blog['tags'] = $tags;
    }
    
    public function setPingServerUrls($pingServerUrls){
        $pingServerUrls = array_map('trim', $pingServerUrls);
        $this->_pingServerUrls = $pingServerUrls;
    }
    
    public function _getPingMethod(){
        return $this->_pingMethod;
    }
    
    public function getResults(){
        return $this->_results;
    }
    
}