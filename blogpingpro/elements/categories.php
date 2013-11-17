<?php
/**
* Custom Form field for displaying categories
*
* @version  1.0
* @author Daniel Eliasson - joomla at stilero.com
* @copyright  (C) 2012-jul-28 Stilero Webdesign http://www.stilero.com
* @category Custom Form field
* @license    GPLv2
*/
 
// no direct access
defined('_JEXEC') or die('Restricted access');
class Categories{
    static function getCategories(){
        $db = JFactory::getDBO();
        $query =
            'SELECT '.$db->nameQuote('id').', '.$db->nameQuote('title').
                ' FROM '.$db->nameQuote('#__categories').
                ' WHERE '.$db->nameQuote('extension').'='.$db->quote('com_content').
                ' AND published = 1 ORDER BY '.$db->nameQuote('title').' ASC';
        $db->setQuery($query);    
        $result = $db->loadAssocList();
        return $result;
    }
    
    static function getJ15Categories(){
        $db = JFactory::getDBO();
        $query =
            'SELECT '.$db->nameQuote('id').', '.$db->nameQuote('title').
                ' FROM '.$db->nameQuote('#__categories').
                ' WHERE '.$db->nameQuote('published').' = 1 ORDER BY '.$db->nameQuote('title').' ASC';
        $db->setQuery($query);    
        $result = $db->loadAssocList();
        return $result;
    }
    
    static function selectList($id, $name, $selectedIDs, $isJ15=FALSE){
        $htmlCode = '<select id="'.$id.'" name="'.$name.'[]" class="inputbox" multiple="multiple" size="10">';
        $defaultOption = array(
            array(
                'id' => '', 
                'title' => JText::_('PLG_SYSTEM_BLOGPINGPRO_CATID_PING_ALL'))
            );
        $cats = $isJ15 ? self::getJ15Categories() : self::getCategories();
        $categories = array_merge($defaultOption, $cats);
        $options = '';
        foreach ($categories as $category) {
            $selected = '';
            if(isset($selectedIDs) && $selectedIDs !=""){
                $selected = '';
                if(in_array($category['id'], $selectedIDs) && $category['id'] != ""){
                    $selected = ' selected="selected"';
                }
            }
            $options .= '<option value="'.$category['id'].'"'.$selected.'>'.$category['title'].'</option>'; 
        }
        $htmlCode .= $options;
        $htmlCode .= '</select>';      
        return $htmlCode;
    }
    
}
if(version_compare(JVERSION, '1.6.0', '<')){
    /**
    * @since J1.5
    */
    class JElementCategories extends JElement{
        private $config;

        function fetchElement($name, $value, &$node, $control_name){
            $rawParams = $this->_parent->_raw;
            $params = explode("\n", $rawParams);
            $sectIDParams = explode('=', $params[0]);
            $sectIDs = explode('|',$sectIDParams[1]);
            return Categories::selectList($control_name.$name, $control_name.'['.$name.']', $sectIDs, true);
        }
        function fetchTooltip ( $label, $description, &$xmlElement, $control_name='', $name=''){
            $output = '<label id="'.$control_name.$name.'-lbl" for="'.$control_name.$name.'"';
            if ($description) {
                    $output .= ' class="hasTip" title="'.JText::_($label).'::'.JText::_($description).'">';
            } else {
                    $output .= '>';
            }
            $output .= JText::_( $label ).'</label>';
            return $output;        
        }
    }//End Class J1.5
}else{
    /**
    * @since J1.6
    */
    class JFormFieldCategories extends JFormField {
        protected $type = 'categories';

        protected function getInput(){
            $data = null;
            $elementName = 'catID';
            foreach ((Array)$this->form as $key => $val) {
                if($val instanceof JRegistry){
                $data = &$val;
                break;
                }
            }
            $data = $data->toArray();
            $selectedOptions = '';
            if(isset($data['params'][$elementName])){
                $selectedOptions = $data['params'][$elementName];
            }
            return Categories::selectList($this->id, $this->name, $selectedOptions);
        }
        
        protected function getLabel(){
            $toolTip = JText::_($this->element['description']);
            $text = JText::_($this->element['label']);
            $labelHTML = '<label id="'.$this->id.'-lbl" for="'.$this->id.'" class="hasTip" title="'.$text.'::'.$toolTip.'">'.$text.'</label>';
            return $labelHTML;
        }
        
    }//End Class
}