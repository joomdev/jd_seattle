<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\View\Log;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Backup\Admin\Model\Log;
use Akeeba\Backup\Admin\View\ViewTraits\ProfileIdAndName;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use FOF30\View\DataView\Html as BaseView;
use JHtml;

/**
 * View controller for the Log Viewer page
 */
class Html extends BaseView
{
	use ProfileIdAndName;

	/**
	 * JHtml list of available log files
	 *
	 * @var  array
	 */
	public $logs = [];

	/**
	 * Currently selected log file tag
	 *
	 * @var  string
	 */
	public $tag;

	/**
	 * Is the select log too big for being
	 *
	 * @var bool
	 */
	public $logTooBig = false;

	/**
	 * Size of the log file
	 * 
	 * @var int
	 */
	public $logSize = 0;

	/**
	 * Big log file threshold: 2Mb
	 */
	const bigLogSize = 2097152;

	/**
	 * The main page of the log viewer. It allows you to select a profile to display. When you do it displays the IFRAME
	 * with the actual log content and the button to download the raw log file.
	 *
	 * @return  void
	 */
	public function onBeforeMain()
	{
		// Get a list of log names
		/** @var Log $model */
		$model      = $this->getModel();
		$this->logs = $model->getLogList();

		$tag = $model->getState('tag');

		if (empty($tag))
		{
			$tag = null;
		}

		$this->tag = $tag;

		// Let's check if the file is too big to display
		if ($this->tag)
		{
			$file = Factory::getLog()->getLogFilename($this->tag);

			if (@file_exists($file))
			{
				$this->logSize   = filesize($file);
				$this->logTooBig = ($this->logSize >= self::bigLogSize);
			}
		}

		if ($this->logTooBig)
		{
			$src = 'index.php?option=com_akeeba&view=Log&task=download&attachment=0&tag=' . urlencode($this->tag);
			$js  = <<<JS

;// Prevent broken 3PD Javascript from causing errors
akeeba.System.documentReady(function (){
	akeeba.System.addEventListener(document.getElementById('showlog'), 'click', function(){
		var iFrameHolder = document.getElementById('iframe-holder');
		iFrameHolder.style.display = 'block';
		iFrameHolder.insertAdjacentHTML('beforeend', '<iframe width="99%" src="$src" height="400px"/>');
		this.parentNode.style.display = 'none';
    });
});

JS;

			$this->addJavascriptInline($js);
		}


		$this->getProfileIdAndName();
	}
}
