<?php
// Include Bootstrap framework
JHtml::_('bootstrap.framework');

/** @var array $displayData
 */
extract($displayData);

// Build the script.
$script = array();
$script[] = "jQuery(function($){";
$script[] = "\t$('#" . $selector . "').collapse(" . $options . ")";

if ($onShow)
{
$script[] = "\t.on('show', " . $onShow . ")";
}

if ($onShown)
{
$script[] = "\t.on('shown', " . $onShown . ")";
}

if ($onHide)
{
$script[] = "\t.on('hideme', " . $onHide . ")";
}

if ($onHidden)
{
$script[] = "\t.on('hidden', " . $onHidden . ")";
}


if ($opt['parent'] && empty($parents))
{
$script[] = "
$(document).on('click.collapse.data-api', '[data-toggle=collapse]', function (e) {
var \$this   = $(this), href
var parent  = \$this.attr('data-parent')
var \$parent = parent && $(parent)

if (\$parent) \$parent.find('[data-toggle=collapse][data-parent=' + parent + ']').not(\$this).addClass('collapsed');
\$this.removeClass('collapsed')
})";
}

$script[] = "});";

// Attach accordion to document
JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

echo '<div id="' . $selector . '" class="accordion">';

