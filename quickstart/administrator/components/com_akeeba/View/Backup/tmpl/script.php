<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/** @var  $this  \Akeeba\Backup\Admin\View\Backup\Html */
$escapedDefaultDescription = addslashes($this->defaultDescription);
$escapedDescription = addslashes(empty($this->description) ? $this->defaultDescription : $this->description);
$escapedComment = addslashes($this->comment);
$escapedAngiePassword = addslashes($this->ANGIEPassword);
$escapedJpsKey = $this->showJPSPassword ? addslashes($this->jpsPassword) : '';
$autoResume = (int)$this->autoResume;
$autoResumeTimeout = (int)$this->autoResumeTimeout;
$autoResumeRetries = (int)$this->autoResumeRetries;
$maxExecTime = (int)$this->maxExecutionTime;
$runtimeBias = (int)$this->runtimeBias;
$escapedJuriBase = addslashes(JUri::base());
$escapedDomains = addcslashes($this->domains, "'\\");
$useIframe = $this->useIFRAME ? 'true' : 'false';
$innerJS = <<< JS
	// Initialization
	akeeba.Backup.defaultDescription = "$escapedDefaultDescription";
	akeeba.Backup.currentDescription = "$escapedDescription";
	akeeba.Backup.currentComment     = "$escapedComment";
	akeeba.Backup.config_angiekey    = "$escapedAngiePassword";
	akeeba.Backup.jpsKey             = "$escapedJpsKey";

	// Auto-resume setup
	akeeba.Backup.resume.enabled = $autoResume;
	akeeba.Backup.resume.timeout = $autoResumeTimeout;
	akeeba.Backup.resume.maxRetries = $autoResumeRetries;

	// The return URL
	akeeba.Backup.returnUrl = '{$this->returnURL}';

	// Used as parameters to start_timeout_bar()
	akeeba.Backup.maxExecutionTime = $maxExecTime;
	akeeba.Backup.runtimeBias = $runtimeBias;

	// Create a function for saving the editor's contents
	akeeba.Backup.commentEditorSave = function() {
	};

	akeeba.System.notification.iconURL = '{$escapedJuriBase}../media/com_akeeba/icons/logo-48.png';

	//Parse the domain keys
	akeeba.Backup.domains = JSON.parse('$escapedDomains');

	// Setup AJAX proxy URL
	akeeba.System.params.AjaxURL = 'index.php?option=com_akeeba&view=Backup&task=ajax';

	// Setup base View Log URL
	akeeba.Backup.URLs.LogURL = '{$escapedJuriBase}index.php?option=com_akeeba&view=Log';
	akeeba.Backup.URLs.AliceURL = '{$escapedJuriBase}index.php?option=com_akeeba&view=Alice';

	// Setup the IFRAME mode
	akeeba.System.params.useIFrame = $useIframe;

JS;

if ($this->desktopNotifications)
{
	$innerJS .= <<< JS
	akeeba.System.notification.askPermission();

JS;
}

if (!$this->unwriteableOutput && $this->autoStart)
{
	$innerJS .= <<< JS
	akeeba.Backup.start();

JS;
}
else
{
	$innerJS .= <<< JS
	
	// Bind start button's click event
	akeeba.System.addEventListener(document.getElementById('backup-start'), 'click', function(e){
		akeeba.Backup.start();
	});

	akeeba.System.addEventListener(document.getElementById('backup-default'), 'click', akeeba.Backup.restoreDefaultOptions);

	// Work around Safari which ignores autocomplete=off (FOR CRYING OUT LOUD!)
	setTimeout('akeeba.Backup.restoreCurrentOptions();', 500);

JS;
}

$js = <<< JS

;// This comment is intentionally put here to prevent badly written plugins from causing a Javascript error
// due to missing trailing semicolon and/or newline in their code.
akeeba.System.documentReady(function(){
	$innerJS
});

JS;

$this->getContainer()->template->addJSInline($js);

?>
