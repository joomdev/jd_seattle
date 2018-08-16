<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();
?>
<h2>
	<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_CHECK_BACKUPS'); ?>
</h2>

<p>
	<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_HEADERINFO'); ?>;
</p>

<div class="akeeba-panel--information">
    <header class="akeeba-block-header">
        <h3><?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_CLICRON'); ?></h3>
    </header>

	<?php if (AKEEBA_PRO): ?>
        <div class="akeeba-block--info">
            <p>
				<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_CLICRON_INFO'); ?>
            </p>
            <p>
                <a class="akeeba-btn--teal"
                   href="https://www.akeebabackup.com/documentation/akeeba-backup-documentation/native-cron-script.html"
                   target="_blank">
                    <span class="akion-ios-book"></span>
					<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_GENERICREADDOC'); ?>
                </a>
            </p>
        </div>

        <p>
			<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_GENERICUSECLI'); ?>
            <code>
				<?php echo $this->escape($this->checkinfo->info->php_path); ?>
                <?php echo $this->escape($this->checkinfo->cli->path); ?>

            </code>
        </p>

        <p>
            <span class="akeeba-label--warning"><?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_CLIGENERICIMPROTANTINFO'); ?></span>
			<?php echo \JText::sprintf('COM_AKEEBA_SCHEDULE_LBL_CLIGENERICINFO', $this->croninfo->info->php_path); ?>
        </p>
	<?php endif; ?>

	<?php if (!AKEEBA_PRO): ?>
        <div class="akeeba-block--warning">
            <p>
				<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_UPGRADETOPRO'); ?></p>
            <p>
                <a class="akeeba-btn--green" href="https://www.akeebabackup.com/subscribe.html" target="_blank">
                    <span class="akion-card"></span>
					<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_UPGRADENOW'); ?>
                </a>
            </p>
        </div>
	<?php endif; ?>
</div>

<div class="akeeba-panel--information">
    <header class="akeeba-block-header">
        <h3><?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_ALTCLICRON'); ?></h3>
    </header>

	<?php if (AKEEBA_PRO): ?>
        <div class="akeeba-block--info">
            <p>
				<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_ALTCLICRON_INFO'); ?>
            </p>
            <p>
                <a class="akeeba-btn--teal"
                   href="https://www.akeebabackup.com/documentation/akeeba-backup-documentation/alternative-cron-script.html"
                   target="_blank">
                    <span class="akion-ios-book"></span>
					<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_GENERICREADDOC'); ?>
                </a>
            </p>
        </div>

		<?php if (!$this->checkinfo->info->feenabled): ?>
            <div class="akeeba-block--failure">
                <p>
					<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_DISABLED'); ?>
                </p>
            </div>
		<?php endif; ?>

		<?php if ($this->croninfo->info->feenabled && !trim($this->croninfo->info->secret)): ?>
            <div class="akeeba-block--failure">
                <p>
					<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_SECRET'); ?>
                </p>
            </div>
		<?php endif; ?>

		<?php if ($this->croninfo->info->feenabled && trim($this->croninfo->info->secret)): ?>
            <p>
				<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_GENERICUSECLI'); ?>
                <code>
					<?php echo $this->escape($this->checkinfo->info->php_path); ?>
                    <?php echo $this->escape($this->checkinfo->altcli->path); ?>

                </code>
            </p>
            <p>
                <span class="akeeba-label--warning"><?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_CLIGENERICIMPROTANTINFO'); ?></span>
				<?php echo \JText::sprintf('COM_AKEEBA_SCHEDULE_LBL_CLIGENERICINFO', $this->checkinfo->info->php_path); ?>
            </p>
		<?php endif; ?>

	<?php endif; ?>
	<?php if (!AKEEBA_PRO): ?>
        <div class="akeeba-block--warning">
            <p>
				<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_UPGRADETOPRO'); ?></p>
            <p>
                <a class="akeeba-btn--green" href="https://www.akeebabackup.com/subscribe.html" target="_blank">
                    <span class="akion-card"></span>
					<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_UPGRADENOW'); ?>
                </a>
            </p>
        </div>
	<?php endif; ?>

</div>

<div class="akeeba-panel--information">
    <header class="akeeba-block-header">
        <h3><?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTENDBACKUP'); ?></h3>
    </header>

    <div class="akeeba-block--info">
        <p>
			<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTENDBACKUP_INFO'); ?>
        </p>
        <p>
            <a class="akeeba-btn--info"
               href="https://www.akeebabackup.com/documentation/akeeba-backup-documentation/automating-your-backup.html"
               target="_blank">
                <span class="akion-ios-book"></span>
				<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_GENERICREADDOC'); ?>
            </a>
        </p>
    </div>

	<?php if (!$this->croninfo->info->feenabled): ?>
        <div class="akeeba-block--failure">
            <p>
				<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_DISABLED'); ?>
            </p>
        </div>
	<?php endif; ?>

	<?php if ($this->croninfo->info->feenabled && !trim($this->croninfo->info->secret)): ?>
        <div class="akeeba-block--failure">
            <p>
				<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_SECRET'); ?>
            </p>
        </div>
	<?php endif; ?>

	<?php if ($this->checkinfo->info->feenabled && trim($this->checkinfo->info->secret)): ?>
        <p>
			<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTENDBACKUP_MANYMETHODS'); ?>
        </p>

        <h4>
			<?php echo JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTENDBACKUP_TAB_WEBCRON', true) ?>
        </h4>

		<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON'); ?>

        <table class="akeeba-table--striped" width="100%">
            <tr>
                <td></td>
                <td>
					<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_INFO'); ?>
                </td>
            </tr>
            <tr>
                <td>
					<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_NAME'); ?>
                </td>
                <td>
					<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_NAME_INFO'); ?>
                </td>
            </tr>
            <tr>
                <td>
					<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_TIMEOUT'); ?>
                </td>
                <td>
					<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_TIMEOUT_INFO'); ?>
                </td>
            </tr>
            <tr>
                <td>
					<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_URL'); ?>
                </td>
                <td>
					<?php echo $this->escape($this->checkinfo->info->root_url); ?>/<?php echo $this->escape($this->checkinfo->frontend->path); ?>

                </td>
            </tr>
            <tr>
                <td>
					<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_LOGIN'); ?>
                </td>
                <td>
					<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_LOGINPASSWORD_INFO'); ?>
                </td>
            </tr>
            <tr>
                <td>
					<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_PASSWORD'); ?>
                </td>
                <td>
					<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_LOGINPASSWORD_INFO'); ?>
                </td>
            </tr>
            <tr>
                <td>
					<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_EXECUTIONTIME'); ?>
                </td>
                <td>
					<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_EXECUTIONTIME_INFO'); ?>
                </td>
            </tr>
            <tr>
                <td>
					<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_ALERTS'); ?>
                </td>
                <td>
					<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_ALERTS_INFO'); ?>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
					<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WEBCRON_THENCLICKSUBMIT'); ?>
                </td>
            </tr>
        </table>

        <h4><?php echo JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTENDBACKUP_TAB_WGET', true) ?></h4>

        <p>
			<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_WGET'); ?>
            <code>
                wget --max-redirect=10000 "<?php echo $this->escape($this->checkinfo->info->root_url); ?>/<?php echo $this->escape($this->checkinfo->frontend->path); ?>" -O - 1>/dev/null 2>/dev/null
            </code>
        </p>

        <h4><?php echo JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTENDBACKUP_TAB_CURL', true) ?></h4>

        <p>
			<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_CURL'); ?>
            <code>
                curl -L --max-redirs 1000 -v "<?php echo $this->escape($this->checkinfo->info->root_url); ?>/<?php echo $this->escape($this->checkinfo->frontend->path); ?>" 1>/dev/null 2>/dev/null
            </code>
        </p>

        <h4><?php echo JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTENDBACKUP_TAB_SCRIPT', true) ?></h4>

        <p>
			<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_CUSTOMSCRIPT'); ?>
        </p>
        <pre>
&lt;?php
    $curl_handle=curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, '<?php echo $this->escape($this->checkinfo->info->root_url); ?>/<?php echo $this->escape($this->checkinfo->frontend->path); ?> ?>');
    curl_setopt($curl_handle,CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($curl_handle,CURLOPT_MAXREDIRS, 10000);
    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER, 1);
    $buffer = curl_exec($curl_handle);
    curl_close($curl_handle);
    if (empty($buffer))
        echo "Sorry, the check didn't work.";
    else
        echo $buffer;
?&gt;
        </pre>

        <h4><?php echo JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTENDBACKUP_TAB_URL', true) ?></h4>

        <p>
			<?php echo \JText::_('COM_AKEEBA_SCHEDULE_LBL_FRONTEND_RAWURL'); ?>
            <code>
				<?php echo $this->escape($this->checkinfo->info->root_url); ?>/<?php echo $this->escape($this->checkinfo->frontend->path); ?>
            </code>
        </p>

	<?php endif; ?>
</div>
