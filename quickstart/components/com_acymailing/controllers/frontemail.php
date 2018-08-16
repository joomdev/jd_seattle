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
$userid = acymailing_currentUserId();
if(empty($userid)) die(acymailing_translation('ASK_LOG'));

$config = acymailing_config();
if(!acymailing_isAllowed($config->get('acl_lists_manage', 'all'))) die('You are not allowed to access this page');

include(ACYMAILING_BACK.'controllers'.DS.'email.php');
class FrontemailController extends EmailController{
}
