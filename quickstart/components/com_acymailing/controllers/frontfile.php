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
$currentUserid = acymailing_currentUserId();
if(empty($currentUserid)){
	acymailing_askLog();
	return false;
}

include(ACYMAILING_BACK.'controllers'.DS.'file.php');

class FrontfileController extends FileController
{
	function __construct($config = array()){
		parent::__construct($config);

		$task = acymailing_getVar('string', 'task');
		if($task != 'select') die('Access not allowed');
	}

	function select(){
		acymailing_setVar('layout', 'select');
		return parent::display();
	}
}
