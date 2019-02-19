<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('JPATH_PLATFORM') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Class pkg_sellacious_extendedInstallerScript
 *
 * @since   1.0.0
 */
class pkg_sellacious_extendedInstallerScript
{
	/**
	 * Method to run before an install/update/uninstall method.
	 * Used to warn user that core package needs to be installed first before installing this one.
	 *
	 * @param   string                    $type
	 * @param   JInstallerAdapterPackage  $installer
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function preFlight($type, $installer)
	{
		// Todo: Detect existing installation and active backoffice directory when in update routine
		if ($type == 'install')
		{
			$table = JTable::getInstance('Extension');

			if (!$table->load(array('type' => 'component', 'element' => 'com_sellacious')))
			{
				$message = 'You need to install <strong>Sellacious Core Package first</strong>. ' .
					'You can <a target="_blank" href="https://extensions.joomla.org/extension/sellacious">download it from JED</a> for FREE!';

				throw new RuntimeException($message);
			}
		}
	}

	/**
	 * Method to run after an install/update/uninstall method
	 *
	 * @param   string                     $type
	 * @param   \JInstallerAdapterPackage  $installer
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function postFlight($type, $installer)
	{
		// Delete the old version files
		if ($type == 'update')
		{
			$this->cleanupOldFiles();
			$this->enablePlugins();
		}
		elseif ($type == 'install')
		{
			$this->setupUserGroups();
			$this->enablePlugins(true);
		}
	}

	/**
	 * Enable all plugins installed with sellacious packages
	 *
	 * @param   bool  $all  Whether to enable all plugins or just the isnew="true" marked ones.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function enablePlugins($all = false)
	{
		$fileNames[] = JPATH_MANIFESTS . '/packages/pkg_sellacious_extended.xml';

		foreach ($fileNames as $filename)
		{
			if (file_exists($filename))
			{
				$manifest = simplexml_load_file($filename);

				if ($manifest instanceof SimpleXMLElement)
				{
					$plugins = $manifest->xpath('/extension/files[@folder="extensions"]/file[@type="plugin"]');
					$enabled = 0;

					foreach ($plugins as $plugin)
					{
						if ($all || (string) $plugin['isnew'] == 'true')
						{
							$keys = array(
								'type'    => (string) $plugin['type'],
								'folder'  => (string) $plugin['group'],
								'element' => (string) $plugin['id'],
							);

							$extension = JTable::getInstance('Extension');

							if ($extension->load($keys))
							{
								$extension->set('enabled', 1);
								$enabled += $extension->store();
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Set default permission for sellacious if it is empty
	 *
	 * @param   array  $groups  Associative array of group type => id to set the permissions
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function setPermissions($groups)
	{
		/** @var  JTableUsergroup  $group */
		$group = JTable::getInstance('Usergroup');
		$group->rebuild();

		/** @var  JTableAsset  $asset */
		$asset = JTable::getInstance('Asset', 'JTable');
		$asset->loadByName('com_sellacious');

		if ($asset->id == 0)
		{
			$asset->set('parent_id', 1);
			$asset->set('name', 'com_sellacious');
			$asset->set('title', 'com_sellacious');
			$asset->setLocation(1, 'last-child');
		}

		$ruleSets = json_decode($asset->rules, true);

		if (empty($ruleSets))
		{
			$ruleSets = array(
				'admin'    => array(
					'config.edit'                     => 1,
					'permissions.edit'                => 1,
					'statistics.visitor'              => 1,
					'category.list'                   => 1,
					'category.create'                 => 1,
					'category.edit'                   => 1,
					'category.edit.state'             => 1,
					'category.delete'                 => 1,
					'coupon.list'                     => 1,
					'coupon.list.own'                 => 1,
					'coupon.create'                   => 1,
					'coupon.edit'                     => 1,
					'coupon.edit.own'                 => 1,
					'coupon.delete'                   => 1,
					'coupon.delete.own'               => 1,
					'coupon.edit.state'               => 1,
					'currency.list'                   => 1,
					'currency.create'                 => 1,
					'currency.edit'                   => 1,
					'currency.edit.state'             => 1,
					'currency.delete'                 => 1,
					'currency.edit.forex'             => 1,
					'field.list'                      => 1,
					'field.create'                    => 1,
					'field.delete'                    => 1,
					'field.edit'                      => 1,
					'field.edit.state'                => 1,
					'product.list'                    => 1,
					'product.list.own'                => 1,
					'product.create'                  => 1,
					'product.edit.basic'              => 1,
					'product.edit.basic.own'          => 1,
					'product.edit.seller'             => 1,
					'product.edit.pricing'            => 1,
					'product.edit.shipping'           => 1,
					'product.edit.related'            => 1,
					'product.edit.seo'                => 1,
					'product.edit.seo.own'            => 1,
					'product.delete'                  => 1,
					'product.delete.own'              => 1,
					'product.edit.state'              => 1,
					'variant.create'                  => 1,
					'variant.delete'                  => 1,
					'variant.delete.own'              => 1,
					'variant.edit'                    => 1,
					'variant.edit.own'                => 1,
					'variant.edit.state'              => 1,
					'shippingrule.list'               => 1,
					'shippingrule.list.own'           => 1,
					'shippingrule.create'             => 1,
					'shippingrule.delete'             => 1,
					'shippingrule.delete.own'         => 1,
					'shippingrule.edit'               => 1,
					'shippingrule.edit.own'           => 1,
					'shippingrule.edit.state'         => 1,
					'shoprule.list'                   => 1,
					'shoprule.create'                 => 1,
					'shoprule.delete'                 => 1,
					'shoprule.edit'                   => 1,
					'shoprule.edit.state'             => 1,
					'splcategory.list'                => 1,
					'splcategory.create'              => 1,
					'splcategory.delete'              => 1,
					'splcategory.edit'                => 1,
					'splcategory.edit.state'          => 1,
					'status.list'                     => 1,
					'status.create'                   => 1,
					'status.delete'                   => 1,
					'status.edit'                     => 1,
					'status.edit.state'               => 1,
					'user.list'                       => 1,
					'user.create'                     => 1,
					'user.delete'                     => 1,
					'user.edit'                       => 1,
					'user.edit.state'                 => 1,
					'user.edit.own'                   => 1,
					'unit.list'                       => 1,
					'unit.create'                     => 1,
					'unit.delete'                     => 1,
					'unit.edit'                       => 1,
					'unit.edit.state'                 => 1,
					'order.list'                      => 1,
					'order.list.own'                  => 1,
					'transaction.list'                => 1,
					'transaction.list.own'            => 1,
					'transaction.addfund.direct'      => 1,
					'transaction.addfund.gateway'     => 1,
					'transaction.addfund.direct.own'  => 1,
					'transaction.addfund.gateway.own' => 1,
					'transaction.withdraw'            => 1,
					'transaction.withdraw.own'        => 1,
					'transaction.withdraw.approve'    => 1,
					'emailtemplate.list'              => 1,
					'emailtemplate.edit'              => 1,
					'location.list'                   => 1,
					'location.list.own'               => 1,
					'location.create'                 => 1,
					'location.edit'                   => 1,
					'location.edit.state'             => 1,
					'paymentmethod.list'              => 1,
					'paymentmethod.list.own'          => 1,
					'paymentmethod.create'            => 1,
					'paymentmethod.edit'              => 1,
					'message.list'                    => 1,
					'message.list.own'                => 1,
					'message.create'                  => 1,
					'message.edit'                    => 1,
					'message.edit.own'                => 1,
					'rating.list'                     => 1,
					'rating.list.own'                 => 1,
					'rating.edit.own'                 => 1,
					'location.delete'                 => 1,
					'paymentmethod.delete'            => 1,
					'message.delete'                  => 1,
					'rating.delete'                   => 1,
					'rating.edit.state'               => 1,
					'app.login'                       => 1,
					'product.edit.seller.own'         => 1,
					'product.edit.pricing.own'        => 1,
					'product.edit.shipping.own'       => 1,
					'product.edit.related.own'        => 1,
					'order.item.edit.status.own'      => 1,
					'message.reply'                   => 1,
					'message.reply.own'               => 1,
					'message.create.bulk'             => 1,
					'message.html'                    => 1,
					'order.item.edit.status'          => 1,
					'mailqueue.list.own'              => 1,
					'mailqueue.list'                  => 1,
				),
				'staff'    => array(
					'coupon.list'                => 1,
					'coupon.list.own'            => 1,
					'coupon.create'              => 1,
					'coupon.edit.own'            => 1,
					'coupon.delete.own'          => 1,
					'currency.list'              => 1,
					'currency.edit.state'        => 1,
					'product.list'               => 1,
					'product.list.own'           => 1,
					'product.create'             => 1,
					'product.edit.basic'         => 1,
					'product.edit.basic.own'     => 1,
					'product.edit.pricing'       => 1,
					'product.edit.shipping'      => 1,
					'product.edit.related'       => 1,
					'product.edit.seo'           => 1,
					'product.delete.own'         => 1,
					'product.edit.state'         => 1,
					'shippingrule.list'          => 1,
					'shippingrule.create'        => 1,
					'shippingrule.delete'        => 1,
					'shippingrule.edit'          => 1,
					'shippingrule.edit.state'    => 1,
					'shoprule.list'              => 1,
					'shoprule.create'            => 1,
					'shoprule.edit.state'        => 1,
					'splcategory.list'           => 1,
					'splcategory.create'         => 1,
					'splcategory.delete'         => 1,
					'splcategory.edit'           => 1,
					'splcategory.edit.state'     => 1,
					'status.list'                => 1,
					'status.create'              => 1,
					'status.edit.state'          => 1,
					'user.list'                  => 1,
					'unit.list'                  => 1,
					'unit.create'                => 1,
					'unit.delete'                => 1,
					'unit.edit'                  => 1,
					'unit.edit.state'            => 1,
					'order.list'                 => 1,
					'order.list.own'             => 1,
					'emailtemplate.list'         => 1,
					'location.list'              => 1,
					'location.list.own'          => 1,
					'location.create'            => 1,
					'location.edit.state'        => 1,
					'paymentmethod.list'         => 1,
					'paymentmethod.list.own'     => 1,
					'paymentmethod.create'       => 1,
					'message.list.own'           => 1,
					'message.create'             => 1,
					'rating.list'                => 1,
					'rating.list.own'            => 1,
					'rating.delete'              => 1,
					'rating.edit.state'          => 1,
					'app.login'                  => 1,
					'product.edit.pricing.own'   => 1,
					'product.edit.shipping.own'  => 1,
					'product.edit.related.own'   => 1,
					'order.item.edit.status.own' => 1,
					'message.reply.own'          => 1,
					'message.create.bulk'        => 1,
					'message.html'               => 1,
					'order.item.edit.status'     => 1,
					'download.list.own'          => 1,
					'download.list'              => 1,
					'license.list'               => 1,
					'license.create'             => 1,
					'license.delete'             => 1,
					'license.edit'               => 1,
					'license.edit.state'         => 1,
				),
				'seller'   => array(
					'coupon.list.own'                 => 1,
					'coupon.create'                   => 1,
					'coupon.edit.own'                 => 1,
					'coupon.delete.own'               => 1,
					'product.list.own'                => 1,
					'product.create'                  => 1,
					'product.edit.basic.own'          => 1,
					'product.delete.own'              => 1,
					'variant.create'                  => 1,
					'variant.delete.own'              => 1,
					'variant.edit.own'                => 1,
					'shippingrule.list.own'           => 1,
					'shippingrule.delete.own'         => 1,
					'shippingrule.edit.own'           => 1,
					'user.edit.own'                   => 1,
					'order.list.own'                  => 1,
					'transaction.list.own'            => 1,
					'transaction.addfund.gateway.own' => 1,
					'transaction.withdraw.own'        => 1,
					'message.list.own'                => 1,
					'message.create'                  => 1,
					'rating.list.own'                 => 1,
					'app.login'                       => 1,
					'product.edit.seller.own'         => 1,
					'product.edit.pricing.own'        => 1,
					'product.edit.shipping.own'       => 1,
					'product.edit.related.own'        => 1,
					'order.item.edit.status.own'      => 1,
					'message.reply.own'               => 1,
					'message.html'                    => 1,
					'mailqueue.list.own'              => 1,
					'download.list.own'               => 1,
				),
				'customer' => array(
					'user.edit.own'                   => 1,
				),
			);

			$rules = array();

			foreach ($ruleSets as $k => $ruleSet)
			{
				if ($gid = ArrayHelper::getValue($groups, $k))
				{
					foreach ($ruleSet as $ruleName => $value)
					{
						$rules[$ruleName][$gid] = $value;
					}
				}
			}

			$asset->rules = json_encode($rules);

			$asset->check();
			$asset->store();
		}
	}

	/**
	 * Delete the files which existed in earlier versions but not in this version
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	protected function cleanupOldFiles()
	{
		$files = array(
			'sellacious/components/com_sellacious/layouts/views/activation/default.php',
			'sellacious/components/com_sellacious/layouts/views/products/default_modal.php',
			'sellacious/includes/toolbar.php',
			'sellacious/templates/sellacious/html/layouts/joomla/toolbar/base.php',
			'sellacious/templates/sellacious/html/layouts/joomla/toolbar/containerclose.php',
			'sellacious/templates/sellacious/html/layouts/joomla/toolbar/iconclass.php',
			'sellacious/templates/sellacious/html/layouts/joomla/toolbar/link.php',
			'sellacious/templates/sellacious/html/layouts/joomla/toolbar/standard.php',
			'sellacious/templates/sellacious/html/layouts/joomla/toolbar/title.php'
		);

		foreach ($files as $file)
		{
			JFile::delete(JPATH_ROOT . '/' . $file);
		}
	}

	/**
	 * Setup user groups and their default permissions as required by sellacious upon first install.
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 *
	 * @throws  Exception
	 */
	protected function setupUserGroups()
	{
		jimport('sellacious.loader');

		if (class_exists('SellaciousHelper'))
		{
			$me       = JFactory::getUser();
			$helper   = SellaciousHelper::getInstance();
			$category = $helper->category->getDefault('seller', 'a.id');
			$seller   = array(
				'user_id'     => $me->id,
				'category_id' => $category ? $category->id : 0,
				'title'       => $me->name,
				'code'        => strtoupper($me->username),
			);

			// We may want to skip this if the default seller account is already set up.
			$helper->profile->create($me->id);
			$helper->user->addAccount($seller, 'seller');
			$helper->config->set('default_seller', $me->id);

			// Create the required user groups
			$uParams     = JComponentHelper::getParams('com_users');
			$regGroup    = $uParams->get('new_usertype', 2);
			$guestGroup  = $uParams->get('guest_usergroup', 1);
			$sellerGroup = 0;
			$staffGroup  = 0;
			$adminGroup  = 0;

			$table = JTable::getInstance('Usergroup');
			$group = array('parent_id' => $regGroup, 'title' => 'Seller');

			if ($table->load($group) || ($table->bind($group) && $table->check() && $table->store()))
			{
				$sellerGroup = $table->get('id');
				JUserHelper::addUserToGroup($me->id, $sellerGroup);
			}

			$table = JTable::getInstance('Usergroup');
			$group = array('parent_id' => $regGroup, 'title' => 'Staff');

			if ($table->load($group) || ($table->bind($group) && $table->check() && $table->store()))
			{
				$staffGroup = $table->get('id');
			}

			$table  = JTable::getInstance('Usergroup');
			$group1 = array('title' => 'Administrator');
			$group2 = array('parent_id' => $regGroup, 'title' => 'Shop Administrator');

			if ($table->load($group2) || $table->load($group1) || ($table->bind($group2) && $table->check() && $table->store()))
			{
				$adminGroup = $table->get('id');
				JUserHelper::addUserToGroup($me->id, $adminGroup);
			}

			// Update configuration with these groups
			$helper->config->set('usergroups_client', array($guestGroup, $regGroup));
			$helper->config->set('usergroups_seller', array($sellerGroup));
			$helper->config->set('usergroups_staff', array($staffGroup));
			$helper->config->set('usergroups_company', array($adminGroup));

			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			try
			{
				$query->update('#__sellacious_categories')
					->set('usergroups = ' . $db->q(json_encode(array($regGroup))))
					->where('type = ' . $db->q('client'))
					->where('is_default = 1');

				$db->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
			}

			try
			{
				$query->clear()
					->update('#__sellacious_categories')
					->set('usergroups = ' . $db->q(json_encode(array($sellerGroup))))
					->where('type = ' . $db->q('seller'))
					->where('is_default = 1');

				$db->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
			}

			try
			{
				$query->clear()
					->update('#__sellacious_categories')
					->set('usergroups = ' . $db->q(json_encode(array($staffGroup))))
					->where('type = ' . $db->q('staff'))
					->where('is_default = 1');

				$db->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
			}

			// Now set permissions
			$groups = array(
				'admin'    => $adminGroup,
				'staff'    => $staffGroup,
				'seller'   => $sellerGroup,
				'customer' => $regGroup,
			);

			$this->setPermissions($groups);
		}
	}
}
