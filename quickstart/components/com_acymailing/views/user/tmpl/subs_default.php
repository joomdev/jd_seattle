<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acyusersubscription">
  <?php
  $k = 0;
  foreach($this->subscription as $row){
    if(empty($row->published) OR !$row->visible) continue;
    $listClass = 'acy_list_status_' . str_replace('-','m',(int) @$row->status);
    ?>
  <div class="<?php echo "row$k $listClass"; ?> acy_onelist">
    <div class="acystatus">
      <span><?php echo $this->status->display("data[listsub][".$row->listid."][status]",@$row->status); ?></span>
    </div>
    <div class="acyListInfo">
      <div class="list_name"><?php echo $row->name ?></div>
      <div class="list_description"><?php echo $row->description ?></div>
    </div>
  </div>
  <?php
    $k = 1 - $k;
  } ?>

</div>

