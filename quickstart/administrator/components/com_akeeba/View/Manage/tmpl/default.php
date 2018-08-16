<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/** @var  \Akeeba\Backup\Admin\View\Manage\Html  $this */
use FOF30\Utils\FEFHelper\Html as FEFHtml;

$urlIncludeFolders = addslashes(JUri::base() . 'index.php?option=com_akeeba&view=IncludeFolders&task=ajax');
$urlBrowser = addslashes(JUri::base() . 'index.php?option=com_akeeba&view=Browser&processfolder=1&tmpl=component&folder=');

$js = FEFHtml::jsOrderingBackend($this->order);
$this->getContainer()->template->addJSInline($js);

?>

<?php if($this->promptForBackupRestoration): ?>
<?php echo $this->loadAnyTemplate('admin:com_akeeba/Manage/howtorestore_modal'); ?>
<?php endif; ?>

<div class="akeeba-block--info">
	<h4><?php echo \JText::_('COM_AKEEBA_BUADMIN_LABEL_HOWDOIRESTORE_LEGEND'); ?></h4>
    <p>
	    <?php echo \JText::sprintf('COM_AKEEBA_BUADMIN_LABEL_HOWDOIRESTORE_TEXT_PRO',
		    'https://www.akeebabackup.com/videos/1212-akeeba-backup-core/1618-abtc04-restore-site-new-server.html',
		    'index.php?option=com_akeeba&view=Transfer',
		    'https://www.akeebabackup.com/latest-kickstart-core.zip'
	    ); ?>
    </p>
</div>

<div id="j-main-container">
	<form action="index.php" method="post" name="adminForm" id="adminForm" class="akeeba-form">

        <section class="akeeba-panel--33-66 akeeba-filter-bar-container">
            <div class="akeeba-filter-bar akeeba-filter-bar--left akeeba-form-section akeeba-form--inline">
                <div class="akeeba-filter-element akeeba-form-group">
                    <input type="text" name="description" placeholder="<?php echo \JText::_('COM_AKEEBA_BUADMIN_LABEL_DESCRIPTION'); ?>"
                           id="filter_description"
                           value="<?php echo $this->escape($this->fltDescription); ?>"
                           title="<?php echo \JText::_('COM_AKEEBA_BUADMIN_LABEL_DESCRIPTION'); ?>"/>
                </div>

                <div class="akeeba-filter-element akeeba-form-group akeeba-filter-joomlacalendarfix">
	                <?php echo \JHtml::_('calendar', $this->fltFrom, 'from', 'from', '%Y-%m-%d', array('class' => 'input-small')); ?>
                </div>

                <div class="akeeba-filter-element akeeba-form-group akeeba-filter-joomlacalendarfix">
	                <?php echo \JHtml::_('calendar', $this->fltTo, 'to', 'to', '%Y-%m-%d', array('class' => 'input-small')); ?>
                </div>

                <div class="akeeba-filter-element akeeba-form-group">
                    <button class="akeeba-btn--grey akeeba-btn--icon-only akeeba-btn--small akeeba-hidden-phone" onclick="this.form.submit();" title="<?php echo \JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
                        <span class="akion-search"></span>
                    </button>
                </div>

                <div class="akeeba-filter-element akeeba-form-group">
	                <?php echo \JHtml::_('select.genericlist', $this->profilesList, 'profile', 'onchange="document.forms.adminForm.submit()" class="advancedSelect"', 'value', 'text', $this->fltProfile); ?>
                </div>
            </div>

            <?php echo FEFHtml::selectOrderingBackend($this->getPagination(), $this->sortFields, $this->order, $this->order_Dir)?>

        </section>

		<table class="akeeba-table akeeba-table--striped" id="itemsList">
		<thead>
			<tr>
				<th width="32">
					<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
				</th>
				<th width="48" class="akeeba-hidden-phone">
					<?php echo \JHtml::_('grid.sort', 'COM_AKEEBA_BUADMIN_LABEL_ID', 'id', $this->order_Dir, $this->order, 'default'); ?>
				</th>
				<th>
					<?php echo \JHtml::_('grid.sort', 'COM_AKEEBA_BUADMIN_LABEL_DESCRIPTION', 'description', $this->order_Dir, $this->order, 'default'); ?>
				</th>
				<th class="akeeba-hidden-phone">
					<?php echo \JHtml::_('grid.sort', 'COM_AKEEBA_BUADMIN_LABEL_PROFILEID', 'profile_id', $this->order_Dir, $this->order, 'default'); ?>
				</th>
				<th width="80">
					<?php echo \JText::_('COM_AKEEBA_BUADMIN_LABEL_DURATION'); ?>
				</th>
				<th width="40">
					<?php echo \JText::_('COM_AKEEBA_BUADMIN_LABEL_STATUS'); ?>
				</th>
				<th width="80" class="akeeba-hidden-phone">
					<?php echo \JText::_('COM_AKEEBA_BUADMIN_LABEL_SIZE'); ?>
				</th>
				<th class="akeeba-hidden-phone">
					<?php echo \JText::_('COM_AKEEBA_BUADMIN_LABEL_MANAGEANDDL'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="11" class="center">
					<?php echo $this->pagination->getListFooter(); ?>

				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php if(empty($this->list)): ?>
			<tr>
				<td colspan="11" class="center">
					<?php echo \JText::_('COM_AKEEBA_BACKUP_STATUS_NONE'); ?>
				</td>
			</tr>
		<?php endif; ?>
		<?php if ( ! (empty($this->list))): ?>
			<?php $id = 1; $i = 0; ?>
			<?php foreach($this->list as $record): ?>
				<?php
				$id = 1 - $id;
				list($originDescription, $originIcon) = $this->getOriginInformation($record);
				list($startTime, $duration, $timeZoneText) = $this->getTimeInformation($record);
				list($statusClass, $statusIcon) = $this->getStatusInformation($record);
				$profileName = $this->getProfileName($record);
				?>
				<tr class="row<?php echo $id; ?>">
					<td><?php echo \JHtml::_('grid.id', ++$i, $record['id']); ?></td>
					<td class="akeeba-hidden-phone">
						<?php echo $this->escape($record['id']); ?>

					</td>
					<td>
						<span class="<?php echo $originIcon; ?> akeebaCommentPopover" rel="popover"
							  title="<?php echo \JText::_('COM_AKEEBA_BUADMIN_LABEL_ORIGIN'); ?>"
							  data-content="<?php echo $this->escape($originDescription); ?>"></span>
						<?php if ( ! (empty($record['comment']))): ?>
						<span class="akion-help-circled akeebaCommentPopover" rel="popover"
							  data-content="<?php echo $this->escape($record['comment']); ?>"></span>
						<?php endif; ?>
						<a href="<?php echo $this->escape(JUri::base()); ?>index.php?option=com_akeeba&view=Manage&task=showcomment&id=<?php echo $this->escape((int)$record['id']); ?>">
							<?php echo $this->escape(empty($record['description']) ? JText::_('COM_AKEEBA_BUADMIN_LABEL_NODESCRIPTION') : $record['description']); ?>

						</a>
						<br/>
						<div class="akeeba-buadmin-startdate" title="<?php echo \JText::_('COM_AKEEBA_BUADMIN_LABEL_START'); ?>">
							<small>
								<span class="akion-calendar"></span>
								<?php echo $this->escape($startTime); ?> <?php echo $this->escape($timeZoneText); ?>
							</small>
						</div>
					</td>
					<td class="akeeba-hidden-phone">
						#<?php echo $this->escape((int)$record['profile_id']); ?>. <?php echo $this->escape($profileName); ?>

						<br/>
						<small>
							<em><?php echo $this->escape($this->translateBackupType($record['type'])); ?></em>
						</small>
					</td>
					<td>
						<?php echo $this->escape($duration); ?>

					</td>
					<td>
						<span class="<?php echo $statusClass; ?> akeebaCommentPopover" rel="popover"
							  title="<?php echo \JText::_('COM_AKEEBA_BUADMIN_LABEL_STATUS'); ?>"
							  data-content="<?php echo \JText::_('COM_AKEEBA_BUADMIN_LABEL_STATUS_' . $record['meta']); ?>">
							<span class="<?php echo $statusIcon; ?>"></span>
						</span>
					</td>
					<td class="akeeba-hidden-phone">
						<?php if($record['meta'] == 'ok'): ?>
							<?php echo $this->escape($this->formatFilesize($record['size'])); ?>

						<?php elseif($record['total_size'] > 0): ?>
							<i><?php echo $this->formatFilesize($record['total_size']); ?></i>
						<?php else: ?>
							&mdash;
						<?php endif; ?>
					</td>
					<td class="akeeba-hidden-phone">
						<?php echo $this->loadAnyTemplate('admin:com_akeeba/Manage/manage_column', [
							'record' => &$record
						]); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
		</table>

        <div class="akeeba-hidden-fields-container">
            <input type="hidden" name="option" id="option" value="com_akeeba"/>
            <input type="hidden" name="view" id="view" value="Manage"/>
            <input type="hidden" name="boxchecked" id="boxchecked" value="0"/>
            <input type="hidden" name="task" id="task" value="default"/>
            <input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->escape($this->order); ?>"/>
            <input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $this->escape($this->order_Dir); ?>"/>
            <input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1"/>
        </div>
	</form>
</div>
