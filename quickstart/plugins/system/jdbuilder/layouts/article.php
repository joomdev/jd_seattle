<?php
/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2019 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
extract($displayData);
?>
<div id="jdbuilder-controls" class="<?php echo $enabled ? 'active' : ''; ?>">
   <button onclick="jQuery('#jdbuilder-area').addClass('active');jQuery('#jdbuilder-controls').addClass('active');jQuery('[name=\'jdbparams[enabled]\']').val(1)" class="btn-jdb btn-jdb-primary btn-jdb-edit" type="button">Edit with JD Builder</button>
   <button onclick="jQuery('#jdbuilder-area').removeClass('active');jQuery('#jdbuilder-controls').removeClass('active');jQuery('[name=\'jdbparams[enabled]\']').val(0)" class="btn-jdb btn-jdb-gray-light btn-jdb-exit" type="button">Back to Joomla Editor</button>
   <!--<button onclick="jQuery('#jdbuilder-area').addClass('full-screen')" class="btn-jdb btn-jdb-primary btn-jdb-fs pull-right" type="button">Full Screen</button>-->
   <div class="clearfix"></div>
</div>
<input type="hidden" name="jdbparams[enabled]" value="<?php echo $enabled ? 1 : 0; ?>" />
<br/>
<script>
   _JDB.ITEM = {"id": <?php echo $id; ?>, "layout_id": <?php echo $lid; ?>, "type": "article"};
</script>