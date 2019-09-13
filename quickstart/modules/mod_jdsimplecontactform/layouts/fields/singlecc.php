<?php
/**
 * @package   JD Simple Contact Form
 * @author    JoomDev https://www.joomdev.com
 * @copyright Copyright (C) 2009 - 2019 JoomDev.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
 */
// no direct access
defined('_JEXEC') or die;
extract($displayData);
$singleCCName = $params->get('singleSendCopyEmail_field', '');
$singleCCTitle = $params->get('singleSendCopyEmailField_title', 'MOD_JDSCF_SINGLE_SEND_COPY_LBL_TITLE');
?>
<div class="form-group form-check">
    <label class="form-check-label"><input type="checkbox" name="jdscf[<?php echo $singleCCName; ?>][single_cc]" value="1" /> 
</div>
<?php echo JText::_($singleCCTitle); ?></label>