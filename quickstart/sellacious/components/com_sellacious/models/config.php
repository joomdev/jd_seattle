<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/**
 * Sellacious model.
 */
class SellaciousModelConfig extends SellaciousModelAdmin
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * @note   Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering
	 * @param   string  $direction
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$this->app->getUserStateFromRequest('com_sellacious.config.return', 'return', '', 'cmd');

		parent::populateState();
	}

	/**
	 * Method to save the form data
	 *
	 * @param   array  $data  The form data
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function save($data)
	{
		unset($data['tags']);

		foreach ($data as $name => $params)
		{
			$this->helper->config->save($params, $name);
		}

		return true;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  stdClass
	 *
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		// Todo: This should load all configurations which are allowed to edit here
		$params = $this->helper->config->getParams();

		if (!$this->helper->access->isSubscribed())
		{
			$params->set('show_brand_footer', 1);
			$params->set('show_rate_us', 1);
			$params->set('show_doc_link', 1);
			$params->set('show_support_link', 1);
			$params->set('show_advertisement', 1);
			$params->set('show_back_to_joomla', 1);
			$params->set('show_sellacious_version', 1);
			$params->set('show_license_to', 1);
		}

		$data = (object) array('com_sellacious' => $params->toArray());

		return $data;
	}

	/**
	 * Override preprocessForm to load the sellacious plugin group instead of content.
	 *
	 * @param   JForm   $form
	 * @param   mixed   $data
	 * @param   string  $group
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$this->helper->core->loadPlugins();

		$form->loadFile('config/shop');
		$form->loadFile('config/premium');
		$form->loadFile('config/layout');
		$form->loadFile('config/layout_admin');
		$form->loadFile('config/preset');
		$form->loadFile('config/sef');
		$form->loadFile('config/media');
		$form->loadFile('config/registration');
		$form->loadFile('config/seller');
		$form->loadFile('config/shipment');
		$form->loadFile('config/rating');
		$form->loadFile('config/b2b');

		// Load manifests Files of modules moved in sellacious manifests.
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$moduleManifests = JPATH_SELLACIOUS . '/joomla/manifests/modules/';

		if (JFolder::exists($moduleManifests))
		{
			$modules = JFolder::folders($moduleManifests);
			foreach ($modules as $module)
			{
				$file = JFolder::files($moduleManifests . '/' . $module);

				if (JFile::exists($moduleManifests . '/' . $module . '/' . $file[0]))
				{
					// TODO: language not loading need to check
					JFactory::getLanguage()->load($module, JPATH_SITE, 'en-GB', true);
					$form->loadFile($moduleManifests . '/' . $module . '/' . $file[0]);
				}
			}
		}

		if (!$this->helper->access->isSubscribed())
		{
			$form->setFieldAttribute('backoffice_logo', 'disabled', 'true', 'com_sellacious');
			$form->setFieldAttribute('backoffice_logo', 'limit', '1', 'com_sellacious');
			$form->setFieldAttribute('backoffice_logoicon', 'disabled', 'true', 'com_sellacious');
			$form->setFieldAttribute('backoffice_logoicon', 'limit', '1', 'com_sellacious');
			$form->setFieldAttribute('backoffice_favicon', 'disabled', 'true', 'com_sellacious');
			$form->setFieldAttribute('backoffice_favicon', 'limit', '1', 'com_sellacious');
			$form->setFieldAttribute('show_brand_footer', 'readonly', 'true', 'com_sellacious');
			$form->setFieldAttribute('show_rate_us', 'readonly', 'true', 'com_sellacious');
			$form->setFieldAttribute('show_doc_link', 'readonly', 'true', 'com_sellacious');
			$form->setFieldAttribute('show_support_link', 'readonly', 'true', 'com_sellacious');
			$form->setFieldAttribute('show_advertisement', 'readonly', 'true', 'com_sellacious');
			$form->setFieldAttribute('show_back_to_joomla', 'readonly', 'true', 'com_sellacious');
			$form->setFieldAttribute('show_sellacious_version', 'readonly', 'true', 'com_sellacious');
			$form->setFieldAttribute('show_license_to', 'readonly', 'true', 'com_sellacious');
			$form->setFieldAttribute('allow_client_authorised_users', 'readonly', 'true', 'com_sellacious');
			$form->setFieldAttribute('allow_credit_limit', 'readonly', 'true', 'com_sellacious');
		}

		parent::preprocessForm($form, $data, $group);
	}
}
