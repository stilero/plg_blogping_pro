<?php
/**
 * Class for holding info about server
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

class StileroBPPServer{
    
    /**
     * URL to the server
     * @var string 
     */
    public $url;
    
    /**
     * Holds info about a server
     * @param string $url
     */
    public function __construct($url) {
        $this->url = $url;
    }
}
