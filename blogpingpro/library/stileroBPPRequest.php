<?php
/**
 * Request Class
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

class StileroBPPRequest{
    
    /**
     * Request XML
     * @var string 
     */
    protected $_xml;
    /**
     * The raw request
     * @var string 
     */
    public $request;
    /**
     * Request headers
     * @var array 
     */
    public $headers;
    /**
     * The blog
     * @var StileroBPPBlog 
     */
    protected $_blog;
    /**
     * Request method. Use the constants of this class
     * @var string 
     */
    protected $_method;
    /**
     * Flag for using extended ping
     * @var boolean 
     */
    protected $_doExtendedPing;
    const REQUEST_METHOD = 'weblogUpdates.ping';
    const REQUEST_METHOD_EXTENDED = 'weblogUpdates.extendedPing';
    
    /**
     * 
     * @param StileroBPPBlog $blog
     * @param boolean $doExtendedPing
     */
    public function __construct(StileroBPPBlog $blog, $doExtendedPing = false) {
        $this->_blog = $blog;
        $this->_doExtendedPing = $doExtendedPing;
        if($this->_doExtendedPing){
            $this->_method = self::REQUEST_METHOD_EXTENDED;
        }else{
            $this->_method = self::REQUEST_METHOD;
        }
        $this->build();
    }
    
    /**
     * Returns the extended params
     * @return array
     */
    protected function getExtendedParams(){
        $params = array(
            'updateUrl' => $this->_blog->updateUrl,
            'rss' => $this->_blog->rss,
            'tags' => $this->_blog->tags
        );
        return $params;
    }

    /**
     * Builds the XML string and sets it in the xml-proterty
     */
    protected function _xml(){
        $params = array(
            'blogName' => $this->_blog->name,
            'blogURL' => $this->_blog->url
        );
        if ($this->_blog->hasExtendedInfo()){
            $params = array_merge(
                $params, $this->getExtendedParams()
            );
        }
        $xml = '';
        foreach ($params as $param) {
            $xml .= "<param><value><string>".htmlspecialchars($param)."</string></value></param>\n";
        }
        $this->_xml = $xml;
    }
    
    /**
     * Builds the headers and sets them to the headers array
     */
    protected function _header(){
        $contentLength  = strlen($$this->request);
        $headers = array(
            'Content-Type: text/xml',
            'Content-length: '.$contentLength,
        ); 
        $this->headers = $headers;
    }
    
    /**
     * Builds the request content
     */
    protected function _request(){
        $this->request =
<<<EOD
<?xml version="1.0"?>
<methodCall>
<methodName>{$this->_method}</methodName>
<params>
{$this->_xml}</params>
</methodCall>
EOD;
    }

    /**
     * Builds the request
     */
    public function build(){
        $this->_xml();
        $this->_request();
        $this->_header();
    }
}