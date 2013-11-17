<?php
/**
 * Pinger Class
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

class StileroBPPPinger extends StileroBPPCurler{
    
    /**
     * Array of stileroBBServer
     * @var array 
     */
    protected $_servers;
    
    /**
     * Blog Class
     * @var StileroBPPBlog 
     */
    protected $_blog;
    protected $_doExtendedPing = false;
    protected $_results;


    public function __construct(StileroBPPBlog $blog, $servers, $doExtendedPing = false, $url = "", $postVars = "", $config = "") {
        parent::__construct($url, $postVars, $config);
        $this->_blog = $blog;
        $this->_servers = $servers;
        $this->_doExtendedPing = $doExtendedPing;
        $this->setRequest();
    }
    
    protected function setRequest(){
        $doExtended = false;
        if($this->_blog->hasExtendedInfo() && $this->_doExtendedPing){
            $doExtended = true;
        }
        $request = new StileroBPPRequest($this->_blog, $doExtended);
        $this->setPostVars($request->request);
        $this->setHeader($request->headers);
    }
    
    /**
     * Handles the response from the request
     */
    protected function _handleResponse(){
        $this->_results[] = array(
            'http_code' => $this->getInfoHTTPCode(),
            'response' => $this->getResponse(),
            'server' => $this->getUrl()
        );
    }
    
    /** 
     * Sends the ping
     */
    public function ping(){
        foreach ($this->_servers as $server) {
            $this->setUrl($server->url);
            $this->query();
        }
        
        $this->_handleResponse();
    }
    
    /**
     * Returns the results from the ping
     * @return array
     */
    public function getResults(){
        return $this->_results;
    }
}
