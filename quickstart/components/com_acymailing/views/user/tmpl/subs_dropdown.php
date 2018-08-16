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
  $selectedIndex = '';
  foreach($this->subscription as $key => $row) {
    if(empty($row->published) OR !$row->visible) continue;

    $value = 0;
    $dropdownOpts[] = acymailing_selectOption($row->listid, $row->name);
    if($row->status == 1) {
      $value = 1;
      $selectedIndex = $k;
    }
    echo '<input type="hidden" class="listsub-dropdown" name="data[listsub]['.$row->listid.'][status]" value="'.$value.'">';

    $k++;
  }

  $dropdown = acymailing_select($dropdownOpts, 'data[listsubdropdown]', 'onchange="setSubsDropdown()"', 'value', 'text', $selectedIndex);
  echo $dropdown;
  ?>
</div>
<script type="text/javascript">
  function setSubsDropdown() {
    var dropdown = document.getElementById('datalistsubdropdown');
    var selectedOption = dropdown.options[dropdown.selectedIndex];
    var selectedListId = selectedOption.value;

    var hiddenInputs = document.getElementsByClassName('listsub-dropdown');
    for(var i = 0; i < hiddenInputs.length; i++) {
      hiddenInputs[i].value = '0';
      if(hiddenInputs[i].name == 'data[listsub][' + selectedListId + '][status]') {
        hiddenInputs[i].value = '1';
      }
    }
  }
</script>

