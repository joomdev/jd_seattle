<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

class JFormFieldContent extends JFormFieldList {

	protected $type = 'Content';

	public function getOptions() {

         $db = JFactory::getDbo();
         $query = $db->getQuery(true);
         $query->select('a.*')->from('`#__content` AS a');
         $rows = $db->setQuery($query)->loadObjectlist();
         foreach($rows as $row){
            $cities[] = $row->title;
         }
         // Merge any additional options in the XML definition.
          $options = array_merge(parent::getOptions(), $cities);
         return $options;
	}
}