<?php
/**
 * @package   Akeeba Backup 5.x
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

if (function_exists('akeebaBackupOnAfterRenderToFixBrokenCloudFlareRocketLoader'))
{
	return;
}

/**
 * CloudFlare has a broken feature called Rocket Loader. It tries to load all Javascript asynchronously disregarding
 * the load order specified by the developers. As a result it causes JavaScript errors on the page which end
 * up preventing any JavaScript from running. Yes, including the JavaScript which powers every single Akeeba Backup
 * feature (as well as many Joomla, WordPress, Drupal etc features). For their description do read:
 * https://support.cloudflare.com/hc/en-us/articles/200168056-What-does-Rocket-Loader-do-
 *
 * For a developer's description of what this MASSIVELY BROKEN feature does read:
 * http://webmasters.stackexchange.com/a/60277
 * As the guy succinctly puts it "I'm actually quite shocked that it works (although perhaps it doesn't always)".
 *
 * In any case, their documentation (https://support.cloudflare.com/hc/en-us/articles/200169436--How-can-I-have-Rocket-Loader-ignore-my-script-s-in-Automatic-Mode-)
 * suggests adding the non-standard `data-cfasync="false"` attribute BEFORE the script's attribute. Here's the thing.
 * Joomla! does NOT let you do that. So the only thing we can do is parse Joomla!'s HTML output and forcibly add this
 * attribute using regular expressions search & replace. Normally this requires a system plugin to hook into Joomla's
 * onAfterRender event. But we can't do that. So we do the next best thing: abuse Joomla's event system to register the
 * even handler directly, without going through JPlugin. This is the kind of crap I was happy to had gotten rid of in
 * Akeeba Backup 5.0 because it's slow and cumbersome. Yet, the brokenness of CloudFlare forces me to re-implement a
 * slow, cumbersome approach which has a negative impact to the component's performance. Ironic, considering how
 * CloudFlare is supposed to speed up your site...
 *
 * RECOMMENDATION: AVOID USING CLOUDFLARE AT ALL COSTS.
 *
 * @return  void
 *
 * @since  5.1.3
 */
function akeebaBackupOnAfterRenderToFixBrokenCloudFlareRocketLoader()
{
	// The generated HTML
	$app    = JFactory::getApplication();
	$buffer = $app->getBody();

	// Find the <head.....</head> section
	$from = stripos($buffer, '<head');

	if ($from === false)
	{
		return;
	}

	$to = stripos($buffer, '</head', $from + 5);

	if ($to === false)
	{
		return;
	}

	// Extract <head> section
	$head = substr($buffer, $from, $to - $from);

	// Replace '<script...src' with '<script...data-cfasync="false" src'
	$regEx = '/<script([^>]*)src\s?=\s?(\'|")/im';
	$head = preg_replace($regEx, '<script$1 data-cfasync="false" src=$2', $head);

	// Reconstruct the page's HTML and set it back to the buffer
	$buffer = substr($buffer, 0, $from) . $head . substr($buffer, $to);
	$app->setBody($buffer);
}
