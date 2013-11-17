<?php
/**
 * Blog Class
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

class StileroBPPBlog{
    /**
     * The Name of the Blog
     * @var type string
     */
    public $name;
    
    /**
     * URL to the Blog
     * @var string 
     */
    public $url;
    
    /**
     * URL to the updated Blog post / article
     * @var string 
     */
    public $updateUrl;
    
    /**
     * URL to the RSS Feed
     * @var string 
     */
    public $rss;
    
    /**
     * Array with tags from the article / blog post
     * @var array 
     */
    public $tags;
    
    /**
     * Class for Blog posts
     * @param string $blogName
     * @param string $url
     * @param string $itemUrl
     * @param array $tags
     */
    public function __construct($blogName, $url, $itemUrl = '', $rss='', $tags = array()) {
        $this->name = $blogName;
        $this->url = $url;
        $this->updateUrl = $itemUrl;
        $this->rss = $rss;
        $this->tags = $tags;
    }
    
    /**
     * Checks if the blog contains extended info (udateURL, RSS and Tags)
     * @return boolean
     */
    public function hasExtendedInfo(){
        if(isset($this->updateUrl) && isset($this->rss) && isset($this->tags)){
            return true;
        }  else {
            return false;
        }
    }
}
