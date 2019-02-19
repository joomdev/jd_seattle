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
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious Cart Buttons Plugin
 *
 * @since   1.3.0
 */
class PlgContentSellaciousButtons extends SellaciousPlugin
{
	/**
	 * Plugin to load query form data and populate the message object with its text format.
	 *
	 * @param   string   $context   The context of the content being passed to the plugin.
	 * @param   object   &$article  An object with the properties of the loaded record.
	 * @param   mixed    $params    Additional parameters. Unused. See {@see PlgContentContent()}.
	 * @param   integer  $page      Optional page number. Unused. Defaults to zero.
	 *
	 * @return  bool  True on success.
	 *
	 * @since   1.3.0
	 */
	public function onContentPrepare($context, &$article, $params, $page = 0)
	{
		// Don't run this plugin when the content is being indexed or when not in site
		if (!$this->app->isClient('site') || $context == 'com_finder.indexer')
		{
			return true;
		}

		// Simple performance check to determine whether bot should process further
		if (strpos($article->text, 'sellacious.cart.buy') === false && strpos($article->text, 'sellacious.cart.add') === false)
		{
			return true;
		}

		// Expression to search for (buttons)
		// Example: [sellacious.cart.buy=P12V345S67;btn btn-success;BUY NOW]
		$regex = '!\[sellacious\.cart\.(buy|add)=(.*?)\]!i';

		// Find all instances of plugin and put in $matches for buttons
		// $match[0] is full pattern match, $match[1] is the type of action and $match[2] is the parameters
		preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);

		if (is_array($matches) && count($matches))
		{
			$this->script();

			foreach ($matches as $match)
			{
				$checkout = $match[1] == 'buy';

				$parts  = explode(';', $match[2]);
				$code   = ArrayHelper::getValue($parts, 0);
				$class  = ArrayHelper::getValue($parts, 1, 'btn btn-primary');
				$label  = ArrayHelper::getValue($parts, 2, $checkout ? 'Buy Now' : 'Add to Cart');

				$button = $this->getButton(strtoupper($code), $label, $class, $checkout);

				$article->text = str_replace($match[0], $button, $article->text);
			}
		}

		return true;
	}

	/**
	 * Add javascript behaviour for the cart buttons generated.
	 *
	 * @return  void
	 *
	 * @since   1.3.0
	 */
	protected function script()
	{
		static $loaded;

		if (!$loaded)
		{
			JHtml::_('behavior.framework');
			JHtml::_('jquery.framework');
			JHtml::_('script', 'com_sellacious/util.cart-buttons.js', false, true);

			$loaded = true;
		}
	}

	/**
	 * Get a rendered button HTML for the given button arguments
	 *
	 * @param   string  $code      The product code to access
	 * @param   string  $label     The label for the button
	 * @param   string  $class     The button class attribute
	 * @param   bool    $checkout  Whether this is a buy now action. False = Add to cart, True = Buy Now
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 */
	protected function getButton($code, $label, $class = 'btn btn-primary', $checkout = false)
	{
		ob_start();

		try
		{
			include JPluginHelper::getLayoutPath($this->_type, $this->_name, 'default');
		}
		catch (Exception $e)
		{
			$label = htmlspecialchars($label, ENT_COMPAT, 'UTF-8');
			$attr  = $checkout ? 'data-checkout="true"' : '';

			echo "<button type=\"button\" class=\"btn-content-cart {$class}\" data-item=\"{$code}\" {$attr}>{$label}</button>";
		}

		return ob_get_clean();
	}
}
