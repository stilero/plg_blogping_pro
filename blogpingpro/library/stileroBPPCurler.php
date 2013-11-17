<?php
/**
 * Curler handles server communication usin cURL
 *
 * @version  1.3
 * @author Daniel Eliasson - joomla at stilero.com
 * @copyright  (C) 2012-aug-31 Stilero Webdesign http://www.stilero.com
 * @category Classes
 * @license	GPLv2
 */

class StileroBPPCurler {
    
    protected $_config;
    protected $header;
    protected $_isPost;
    protected $_curlHandler;
    protected $url;
    protected $postVars;
    protected $_response;
    protected $_responseInfoParts;
    protected $_cookieFile;
    protected $_isCustomRequest = false;
    protected $_customRequestType;
    const REQUEST_METHOD_POST = 'POST';
    const REQUEST_METHOD_GET = 'GET';
    const HTTP_STATUS_OK = '200';

    function __construct($url="", $postVars="", $config="") {
        $this->_isPost = false;
        $this->url = $url;
        if(!empty($postVars)){
            $this->_isPost = true;
            $this->postVars = $postVars;
        }
        
        $this->_config = 
            array(
                'curlUserAgent'         =>  'Communicator - www.stilero.com',
                'curlConnectTimeout'    =>  10,
                'curlTimeout'           =>  10,
                'curlReturnTransf'      =>  true, //return the handle as a string
                'curlSSLVerifyPeer'     =>  false,
                'curlFollowLocation'    =>  false,
                'curlProxy'             =>  false,
                'curlProxyPassword'     =>  false,
                'curlEncoding'          =>  false,
                'curlHeader'            =>  false, //Include the header in the output
                'curlHeaderOut'         =>  true,
                'curlUseCookies'        =>  false,
                'debug'                 =>  false,
                'eol'                   =>  "<br /><br />"
            );
        if(is_array($config)) {
            $this->_config = array_merge($this->_config, $config);
        }
    }
    
    public function query(){
        $this->resetResponse();
        $this->_curlHandler = curl_init(); 
        $this->_setupCurl();
        $this->_response = curl_exec ($this->_curlHandler);
        $this->_responseInfoParts = curl_getinfo($this->_curlHandler); 
        curl_close ($this->_curlHandler);
    }
    
    private function _setupCurl(){
        $this->_initCurlSettings();
        $this->_initCurlCustomRequest();
        $this->_initCurlPostMode();
        $this->_initCurlHeader();
        $this->_initCurlProxyPassword();
    }
    
    private function _initCurlSettings(){
        curl_setopt_array(
            $this->_curlHandler, 
            array(
                CURLOPT_URL             =>  $this->url,
                CURLOPT_USERAGENT       =>  $this->_config['curlUserAgent'],
                CURLOPT_CONNECTTIMEOUT  =>  $this->_config['curlConnectTimeout'],
                CURLOPT_TIMEOUT         =>  $this->_config['curlTimeout'],
                CURLOPT_RETURNTRANSFER  =>  $this->_config['curlReturnTransf'],
                CURLOPT_SSL_VERIFYPEER  =>  $this->_config['curlSSLVerifyPeer'],
                CURLOPT_FOLLOWLOCATION  =>  $this->_config['curlFollowLocation'],
                CURLOPT_PROXY           =>  $this->_config['curlProxy'],
                CURLOPT_ENCODING        =>  $this->_config['curlEncoding'],
                CURLOPT_HEADER          =>  $this->_config['curlHeader'],
                CURLINFO_HEADER_OUT     =>  $this->_config['curlHeaderOut']
            )
        );
    }
    private function _initCurlCustomRequest(){
        if($this->_isCustomRequest){
            curl_setopt($this->_curlHandler, CURLOPT_CUSTOMREQUEST, $this->_customRequestType);
        }
    }
    
    private function _initCurlPostMode(){
        if($this->_isPost){
            curl_setopt($this->_curlHandler, CURLOPT_POST, $this->_isPost);
            curl_setopt($this->_curlHandler, CURLOPT_POSTFIELDS, $this->postVars);
        }
    }
    
    private function _initCurlHeader(){
        $this->_buildHTTPHeader();
        curl_setopt($this->_curlHandler, CURLOPT_HTTPHEADER, $this->header);
    }
    
    protected function _buildHTTPHeader(){
        if(isset($this->header)){
            return;
        }
        $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,"; 
        $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5"; 
        $header[] = "Cache-Control: max-age=0"; 
        $header[] = "Connection: keep-alive"; 
        $header[] = "Keep-Alive: 300"; 
        $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7"; 
        $header[] = "Accept-Language: en-us,en;q=0.5"; 
        $header[] = "Pragma: ";  
        $this->header = $header;
    }
    
    private function _initCurlProxyPassword(){
        if ($this->_config['curlProxyPassword'] !== false) {
            curl_setopt($this->_curlHandler, CURLOPT_PROXYUSERPWD, $this->_config['curl_proxyuserpwd']);
        } 
    }
    
    public function resetResponse(){
        $this->_response = '';
        $this->_responseInfoParts = array();
    }
    
    public function setUrl($url){
        $this->url = $url;
    }
    
    public function setHeader($header=''){
        $this->header = $header;
    }
    
    public function setCustomRequest($type){
        $this->_isCustomRequest = true;
        $this->_customRequestType = $type;
    }
    
    public function setPostVars($postVars){
        if(is_array($postVars)){
            if(!empty($postVars)){
                $this->_isPost = true;
                $this->postVars = http_build_query($postVars);
            }
        }else if($postVars != ""){
            $this->postVars = $postVars;
            $this->_isPost = true;
        }
    }
    
    public function getResponse(){
        return $this->_response;
    }
    
    public function getInfo(){
        return $this->_responseInfoParts;
    }
    
    public function getInfoHTTPCode(){
        return $this->_responseInfoParts['http_code'];
    }
        
    public function isOK(){
        if ($this->_responseInfoParts['http_code'] == self::HTTP_STATUS_OK) {
            return true;
        }else{
            return false;
        }
    }
    
    public function __get($name) {
        return $this->$name;
    }
    
    public function __set($name, $value) {
        $this->$name = $value;
    }
}