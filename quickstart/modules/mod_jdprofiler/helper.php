
<?php
/**
 * @package	JD profiler Module
 * @subpackage  mod_jdprofiler
 * @author	JoomDev
 * @copyright	Copyright (C) 2008 - 2018 Joomdev.com. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
$doc = JFactory::getDocument();
// Style Sheet
$doc->addStyleSheet(JURI::root().'media/mod_jdprofiler/assets/css/jd-profile-style.css');
$doc->addStyleSheet(JURI::root().'media/mod_jdprofiler/assets/css/jdgrid.css');
// Style Sheet
if($params->get('load_bootstrap', 1)){
	$doc->addStyleSheet('https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css');
}
if($params->get('load_fontawesome', 1)){
	$doc->addStyleSheet('https://use.fontawesome.com/releases/v5.3.1/css/all.css');
}

class modJdprofilerHelper {
    public function profiles($team,$limit,$sort,$order){
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__jdprofiler_profiles');
        $query->Where($db->quoteName('state') . ' = '. $db->quote(1));
        $query->Where($db->quoteName('team') . ' = '. $db->quote($team));
        if($order=="random"){
            $query->order('RAND() LIMIT '.$limit); 
        }elseif($order=="ordering"){
            $query->order($order);
            $query->setLimit($limit);
        }
        else{
            $query->order($order.' '.$sort);
            $query->setLimit($limit);
        }
        $db->setQuery($query);
        $results = $db->loadObjectList();
        return $results;
    }
}
