<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access.
namespace Sellacious\Language;

use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Languages Pack Object to help generate a joomla/sellacious language pack automatically
 *
 * @since  1.6.0
 */
class LanguagePack
{
	/**
	 * @var  int
	 *
	 * @since   1.6.0
	 */
	protected $package_id;

	/**
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $language_code;

	/**
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $language_name;

	/**
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $language_image;

	/**
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $native_title;

	/**
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $app_version;

	/**
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $created_date;

	/**
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $author_name;

	/**
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $author_email;

	/**
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $author_url;

	/**
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $package_description;

	/**
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $language_rtl;

	/**
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $language_url_tag;

	/**
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $language_first_day;

	/**
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $language_weekend_days;

	/**
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $language_calendar;

	/**
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $localise_class_name;

	/**
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $manifest_path;

	/**
	 * LanguagePack constructor.
	 *
	 * @param   \stdClass  $lang  The language record object
	 *
	 * @since   1.6.0
	 */
	public function __construct($lang)
	{
		$params = isset($lang->params) ? new Registry($lang->params) : new Registry;

		$this->language_code         = $lang->lang_code;
		$this->language_name         = $lang->title;
		$this->language_image        = $lang->image ?: strtolower(str_replace('-', '_', $lang->lang_code));
		$this->native_title          = $lang->title_native;
		$this->app_version           = '1.0.0';
		$this->created_date          = \JFactory::getDate()->format('F d, Y');
		$this->author_name           = \JFactory::getUser()->name;
		$this->author_email          = \JFactory::getUser()->email;
		$this->author_url            = \JUri::getInstance()->toString(array('scheme', 'host'));
		$this->package_description   = $lang->description;
		$this->language_rtl          = $params->get('rtl', '0');
		$this->language_url_tag      = $lang->sef;
		$this->language_first_day    = $params->get('week_start', 1);
		$this->language_weekend_days = implode(',', (array) $params->get('weekend', array(0, 6)));
		$this->language_calendar     = $params->get('calendar', 'gregorian');
		$this->localise_class_name   = str_replace('-', '_', ucfirst($lang->lang_code)) . 'Localise';
		$this->manifest_path         = JPATH_MANIFESTS . '/packages/pkg_' . $lang->lang_code . '.xml';
	}

	/**
	 * Create and install the language pack.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function install()
	{
		$this->installPackage();

		if ($this->package_id)
		{
			$clients = \JApplicationHelper::getClientInfo();

			foreach ($clients as $client)
			{
				$this->installClientLanguage($client);
			}

			$this->installContentLanguage();
		}
	}

	/**
	 * Install the language package
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function installPackage()
	{
		if (!$this->installPackageManifest())
		{
			return;
		}

		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.extension_id')
			->from($db->qn('#__extensions', 'a'))
			->where('a.type = ' . $db->q('package'))
			->where('a.element = ' . $db->q("pkg_{$this->language_code}"));

		$extId = $db->setQuery($query)->loadResult();

		if ($extId)
		{
			$this->package_id = $extId;
		}
		else
		{
			$extension = (object) array(
				'extension_id'     => null,
				'package_id'       => '0',
				'name'             => "{$this->language_name} Language Package",
				'type'             => 'package',
				'element'          => "pkg_{$this->language_code}",
				'folder'           => '',
				'client_id'        => '0',
				'enabled'          => '1',
				'access'           => '1',
				'protected'        => '0',
				'manifest_cache'   => json_encode(array(
					'name'         => "{$this->language_name} Language Package",
					'type'         => 'package',
					'creationDate' => $this->created_date,
					'author'       => $this->author_name,
					'copyright'    => 'Copyright (C) All rights reserved.',
					'authorEmail'  => $this->author_email,
					'authorUrl'    => $this->author_url,
					'version'      => $this->app_version,
					'description'  => $this->package_description,
					'group'        => '',
					'filename'     => "pkg_{$this->language_code}",
				)),
				'params'           => '',
				'custom_data'      => '',
				'system_data'      => '',
				'checked_out'      => '0',
				'checked_out_time' => '0000-00-00 00:00:00',
				'ordering'         => '0',
				'state'            => '0',
			);

			if ($db->insertObject('#__extensions', $extension, 'extension_id'))
			{
				$this->package_id = $extension->extension_id;
			}
		}
	}

	/**
	 * Install the language package manifest
	 *
	 * @return   bool
	 *
	 * @since    1.6.0
	 */
	protected function installPackageManifest()
	{
		if (!is_file($this->manifest_path))
		{
			$manifest = <<<XMLP
<?xml version="1.0" encoding="UTF-8" ?>
<extension type="package" version="3.8" method="upgrade">
	<name>{$this->language_name} Language Package</name>
	<packagename>{$this->language_code}</packagename>
	<version>{$this->app_version}</version>
	<creationDate>{$this->created_date}</creationDate>
	<author>{$this->author_name}</author>
	<authorEmail>{$this->author_email}</authorEmail>
	<authorUrl>{$this->author_url}</authorUrl>
	<copyright>Copyright (C) All rights reserved.</copyright>
	<license>GNU General Public License version 2 or later; see LICENSE.txt</license>
	<description>{$this->package_description}</description>
	<blockChildUninstall>true</blockChildUninstall>
	<files>
		<file type="language" client="administrator" id="{$this->language_code}">administrator_{$this->language_code}.zip</file>
		<file type="language" client="sellacious" id="{$this->language_code}">sellacious_{$this->language_code}.zip</file>
		<file type="language" client="site" id="{$this->language_code}">site_{$this->language_code}.zip</file>
	</files>
</extension>
XMLP;

			jimport('joomla.filesystem.folder');

			\JFolder::create(dirname($this->manifest_path));

			return (bool) file_put_contents($this->manifest_path, $manifest);
		}

		return true;
	}

	/**
	 * Install the required files for application client for language pack installation
	 *
	 * @param   \stdClass  $client  The application client info
	 *
	 * @return  bool
	 *
	 * @since   1.6.0
	 *
	 * @see     JApplicationHelper::getClientInfo()
	 */
	protected function installClientLanguage($client)
	{
		// Process install files first
		$files = $this->getLanguageInstallFiles($client);
		$dir   = $client->path . '/language/' . $this->language_code;

		jimport('joomla.filesystem.folder');

		\JFolder::create($dir);

		foreach ($files as $file => $content)
		{
			if (!is_file($dir . '/' . $file) || filesize($dir . '/' . $file) < 25)
			{
				if (false === file_put_contents($dir . '/' . $file, $content))
				{
					return false;
				}
			}
		}

		// Install into the database
		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.extension_id')
			->from($db->qn('#__extensions', 'a'))
			->where('a.client_id = ' . (int) $client->id)
			->where('a.type = ' . $db->q('language'))
			->where('a.element = ' . $db->q($this->language_code));

		$extId = $db->setQuery($query)->loadResult();

		if (!$extId)
		{
			$clientTitle = ucfirst(strtolower($client->name));
			$extension   = (object) array(
				'extension_id'     => null,
				'package_id'       => $this->package_id,
				'name'             => "{$this->language_name} ({$this->language_code})",
				'type'             => 'language',
				'element'          => $this->language_code,
				'folder'           => '',
				'client_id'        => (int) $client->id,
				'enabled'          => '1',
				'access'           => '1',
				'protected'        => '0',
				'manifest_cache'   => json_encode(array(
					'name'         => "{$this->language_name}",
					'type'         => 'language',
					'creationDate' => $this->created_date,
					'author'       => $this->author_name,
					'copyright'    => 'Copyright (C) All rights reserved.',
					'authorEmail'  => $this->author_email,
					'authorUrl'    => $this->author_url,
					'version'      => $this->app_version,
					'description'  => "{$this->language_name} {$clientTitle} language",
					'group'        => '',
				)),
				'params'           => '',
				'custom_data'      => '',
				'system_data'      => '',
				'checked_out'      => '0',
				'checked_out_time' => '0000-00-00 00:00:00',
				'ordering'         => '0',
				'state'            => '0',
			);

			$db->insertObject('#__extensions', $extension, 'extension_id');
		}

		return true;
	}

	/**
	 * Get the required files for application client for language pack installation
	 *
	 * @param   \stdClass  $client  The application client info
	 *
	 * @return  array  The associative array of filename and file content
	 *
	 * @since   1.6.0
	 *
	 * @see     JApplicationHelper::getClientInfo()
	 */
	protected function getLanguageInstallFiles($client)
	{
		$clientTitle = ucfirst(strtolower($client->name));

		$install = <<<XMLI
<?xml version="1.0" encoding="utf-8"?>
<extension version="3.8" client="{$client->name}" type="language" method="upgrade">
	<name>{$this->language_name}</name>
	<tag>{$this->language_code}</tag>
	<version>{$this->app_version}</version>
	<creationDate>{$this->created_date}</creationDate>
	<author>{$this->author_name}</author>
	<authorEmail>{$this->author_email}</authorEmail>
	<authorUrl>{$this->author_url}</authorUrl>
	<copyright>Copyright (C) All rights reserved.</copyright>
	<license>GNU General Public License version 2 or later; see LICENSE.txt</license>
	<description>{$this->language_name} {$clientTitle} language</description>
	<files>
		<filename>{$this->language_code}.localise.php</filename>
		<filename file="meta">install.xml</filename>
		<filename file="meta">{$this->language_code}.xml</filename>
		<filename>index.html</filename>
	</files>
	<params />
</extension>
XMLI;

		$localise = <<<PHP
<?php
/**
 * @package    Joomla.Language
 *
 * @copyright  Copyright (C) All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Language localise class.
 *
 * @since  1.6
 */
abstract class {$this->localise_class_name}
{
	/**
	 * Returns the potential suffixes for a specific number of items
	 *
	 * @param   integer  \$count  The number of items.
	 *
	 * @return  array  An array of potential suffixes.
	 *
	 * @since   1.6
	 */
	public static function getPluralSuffixes(\$count)
	{
		if (\$count == 0)
		{
			return array('0');
		}
		elseif (\$count == 1)
		{
			return array('1');
		}
		else
		{
			return array('MORE');
		}
	}

	/**
	 * Returns the ignored search words
	 *
	 * @return  array  An array of ignored search words.
	 *
	 * @since   1.6
	 */
	public static function getIgnoredSearchWords()
	{
		return array();
	}

	/**
	 * Returns the lower length limit of search words
	 *
	 * @return  integer  The lower length limit of search words.
	 *
	 * @since   1.6
	 */
	public static function getLowerLimitSearchWord()
	{
		return 3;
	}

	/**
	 * Returns the upper length limit of search words
	 *
	 * @return  integer  The upper length limit of search words.
	 *
	 * @since   1.6
	 */
	public static function getUpperLimitSearchWord()
	{
		return 20;
	}

	/**
	 * Returns the number of chars to display when searching
	 *
	 * @return  integer  The number of chars to display when searching.
	 *
	 * @since   1.6
	 */
	public static function getSearchDisplayedCharactersNumber()
	{
		return 200;
	}
}
PHP;

		$meta = <<<XMLM
<?xml version="1.0" encoding="utf-8"?>
<metafile version="3.8" client="{$client->name}">
	<tag>{$this->language_code}</tag>
	<name>{$this->language_name}</name>
	<version>{$this->app_version}</version>
	<creationDate>{$this->created_date}</creationDate>
	<author>{$this->author_name}</author>
	<authorEmail>{$this->author_email}</authorEmail>
	<authorUrl>{$this->author_url}</authorUrl>
	<copyright>Copyright (C) All rights reserved.</copyright>
	<license>GNU General Public License version 2 or later; see LICENSE.txt</license>
	<description>{$this->language_name} {$clientTitle} language</description>
	<metadata>
		<name>{$this->language_name}</name>
		<nativeName>{$this->language_name}</nativeName>
		<tag>{$this->language_code}</tag>
		<rtl>{$this->language_rtl}</rtl>
		<locale>{$this->language_code}.utf8, {$this->language_code}.UTF-8, {$this->language_code}, {$this->language_url_tag}</locale>
		<firstDay>{$this->language_first_day}</firstDay>
		<weekEnd>{$this->language_weekend_days}</weekEnd>
		<calendar>{$this->language_calendar}</calendar>
	</metadata>
	<params />
</metafile>
XMLM;

		$files = array(
			'install.xml'                         => $install,
			"{$this->language_code}.localise.php" => $localise,
			"{$this->language_code}.xml"          => $meta,
		);

		return $files;
	}

	/**
	 * Create a content language for language pack installed
	 *
	 * @return  int
	 *
	 * @since   1.6.0
	 */
	public function installContentLanguage()
	{
		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.lang_id')
			->from($db->qn('#__languages', 'a'))
			->where('a.lang_code = ' . $db->q($this->language_code));

		$langId = $db->setQuery($query)->loadResult();

		if (!$langId)
		{
			$language = (object) array(
				'lang_id'      => null,
				'asset_id'     => '0',
				'lang_code'    => $this->language_code,
				'title'        => $this->language_name,
				'title_native' => $this->native_title,
				'sef'          => $this->language_url_tag,
				'image'        => $this->language_image,
				'description'  => '',
				'metakey'      => '',
				'metadesc'     => '',
				'sitename'     => '',
				'published'    => '1',
				'access'       => '1',
				'ordering'     => '0',
			);

			$db->insertObject('#__languages', $language, 'lang_id');

			$langId = $language->lang_id;
		}
		else
		{
			$language = (object) array(
				'lang_id'   => $langId,
				'published' => '1',
			);

			$db->updateObject('#__languages', $language, array('lang_id'));
		}

		return $langId;
	}
}
