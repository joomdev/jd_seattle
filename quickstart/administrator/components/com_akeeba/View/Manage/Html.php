<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\View\Manage;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Backup\Admin\Model\Profiles;
use Akeeba\Backup\Admin\Model\Statistics;
use Akeeba\Engine\Platform;
use DateTimeZone;
use FOF30\Date\Date;
use FOF30\View\DataView\Html as BaseView;
use JFactory;
use JHtml;
use JLoader;
use JText;
use JUri;

/**
 * View controller for the Backup Now page
 */
class Html extends BaseView
{
	/**
	 * Should I use the user's local time zone for display?
	 *
	 * @var  boolean
	 */
	public $useLocalTime;

	/**
	 * Time format string to use for the time zone suffix
	 *
	 * @var  string
	 */
	public $timeZoneFormat;

	/**
	 * The backup record for the showcomment view
	 *
	 * @var  array
	 */
	public $record = [];

	/**
	 * The backup record ID for the showcomment view
	 *
	 * @var  int
	 */
	public $record_id = 0;

	/**
	 * List of Profiles objects
	 *
	 * @var  array
	 */
	public $profiles = array();

	/**
	 * List of profiles for JHtmlSelect
	 *
	 * @var  array
	 */
	public $profilesList = [];

	/**
	 * Order column
	 *
	 * @var  string
	 */
	public $order = 'backupstart';

	/**
	 * Order direction, ASC/DESC
	 *
	 * @var  string
	 */
	public $order_Dir = 'DESC';

	/**
	 * Description filter
	 *
	 * @var string
	 */
	public $fltDescription = '';

	/**
	 * From date filter
	 *
	 * @var  string
	 */
	public $fltFrom = '';

	/**
	 * To date filter
	 *
	 * @var  string
	 */
	public $fltTo = '';

	/**
	 * Origin filter
	 *
	 * @var  string
	 */
	public $fltOrigin = '';

	/**
	 * Profile filter
	 *
	 * @var  string
	 */
	public $fltProfile = '';

	/**
	 * List of records to display
	 *
	 * @var  array
	 */
	public $list = [];

	/**
	 * Pagination object
	 *
	 * @var \JPagination
	 */
	public $pagination = null;

	/**
	 * Date format for the backup start time
	 *
	 * @var  string
	 */
	public $dateFormat = '';

	/**
	 * Should I pormpt the user ot run the configuration wizard?
	 *
	 * @var  bool
	 */
	public $promptForBackupRestoration = false;

	/**
	 * Sorting order options
	 *
	 * @var  array
	 */
	public $sortFields = [];

	/**
	 * Cache the user permissions
	 *
	 * @var   array
	 *
	 * @since 5.3.0
	 */
	public $permissions = array();

	/**
	 * List the backup records
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 */
	public function onBeforeMain()
	{
		// Load custom Javascript for this page
		$this->addJavascriptFile('media://com_akeeba/js/Manage.min.js');

		// Load core classes used in the view template
		JLoader::import('joomla.utilities.date');

		$user              = $this->container->platform->getUser();
		$this->permissions = array(
			'configure' => $user->authorise('akeeba.configure', 'com_akeeba'),
			'backup'    => $user->authorise('akeeba.backup', 'com_akeeba'),
			'download'  => $user->authorise('akeeba.download', 'com_akeeba'),
		);


		/** @var Profiles $profilesModel */
		$profilesModel           = $this->container->factory->model('Profiles')->tmpInstance();
		$enginesPerPprofile      = $profilesModel->getPostProcessingEnginePerProfile();
		$this->enginesPerProfile = $enginesPerPprofile;

		// "Show warning first" download button.
		$confirmationText = JText::_('COM_AKEEBA_BUADMIN_LOG_DOWNLOAD_CONFIRM', true, false);
		$confirmationText = str_replace('\\\\n', '\\n', $confirmationText);
		$baseURI          = JUri::base();
		$js               = <<<JS

;// This comment is intentionally put here to prevent badly written plugins from causing a Javascript error
// due to missing trailing semicolon and/or newline in their code.
function confirmDownloadButton()
{
	var answer = confirm("$confirmationText");
	if (answer)
	{
		submitbutton('download')
	}
}

function confirmDownload(id, part)
{
	var answer = confirm("$confirmationText");
	var newURL = '$baseURI';
	if (answer)
	{
		newURL += 'index.php?option=com_akeeba&view=Manage&task=download&id='+id;
		
		if (part != '')
		{
			newURL += '&part=' + part
		}
		
		window.location = newURL;
	}
}

akeeba.System.documentReady(function(){
	akeeba.Tooltip.enableFor(document.querySelectorAll('.akeebaCommentPopover'), false);
});

JS;
		$this->addJavascriptInline($js);

		JHtml::_('behavior.calendar');

		$hash = 'akeebamanage';

		// ...ordering
		$platform        = $this->container->platform;
		$input           = $this->input;
		$this->order     = $platform->getUserStateFromRequest($hash . 'filter_order', 'filter_order', $input, 'backupstart');
		$this->order_Dir = $platform->getUserStateFromRequest($hash . 'filter_order_Dir', 'filter_order_Dir', $input, 'DESC');

		// ...filter state
		$this->fltDescription = $platform->getUserStateFromRequest($hash . 'filter_description', 'description', $input, '');
		$this->fltFrom        = $platform->getUserStateFromRequest($hash . 'filter_from', 'from', $input, '');
		$this->fltTo          = $platform->getUserStateFromRequest($hash . 'filter_to', 'to', $input, '');
		$this->fltOrigin      = $platform->getUserStateFromRequest($hash . 'filter_origin', 'origin', $input, '');
		$this->fltProfile     = $platform->getUserStateFromRequest($hash . 'filter_profile', 'profile', $input, '');

		$filters  = $this->getFilters();
		$ordering = $this->getOrdering();

		/** @var Statistics $model */
		$model = $this->getModel();
		$list  = $model->getStatisticsListWithMeta(false, $filters, $ordering);

		// Let's create an array indexed with the profile id for better handling
		$profiles = $profilesModel->get(true);

		$profilesList = array(
			JHtml::_('select.option', '', '–' . JText::_('COM_AKEEBA_BUADMIN_LABEL_PROFILEID') . '–')
		);

		if (!empty($profiles))
		{
			foreach ($profiles as $profile)
			{
				$profilesList[] = JHtml::_('select.option', $profile->id, '#' . $profile->id . '. ' . $profile->description);
			}
		}

		// Assign data to the view
		$this->profiles     = $profiles; // Profiles
		$this->profilesList = $profilesList; // Profiles list for select box
		$this->list         = $list; // Data
		$this->pagination   = $model->getPagination($filters); // Pagination object

		// Date format
		$dateFormat       = $this->container->params->get('dateformat', '');
		$dateFormat       = trim($dateFormat);
		$this->dateFormat = !empty($dateFormat) ? $dateFormat : JText::_('DATE_FORMAT_LC4');

		// Time zone options
		$this->useLocalTime   = $this->container->params->get('localtime', '1') == 1;
		$this->timeZoneFormat = $this->container->params->get('timezonetext', 'T');

		// Should I show the prompt for the configuration wizard?
		$this->promptForBackupRestoration = $this->container->params->get('show_howtorestoremodal', 1) != 0;

		// Construct the array of sorting fields
		$this->sortFields = array(
			'id'          => JText::_('COM_AKEEBA_BUADMIN_LABEL_ID'),
			'description' => JText::_('COM_AKEEBA_BUADMIN_LABEL_DESCRIPTION'),
			'backupstart' => JText::_('COM_AKEEBA_BUADMIN_LABEL_START'),
			'profile_id'  => JText::_('COM_AKEEBA_BUADMIN_LABEL_PROFILEID'),
		);
	}

	/**
	 * Edit a backup record's description and comment
	 *
	 * @return  void
	 */
	public function onBeforeShowcomment()
	{
		/** @var Statistics $model */
		$model           = $this->getModel();
		$id              = $model->getState('id', 0);
		$record          = Platform::getInstance()->get_statistics($id);
		$this->record    = $record;
		$this->record_id = $id;

		$this->setLayout('comment');
	}

	/**
	 * File size formatting function. COnverts number of bytes to a human readable represenation.
	 *
	 * @param   int    $sizeInBytes        Size in bytes
	 * @param   int    $decimals           How many decimals should I use? Default: 2
	 * @param   string $decSeparator       Decimal separator
	 * @param   string $thousandsSeparator Thousands grouping character
	 *
	 * @return string
	 */
	protected function formatFilesize($sizeInBytes, $decimals = 2, $decSeparator = '.', $thousandsSeparator = '')
	{
		if ($sizeInBytes <= 0)
		{
			return '-';
		}

		$units = array('b', 'KB', 'MB', 'GB', 'TB');
		$unit  = floor(log($sizeInBytes, 2) / 10);

		if ($unit == 0)
		{
			$decimals = 0;
		}

		return number_format($sizeInBytes / pow(1024, $unit), $decimals, $decSeparator, $thousandsSeparator) . ' ' . $units[$unit];
	}

	/**
	 * Translates the internal backup type (e.g. cli) to a human readable string
	 *
	 * @param   string $recordType The internal backup type
	 *
	 * @return  string
	 */
	protected function translateBackupType($recordType)
	{
		static $backup_types = null;

		if (!is_array($backup_types))
		{
			// Load a mapping of backup types to textual representation
			$scripting    = \Akeeba\Engine\Factory::getEngineParamsProvider()->loadScripting();
			$backup_types = array();
			foreach ($scripting['scripts'] as $key => $data)
			{
				$backup_types[$key] = JText::_($data['text']);
			}
		}

		if (array_key_exists($recordType, $backup_types))
		{
			return $backup_types[$recordType];
		}

		return '&ndash;';
	}

	/**
	 * Returns the origin's translated name and the appropriate icon class
	 *
	 * @param   array $record A backup record
	 *
	 * @return  array  array(originTranslation, iconClass)
	 */
	protected function getOriginInformation($record)
	{
		$originLanguageKey = 'COM_AKEEBA_BUADMIN_LABEL_ORIGIN_' . $record['origin'];
		$originDescription = JText::_($originLanguageKey);

		switch (strtolower($record['origin']))
		{
			case 'backend':
				$originIcon = 'akion-android-desktop';
				break;

			case 'frontend':
				$originIcon = 'akion-ios-world';
				break;

			case 'json':
				$originIcon = 'akion-android-cloud';
				break;

			case 'cli':
				$originIcon = 'akion-ios-paper-outline';
				break;

			case 'xmlrpc':
				$originIcon = 'akion-code';
				break;

			case 'restorepoint':
				$originIcon = 'akion-refresh';
				break;

			case 'lazy':
				$originIcon = 'akion-cube';
				break;

			default:
				$originIcon = 'akion-help';
				break;
		}

		if (empty($originLanguageKey) || ($originDescription == $originLanguageKey))
		{
			$originDescription = '&ndash;';
			$originIcon        = 'akion-help';

			return array($originDescription, $originIcon);
		}

		return array($originDescription, $originIcon);
	}

	/**
	 * Get the start time and duration of a backup record
	 *
	 * @param   array $record A backup record
	 *
	 * @return  array  array(startTimeAsString, durationAsString)
	 */
	protected function getTimeInformation($record)
	{
		$utcTimeZone = new DateTimeZone('UTC');
		$startTime   = new Date($record['backupstart'], $utcTimeZone);
		$endTime     = new Date($record['backupend'], $utcTimeZone);

		$duration = $endTime->toUnix() - $startTime->toUnix();

		if ($duration > 0)
		{
			$seconds  = $duration % 60;
			$duration = $duration - $seconds;

			$minutes  = ($duration % 3600) / 60;
			$duration = $duration - $minutes * 60;

			$hours    = $duration / 3600;
			$duration = sprintf('%02d', $hours) . ':' . sprintf('%02d', $minutes) . ':' . sprintf('%02d', $seconds);
		}
		else
		{
			$duration = '';
		}

		$user   = $this->container->platform->getUser();
		$userTZ = $user->getParam('timezone', 'UTC');
		$tz     = new DateTimeZone($userTZ);
		$startTime->setTimezone($tz);

		$timeZoneSuffix = '';

		if (!empty($this->timeZoneFormat))
		{
			$timeZoneSuffix = $startTime->format($this->timeZoneFormat, $this->useLocalTime);
		}

		return array(
			$startTime->format($this->dateFormat, $this->useLocalTime),
			$duration,
			$timeZoneSuffix
		);
	}

	/**
	 * Get the class and icon for the backup status indicator
	 *
	 * @param   array $record A backup record
	 *
	 * @return  array  array(class, icon)
	 */
	protected function getStatusInformation($record)
	{
		$statusClass = '';

		switch ($record['meta'])
		{
			case 'ok':
				$statusIcon  = 'akion-checkmark';
				$statusClass = 'akeeba-label--green';
				break;
			case 'pending':
				$statusIcon  = 'akion-play';
				$statusClass = 'akeeba-label--orange';
				break;
			case 'fail':
				$statusIcon  = 'akion-android-cancel';
				$statusClass = 'akeeba-label--red';
				break;
			case 'remote':
				$statusIcon  = 'akion-cloud';
				$statusClass = 'akeeba-label--teal';
				break;
			default:
				$statusIcon = 'akion-trash-a';
				$statusClass = 'akeeba-label--grey';
				break;
		}

		return array($statusClass, $statusIcon);
	}

	/**
	 * Get the profile name for the backup record (or "–" if the profile no longer exists)
	 *
	 * @param   array $record A backup record
	 *
	 * @return  string
	 */
	protected function getProfileName($record)
	{
		$profileName = '&mdash;';

		if (isset($this->profiles[$record['profile_id']]))
		{
			$profileName = $this->escape($this->profiles[$record['profile_id']]->description);

			return $profileName;
		}

		return $profileName;
	}

	/**
	 * Get the filters in a format that Akeeba Engine understands
	 *
	 * @return  array
	 */
	private function getFilters()
	{
		$filters = array();

		if ($this->fltDescription)
		{
			$filters[] = array(
				'field'   => 'description',
				'operand' => 'LIKE',
				'value'   => $this->fltDescription
			);
		}

		if ($this->fltFrom && $this->fltTo)
		{
			$filters[] = array(
				'field'   => 'backupstart',
				'operand' => 'BETWEEN',
				'value'   => $this->fltFrom,
				'value2'  => $this->fltTo
			);
		}
		elseif ($this->fltFrom)
		{
			$filters[] = array(
				'field'   => 'backupstart',
				'operand' => '>=',
				'value'   => $this->fltFrom,
			);
		}
		elseif ($this->fltTo)
		{
			JLoader::import('joomla.utilities.date');
			$toDate = new Date($this->fltTo);
			$to = $toDate->format('Y-m-d') . ' 23:59:59';

			$filters[] = array(
				'field'   => 'backupstart',
				'operand' => '<=',
				'value'   => $to,
			);
		}
		if ($this->fltOrigin)
		{
			$filters[] = array(
				'field'   => 'origin',
				'operand' => '=',
				'value'   => $this->fltOrigin
			);
		}
		if ($this->fltProfile)
		{
			$filters[] = array(
				'field'   => 'profile_id',
				'operand' => '=',
				'value'   => (int) $this->fltProfile
			);
		}

		$filters[] = array(
			'field'   => 'tag',
			'operand' => '<>',
			'value'   => 'restorepoint'
		);


		if (empty($filters))
		{
			$filters = null;
		}

		return $filters;
	}

	/**
	 * Get the list ordering in a format that Akeeba Engine understands
	 *
	 * @return  array
	 */
	private function getOrdering()
	{
		$order = array(
			'by'    => $this->order,
			'order' => strtoupper($this->order_Dir)
		);

		return $order;
	}
}
