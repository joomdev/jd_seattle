<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acylistslisting" >
<h1 class="componentheading"><?php echo acymailing_translation('MAILING_LISTS'); ?></h1>
<?php
	if(!empty($this->listsintrotext)) echo '<div class="acymailing_listsintrotext" >'.$this->listsintrotext.'</div>';
	$k = 0;

	foreach($this->rows as $i => $oneList){
		$row =& $this->rows[$i];
		$frontEndAccess = true;
		$frontEndManagement = false;

		if(!$frontEndManagement AND (!$frontEndAccess OR !$row->published OR !$row->visible)) continue;
?>

	<div class="<?php echo "acymailing_list acymailing_row$k"; ?>">
			<div class="list_name"><a href="<?php echo acymailing_completeLink('archive&listid='.$row->listid.'-'.$row->alias.$this->item)?>"><?php echo $row->name; ?></a></div>
			<div class="list_description"><?php echo $row->description; ?></div>
	</div>
<?php
		$k = 1-$k;
	}

	if(!empty($this->listsfinaltext)) echo '<div class="acymailing_listsfinaltext" >'.$this->listsfinaltext.'</div>';
?>
</div>
