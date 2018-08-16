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
include(ACYMAILING_BACK.'views'.DS.'list'.DS.'view.html.php');
class FrontlistViewFrontlist extends ListViewList
{
	var $ctrl = 'frontlist';

	function display($tpl = null){
		global $Itemid;
		$this->Itemid = $Itemid;

		parent::display($tpl);
	}

	function listing(){
		if(empty($_POST) && !acymailing_getVar('int', 'start') && !acymailing_getVar('int', 'limitstart')){
			acymailing_setVar('limitstart',0);
		}

		return parent::listing();
	}
}
