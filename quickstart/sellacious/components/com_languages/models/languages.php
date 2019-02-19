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
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Language\LanguageHelper;
use Sellacious\Language\LanguagePack;

defined('_JEXEC') or die;

/**
 * Languages Model Class
 *
 * @since  1.6.0
 */
class LanguagesModelLanguages extends SellaciousModelList
{
	/**
	 * Records count
	 *
	 * @var   int
	 *
	 * @since   1.6.0
	 */
	protected $languageCount = 0;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'sa.id',
				'lang_code', 'a.lang_code',
				'title', 'a.title',
				'title_native', 'a.title_native',
				'sef', 'a.sef',
				'image', 'a.image',
				'state', 'a.state',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.0
	 */
	public function getItems()
	{
		$store = $this->getStoreId();

		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		try
		{
			$languages = array();
			$search    = strtolower($this->getState('filter.search'));
			$state     = $this->getState('list.state', '0');

			// Fetch installed languages
			$installed = $this->getInstalledLanguages();

			foreach ($installed as $code => $language)
			{
				if ($state == '0' || $state == '1')
				{
					if ($search)
					{
						if (strpos(strtolower($language->title), $search) === false &&
							strpos(strtolower($language->title_native), $search) === false &&
							strpos(strtolower($language->lang_code), $search) === false
						)
						{
							continue;
						}
					}

					$languages[$code] = $language;
				}
			}

			if ($state == '0' || $state == '-1')
			{
				// Fetch installable languages
				$available = $this->getAvailableLanguages();

				foreach ($available as $language)
				{
					if (!array_key_exists($language->lang_code, $installed))
					{
						if ($search)
						{
							if (strpos(strtolower($language->title), $search) === false &&
								strpos(strtolower($language->title_native), $search) === false &&
								strpos(strtolower($language->lang_code), $search) === false)
							{
								continue;
							}
						}

						$languages[$language->lang_code] = $language;
					}
					elseif (isset($languages[$language->lang_code]))
					{
						$languages[$language->lang_code]->params['install_url'] = $language->params['install_url'];
					}
				}
			}

			$ordering  = $this->getState('list.ordering') ?: array('state', 'title');
			$direction = $this->getState('list.direction');
			$direction = ArrayHelper::getValue(array('desc' => -1, 'asc' => 1), strtolower($direction), array(-1, 1));
			$languages = ArrayHelper::sortObjects($languages, $ordering, $direction);

			// Count the non-paginated list
			$this->languageCount = count($languages);

			$limit = ($this->getState('list.limit') > 0) ? $this->getState('list.limit') : $this->languageCount;

			$languages = array_slice($languages, $this->getStart(), $limit);

			$this->cache[$store] = $languages;
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return $this->cache[$store];
	}

	/**
	 * Returns a record count for the updateSite.
	 *
	 * @param   JDatabaseQuery|string  $query  The query.
	 *
	 * @return  integer  Number of rows for query.
	 *
	 * @since   1.6.0
	 */
	protected function _getListCount($query)
	{
		return $this->languageCount;
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');

		return parent::getStoreId($id);
	}

	/**
	 * Method to get an array of languages that are already installed.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.0
	 */
	protected function getInstalledLanguages()
	{
		$languages = array();
		$installed = LanguageHelper::getInstalledLanguages(null, true, true);

		foreach ($installed as $clientId => $installedLanguages)
		{
			foreach ($installedLanguages as $code => $lang)
			{
				if (is_array($lang))
				{
					foreach ($lang as $langItem)
					{
						$this->pushLanguage($languages, $langItem, $clientId);
					}
				}
				else
				{
					$this->pushLanguage($languages, $lang, $clientId);
				}
			}
		}

		return $languages;
	}

	/**
	 * Gets an array of objects from the updateSite.
	 *
	 * @return  object[]  An array of results.
	 *
	 * @throws  RuntimeException
	 *
	 * @since   1.6.0
	 */
	protected function getAvailableLanguages()
	{
		$languages  = array();
		$updateSite = $this->getUpdateSite();

		try
		{
			$http     = new JHttp;
			$response = $http->get($updateSite);
		}
		catch (RuntimeException $e)
		{
			$response = null;
		}

		if ($response === null || $response->code !== 200)
		{
			$this->app->enqueueMessage(JText::_('COM_LANGUAGES_INSTALL_MSG_WARNING_NO_LANGUAGES_UPDATESERVER'), 'warning');

			return $languages;
		}

		$xml = simplexml_load_string($response->body);

		if (!$xml instanceof SimpleXMLElement)
		{
			$this->app->enqueueMessage(JText::_('COM_LANGUAGES_INSTALL_MSG_WARNING_ERROR_LANGUAGES_UPDATESERVER'), 'warning');

			return $languages;
		}

		foreach ($xml->extension as $extension)
		{
			if (!preg_match('#^pkg_([a-z]{2,3}-[A-Z]{2})$#', $extension['element'], $element) || !isset($element[1]))
			{
				continue;
			}

			$code      = $element[1];
			$nLanguage = array(
				'id'            => null,
				'lang_code'     => $code,
				'title'         => $extension['name'],
				'title_native'  => $extension['name'],
				'sef'           => substr($code, 0, strpos($code, '-')),
				'image'         => strtolower(str_replace('-', '_', $code)),
				'description'   => '',
				'sitename'      => '',
				'state'         => '-1',
				'ordering'      => '0',
				'params'        => array(
					'rtl'         => null,
					'week_start'  => null,
					'weekend'     => null,
					'calendar'    => null,
					'install_url' => (string) $extension['detailsurl'],
					'version'     => (string) $extension['version'],
				),
				'site'          => false,
				'administrator' => false,
				'sellacious'    => false,
			);

			$languages[$code] = (object) $nLanguage;
		}

		return $languages;
	}

	/**
	 * Get the Update Site
	 *
	 * @return  string  The URL of the Accredited Languagepack Update site XML
	 *
	 * @since   1.6.0
	 */
	private function getUpdateSite()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('us.location'))
			->from($db->qn('#__extensions', 'e'))
			->where($db->qn('e.type') . ' = ' . $db->q('package'))
			->where($db->qn('e.element') . ' = ' . $db->q('pkg_en-GB'))
			->where($db->qn('e.client_id') . ' = 0')
			->join('LEFT', $db->qn('#__update_sites_extensions', 'use') . ' ON ' . $db->qn('use.extension_id') . ' = ' . $db->qn('e.extension_id'))
			->join('LEFT', $db->qn('#__update_sites', 'us') . ' ON ' . $db->qn('us.update_site_id') . ' = ' . $db->qn('use.update_site_id'));

		return $db->setQuery($query)->loadResult();
	}

	/**
	 * Get the id for the content language for this language
	 *
	 * @param   stdClass  $lang  The language
	 *
	 * @return  int
	 *
	 * @since   1.6.0
	 */
	private function getContentLanguageId($lang)
	{
		$query = $this->_db->getQuery(true);

		$query->select('lang_id')->from('#__languages')->where('lang_code = ' . $this->_db->q($lang->lang_code));

		$langId = $this->_db->setQuery($query)->loadResult();

		if (!$langId)
		{
			$pack   = new LanguagePack($lang);
			$langId = $pack->installContentLanguage();
		}

		return $langId;
	}

	/**
	 * Process a language object to populate it into the list of languages for the view
	 *
	 * @param   stdClass[]  $languages  The list of languages to which this language will be pushed
	 * @param   stdClass    $lang       The language object
	 * @param   int         $clientId   The language client id
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function pushLanguage(array &$languages, $lang, $clientId)
	{
		$cLanguage = new Registry($lang);
		$langTag   = $cLanguage->get('metadata.tag');
		$langName  = $cLanguage->get('manifest.name', $cLanguage->get('name'));
		$oLanguage = array_key_exists($langTag, $languages) ? new Registry($languages[$langTag]) : new Registry;
		$nLanguage = (object) array(
			'id'            => $oLanguage->get('id'),
			'lang_code'     => $oLanguage->get('lang_code') ?: $langTag,
			'title'         => $oLanguage->get('title') ?: $langName,
			'title_native'  => $oLanguage->get('title_native') ?: $cLanguage->get('metadata.nativeName', $langName),
			'sef'           => $oLanguage->get('sef') ?: substr($langTag, 0, strpos($langTag, '-')),
			'image'         => $oLanguage->get('image') ?: strtolower(str_replace('-', '_', $langTag)),
			'description'   => $oLanguage->get('description') ?:  $cLanguage->get('manifest.description'),
			'sitename'      => $oLanguage->get('sitename'),
			'state'         => $oLanguage->get('state', '1'),
			'ordering'      => $oLanguage->get('ordering', '0'),
			'params'        => array(
				'rtl'        => $oLanguage->get('params.rtl') ?: $cLanguage->get('metadata.rtl', 0),
				'week_start' => $oLanguage->get('params.week_start') ?: $cLanguage->get('metadata.firstDay', 0),
				'weekend'    => $oLanguage->get('params.weekend') ?: $cLanguage->get('metadata.weekEnd'),
				'calendar'   => $oLanguage->def('params.calendar') ?: $cLanguage->get('metadata.calendar', 'gregorian'),
			),
			'site'          => $oLanguage->def('site'),
			'administrator' => $oLanguage->def('administrator'),
			'sellacious'    => $oLanguage->def('sellacious'),
		);

		$nLanguage->id = $this->getContentLanguageId($nLanguage);

		if ($clientId == 0)
		{
			$nLanguage->site = true;
		}
		elseif ($clientId == 1)
		{
			$nLanguage->administrator = true;
		}
		elseif ($clientId == 2)
		{
			$nLanguage->sellacious = true;
		}

		$languages[$langTag] = $nLanguage;
	}
}
