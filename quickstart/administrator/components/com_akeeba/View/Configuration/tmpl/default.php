<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/** @var  \Akeeba\Backup\Admin\View\Configuration\Html $this */

$urls       = array(
	'browser'      => addslashes('index.php?option=com_akeeba&view=Browser&processfolder=1&tmpl=component&folder='),
	'ftpBrowser'   => addslashes('index.php?option=com_akeeba&view=FTPBrowser'),
	'sftpBrowser'  => addslashes('index.php?option=com_akeeba&view=SFTPBrowser'),
	'testFtp'      => addslashes('index.php?option=com_akeeba&view=Configuration&task=testftp'),
	'testSftp'     => addslashes('index.php?option=com_akeeba&view=Configuration&task=testsftp'),
	'dpeauthopen'  => addslashes('index.php?option=com_akeeba&view=Configuration&task=dpeoauthopen&format=raw'),
	'dpecustomapi' => addslashes('index.php?option=com_akeeba&view=Configuration&task=dpecustomapi&format=raw'),
);
$this->json = addcslashes($this->json, "'\\");
$js         = <<< JS

;// This comment is intentionally put here to prevent badly written plugins from causing a Javascript error
// due to missing trailing semicolon and/or newline in their code.
akeeba.System.documentReady(function(){
	// Push some custom URLs
	akeeba.Configuration.URLs['browser']      = '{$urls['browser']}';
	akeeba.Configuration.URLs['ftpBrowser']   = '{$urls['ftpBrowser']}';
	akeeba.Configuration.URLs['sftpBrowser']  = '{$urls['sftpBrowser']}';
	akeeba.Configuration.URLs['testFtp']      = '{$urls['testFtp']}';
	akeeba.Configuration.URLs['testSftp']     = '{$urls['testSftp']}';
	akeeba.Configuration.URLs['dpeauthopen']  = '{$urls['dpeauthopen']}';
	akeeba.Configuration.URLs['dpecustomapi'] = '{$urls['dpecustomapi']}';
	akeeba.System.params.AjaxURL              = akeeba.Configuration.URLs['dpecustomapi'];

	// Load the configuration UI data in a timeout to prevent Safari from auto-filling the password fields
	var data = JSON.parse('{$this->json}');

	setTimeout(function ()
	{
		// Work around browsers which blatantly ignore autocomplete=off
		setTimeout('akeeba.Configuration.restoreDefaultPasswords();', 1000);

		// Render the configuration UI in the timeout to prevent Safari from auto-filling the password fields
		akeeba.Configuration.parseConfigData(data);

		// Enable popovers. Must obviously run after we have the UI set up.
		akeeba.Configuration.enablePopoverFor(document.querySelectorAll('[rel="popover"]'));
	}, 10);
});

JS;

$this->getContainer()->template->addJSInline($js);

?>
<?php /* Configuration Wizard pop-up */ ?>
<?php if ($this->promptForConfigurationWizard): ?>
	<?php echo $this->loadAnyTemplate('admin:com_akeeba/Configuration/confwiz_modal'); ?>
<?php endif; ?>

<?php /* Modal dialog prototypes */ ?>
<?php echo $this->loadAnyTemplate('admin:com_akeeba/CommonTemplates/FTPBrowser'); ?>
<?php echo $this->loadAnyTemplate('admin:com_akeeba/CommonTemplates/SFTPBrowser'); ?>
<?php echo $this->loadAnyTemplate('admin:com_akeeba/CommonTemplates/FTPConnectionTest'); ?>
<?php echo $this->loadAnyTemplate('admin:com_akeeba/CommonTemplates/ErrorModal'); ?>
<?php echo $this->loadAnyTemplate('admin:com_akeeba/CommonTemplates/FolderBrowser'); ?>

<?php if ($this->securesettings == 1): ?>
    <div class="akeeba-block--success">
		<?php echo \JText::_('COM_AKEEBA_CONFIG_UI_SETTINGS_SECURED'); ?>
    </div>
<?php elseif ($this->securesettings == 0): ?>
    <div class="akeeba-block--failure">
		<?php echo \JText::_('COM_AKEEBA_CONFIG_UI_SETTINGS_NOTSECURED'); ?>
    </div>
<?php endif; ?>

<?php echo $this->loadAnyTemplate('admin:com_akeeba/CommonTemplates/ProfileName'); ?>

<div class="akeeba-block--info">
	<?php echo \JText::_('COM_AKEEBA_CONFIG_WHERE_ARE_THE_FILTERS'); ?>
</div>

<form name="adminForm" id="adminForm" method="post" action="index.php"
      class="akeeba-form--horizontal akeeba-form--with-hidden akeeba-form--configuration">

    <div class="akeeba-panel--info" style="margin-bottom: -1em">
        <header class="akeeba-block-header">
            <h5>
                <?php echo JText::_('COM_AKEEBA_PROFILES_LABEL_DESCRIPTION') ?>
            </h5>
        </header>

        <div class="akeeba-form-group">
            <label for="profilename" rel="popover"
                   data-original-title="<?php echo JText::_('COM_AKEEBA_PROFILES_LABEL_DESCRIPTION') ?>"
                   data-content="<?php echo JText::_('COM_AKEEBA_PROFILES_LABEL_DESCRIPTION_TOOLTIP') ?>">
				<?php echo JText::_('COM_AKEEBA_PROFILES_LABEL_DESCRIPTION') ?>
            </label>
            <input type="text" name="profilename" id="profilename"
                   value="<?php echo $this->escape($this->profilename); ?>"/>
        </div>

        <div class="akeeba-form-group">
            <label class="control-label" for="quickicon" rel="popover"
                   data-original-title="<?php echo JText::_('COM_AKEEBA_CONFIG_QUICKICON_LABEL') ?>"
                   data-content="<?php echo JText::_('COM_AKEEBA_CONFIG_QUICKICON_DESC') ?>">
				<?php echo JText::_('COM_AKEEBA_CONFIG_QUICKICON_LABEL') ?>
            </label>
            <div>
                <input type="checkbox" name="quickicon"
                       id="quickicon" <?php echo $this->quickIcon ? 'checked="checked"' : ''; ?>/>
            </div>
        </div>
    </div>

    <!-- This div contains dynamically generated user interface elements -->
    <div id="akeebagui">
    </div>

    <div class="akeeba-hidden-fields-container">
        <input type="hidden" name="option" value="com_akeeba"/>
        <input type="hidden" name="view" value="Configuration"/>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="<?php echo $this->container->platform->getToken(true) ?>" value="1"/>
    </div>
</form>
