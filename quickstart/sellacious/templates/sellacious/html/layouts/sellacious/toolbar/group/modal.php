<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

JHtml::_('behavior.core');

/**
 * Generic toolbar button layout to open a modal
 * -----------------------------------------------
 * @param   array   $displayData    Button parameters. Default supported parameters:
 *                                  - selector  string  Unique DOM identifier for the modal. CSS id without #
 *                                  - class     string  Button class
 *                                  - icon      string  Button icon
 *                                  - text      string  Button text
 */

$selector = $displayData['selector'];
$class    = isset($displayData['class']) ? $displayData['class'] : '';
$icon     = isset($displayData['icon']) ? $displayData['icon'] : 'out-3';
$text     = isset($displayData['text']) ? $displayData['text'] : '';
?>
<button class="<?php echo $class; ?> btn btn-small" data-toggle="modal" data-target="#<?php echo $selector; ?>">
	<span class="icon-<?php echo $icon; ?>" aria-hidden="true"></span>
	<?php echo $text; ?>
</button>
