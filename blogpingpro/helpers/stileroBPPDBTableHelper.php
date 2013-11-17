<?php
/**
 * DB Table Helpers
 *
 * @version  1.0
 * @package Stilero
 * @subpackage plg_twittertweet
 * @author Daniel Eliasson <daniel at stilero.com>
 * @copyright  (C) 2013-aug-12 Stilero Webdesign (http://www.stilero.com)
 * @license	GNU General Public License version 2 or later.
 * @link http://www.stilero.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 

class StileroBPPDBTableHelper{
    
    /**
     * Checks if a table exists and returns true on success
     * @param string $tableName The Tablename in Joomla style for example '#__twittertweet_tweeted'
     * @return boolean true on success, otherwise false
     */
    public static function isExisting($tableName){
        $db = JFactory::getDbo();
        $query = "DESC ".$db->nameQuote($tableName);
        $db->setQuery($query);
        $isTableFound = $db->query();
        if($isTableFound){
            return TRUE;
        }
        return FALSE;
    }
}
