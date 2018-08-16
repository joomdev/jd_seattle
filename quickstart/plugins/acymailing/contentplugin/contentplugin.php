<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class plgAcymailingContentplugin extends JPlugin
{

	function __construct(&$subject, $config){
		parent::__construct($subject, $config);

		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('acymailing', 'contentplugin');
			$this->params = new acyParameter( $plugin->params );
		}

		$this->paramsContent = JComponentHelper::getParams('com_content');
		acymailing_importPlugin('content');

		$excludedHandlers = array('plgContentEmailCloak','pluginImageShow');
		$excludedNames = array('system' => array('SEOGenerator','SEOSimple'), 'content' => array('webeecomment','highslide','smartresizer','phocagallery'));
		$excludedType = array_keys($excludedNames);

		if(!ACYMAILING_J16){
			$this->dispatcherContent = JDispatcher::getInstance();
			foreach ($this->dispatcherContent->_observers as $id => $observer){
				if (is_array($observer) AND in_array($observer['handler'],$excludedHandlers)){
					$this->dispatcherContent->_observers[$id]['event'] = '';
				}elseif(is_object($observer)){
					if(in_array($observer->_type,$excludedType) AND in_array($observer->_name,$excludedNames[$observer->_type])){
						$this->dispatcherContent->_observers[$id] = null;
					}
				}
			}
		}

		if(!class_exists('JSite')) include_once(ACYMAILING_ROOT.'includes'.DS.'application.php');

	}

	function acymailing_replacetags(&$email,$send = true){

		$art = new stdClass();
		$art->title = $email->subject;
		$art->introtext = $email->body;
		$art->fulltext = $email->body;
		$art->attribs = '';
		$art->state=1;
		$art->created_by=@$email->userid;
		$art->images = '';
		$art->id = 0;
		$art->section = 0;
		$art->catid = 0;

		$context = 'com_acymailing';


		try{
			if(!empty($email->body)){
				$art->text = $email->body;
				if(!ACYMAILING_J16){
					$resultsPlugin = acymailing_trigger('onPrepareContent', array(&$art, &$this->paramsContent, 0));
				}else{
					if($send) $art->text .= '{emailcloak=off}';
					$resultsPlugin = acymailing_trigger('onContentPrepare', array($context, &$art, &$this->paramsContent, 0));
					if($send) $art->text = str_replace(array('{emailcloak=off}','{* emailcloak=off}'),'',$art->text);
				}
				$email->body = $art->text;
			}
			if(!empty($email->altbody)){
				$art->text = $email->altbody;
				if(!ACYMAILING_J16){
					$resultsPlugin = acymailing_trigger('onPrepareContent', array(&$art, &$this->paramsContent, 0));
				}else{
					if($send) $art->text .= '{emailcloak=off}';
					$resultsPlugin = acymailing_trigger('onContentPrepare', array ($context,&$art, &$this->paramsContent, 0 ));
					if($send) $art->text = str_replace(array('{emailcloak=off}','{* emailcloak=off}'),'',$art->text);
				}
				$email->altbody = $art->text;
			}
		}catch(Exception $e){
			acymailing_display(array('An error occured with the AcyMailing contentplugin plugin, you may want to disable it from the AcyMailing configuration page',$e->getMessage()),'error');
		}

	}
}//endclass
