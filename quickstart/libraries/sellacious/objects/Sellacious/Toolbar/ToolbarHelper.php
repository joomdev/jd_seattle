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
namespace Sellacious\Toolbar;

defined('_JEXEC') or die;

use Sellacious\Toolbar\Button\ConfirmButton;
use Sellacious\Toolbar\Button\CustomButton;
use Sellacious\Toolbar\Button\HelpButton;
use Sellacious\Toolbar\Button\LinkButton;
use Sellacious\Toolbar\Button\PopupButton;
use Sellacious\Toolbar\Button\SeparatorButton;
use Sellacious\Toolbar\Button\StandardButton;

/**
 * Utility class for the button bar
 *
 * @since   1.6.0
 */
class ToolbarHelper
{
	/**
	 * Title cell.
	 * For the title and toolbar to be rendered correctly,
	 * this title function must be called before the startTable function and the toolbars icons
	 * this is due to the nature of how the css has been used to position the title in respect to the toolbar.
	 *
	 * @param   string  $title  The title.
	 * @param   string  $icon   The space-separated names of the image.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function title($title, $icon = 'generic.png')
	{
		$layout = new \JLayoutFile('sellacious.toolbar.title');
		$args   = array('title' => $title, 'icon' => $icon);
		$html   = $layout->render($args);

		try
		{
			$app = \JFactory::getApplication();
		}
		catch (\Exception $e)
		{
		}

		$doc = \JFactory::getDocument();

		$app->JComponentTitle = $html;
		$doc->setTitle($app->get('sitename') . ' - ' . strip_tags($title));
	}

	/**
	 * Writes a spacer cell.
	 *
	 * @param   string  $width  The width for the cell
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function spacer($width = '')
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new SeparatorButton('spacer', $width));
	}

	/**
	 * Writes a divider between menu buttons
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function divider()
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new SeparatorButton('divider', ''));
	}

	/**
	 * Writes a custom option and task button for the button bar.
	 *
	 * @param   string  $task        The task to perform (picked up by the switch($task) blocks.
	 * @param   string  $icon        The image to display.
	 * @param   string  $iconOver    The image to display when moused over.
	 * @param   string  $alt         The alt text for the icon image.
	 * @param   bool    $listSelect  True if required to check that a standard list item is checked.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function custom($task = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true)
	{
		$bar = Toolbar::getInstance();

		// Strip extension
		$icon = preg_replace('#\.[^.]*$#', '', $icon);

		$bar->appendButton(new StandardButton($icon, $alt, $task, $listSelect));
	}

	/**
	 * Writes a preview button for a given option (opens a popup window).
	 *
	 * @param   string  $url            The name of the popup file (excluding the file extension)
	 * @param   bool    $updateEditors  Unused
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function preview($url = '', $updateEditors = false)
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new PopupButton('preview', 'Preview', $url . '&task=preview'));
	}

	/**
	 * Writes a preview button for a given option (opens a popup window).
	 *
	 * @param   string  $ref        The name of the popup file (excluding the file extension for an xml file).
	 * @param   bool    $com        Use the help file in the component directory.
	 * @param   string  $override   Use this URL instead of any other
	 * @param   string  $component  Name of component to get Help (null for current component)
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function help($ref, $com = false, $override = null, $component = null)
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new HelpButton($ref, $com, $override, $component));
	}

	/**
	 * Writes a cancel button that will go back to the previous page without doing
	 * any other operation.
	 *
	 * @param   string  $alt   Alternative text.
	 * @param   string  $href  URL of the href attribute.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function back($alt = 'JTOOLBAR_BACK', $href = 'javascript:history.back();')
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new LinkButton('back', $alt, $href));
	}

	/**
	 * Creates a button to redirect to a link
	 *
	 * @param   string  $url    The link url
	 * @param   string  $text   Button text
	 * @param   string  $class  Class for icon to be used
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function link($url, $text, $class = 'link')
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new LinkButton($class, $text, $url));
	}

	/**
	 * Writes a media_manager button.
	 *
	 * @param   string  $directory  The sub-directory to upload the media to.
	 * @param   string  $alt        An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function media_manager($directory = '', $alt = 'JTOOLBAR_UPLOAD')
	{
		$bar = Toolbar::getInstance();
		$str = 'index.php?option=com_media&tmpl=component&task=popupUpload&folder=' . $directory;

		$bar->appendButton(new PopupButton('upload', $alt, $str, 800, 520));
	}

	/**
	 * Writes a common 'default' button for a record.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function makeDefault($task = 'default', $alt = 'JTOOLBAR_DEFAULT')
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new StandardButton('default', $alt, $task, true));
	}

	/**
	 * Writes a common 'assign' button for a record.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function assign($task = 'assign', $alt = 'JTOOLBAR_ASSIGN')
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new StandardButton('assign', $alt, $task, true));
	}

	/**
	 * Writes the common 'new' icon for the button bar.
	 *
	 * @param   string   $task   An override for the task.
	 * @param   string   $alt    An override for the alt text.
	 * @param   boolean  $check  True if required to check that a standard list item is checked.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function addNew($task = 'add', $alt = 'JTOOLBAR_NEW', $check = false)
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new StandardButton('new', $alt, $task, $check));
	}

	/**
	 * Writes a common 'publish' button.
	 *
	 * @param   string   $task   An override for the task.
	 * @param   string   $alt    An override for the alt text.
	 * @param   boolean  $check  True if required to check that a standard list item is checked.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function publish($task = 'publish', $alt = 'JTOOLBAR_PUBLISH', $check = false)
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new StandardButton('publish', $alt, $task, $check));
	}

	/**
	 * Writes a common 'publish' button for a list of records.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function publishList($task = 'publish', $alt = 'JTOOLBAR_PUBLISH')
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new StandardButton('publish', $alt, $task, true));
	}

	/**
	 * Writes a common 'unpublish' button.
	 *
	 * @param   string   $task   An override for the task.
	 * @param   string   $alt    An override for the alt text.
	 * @param   boolean  $check  True if required to check that a standard list item is checked.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function unpublish($task = 'unpublish', $alt = 'JTOOLBAR_UNPUBLISH', $check = false)
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new StandardButton('unpublish', $alt, $task, $check));
	}

	/**
	 * Writes a common 'unpublish' button for a list of records.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function unpublishList($task = 'unpublish', $alt = 'JTOOLBAR_UNPUBLISH')
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new StandardButton('unpublish', $alt, $task, true));
	}

	/**
	 * Writes a common 'archive' button for a list of records.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function archiveList($task = 'archive', $alt = 'JTOOLBAR_ARCHIVE')
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new StandardButton('archive', $alt, $task, true));
	}

	/**
	 * Writes an unarchive button for a list of records.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function unarchiveList($task = 'unarchive', $alt = 'JTOOLBAR_UNARCHIVE')
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new StandardButton('unarchive', $alt, $task, true));
	}

	/**
	 * Writes a common 'edit' button for a list of records.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function editList($task = 'edit', $alt = 'JTOOLBAR_EDIT')
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new StandardButton('edit', $alt, $task, true));
	}

	/**
	 * Writes a common 'edit' button for a template html.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function editHtml($task = 'edit_source', $alt = 'JTOOLBAR_EDIT_HTML')
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new StandardButton('edithtml', $alt, $task, true));
	}

	/**
	 * Writes a common 'edit' button for a template css.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function editCss($task = 'edit_css', $alt = 'JTOOLBAR_EDIT_CSS')
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new StandardButton('editcss', $alt, $task, true));
	}

	/**
	 * Writes a common 'delete' button for a list of records.
	 *
	 * @param   string  $msg   Postscript for the 'are you sure' message.
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function deleteList($msg = '', $task = 'remove', $alt = 'JTOOLBAR_DELETE')
	{
		$bar = Toolbar::getInstance();

		if ($msg)
		{
			$bar->appendButton(new ConfirmButton($msg, 'delete', $alt, $task, true));
		}
		else
		{
			$bar->appendButton(new StandardButton('delete', $alt, $task, true));
		}
	}

	/**
	 * Writes a common 'trash' button for a list of records.
	 *
	 * @param   string  $task   An override for the task.
	 * @param   string  $alt    An override for the alt text.
	 * @param   bool    $check  True to allow lists.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function trash($task = 'remove', $alt = 'JTOOLBAR_TRASH', $check = true)
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new StandardButton('trash', $alt, $task, $check, false));
	}

	/**
	 * Writes a save button for a given option.
	 * Apply operation leads to a save action only (does not leave edit mode).
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function apply($task = 'apply', $alt = 'JTOOLBAR_APPLY')
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new StandardButton('apply', $alt, $task, false));
	}

	/**
	 * Writes a save button for a given option.
	 * Save operation leads to a save and then close action.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function save($task = 'save', $alt = 'JTOOLBAR_SAVE')
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new StandardButton('save', $alt, $task, false));
	}

	/**
	 * Writes a save and create new button for a given option.
	 * Save and create operation leads to a save and then add action.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function save2new($task = 'save2new', $alt = 'JTOOLBAR_SAVE_AND_NEW')
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new StandardButton('save-new', $alt, $task, false));
	}

	/**
	 * Writes a save as copy button for a given option.
	 * Save as copy operation leads to a save after clearing the key,
	 * then returns user to edit mode with new key.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function save2copy($task = 'save2copy', $alt = 'JTOOLBAR_SAVE_AS_COPY')
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new StandardButton('save-copy', $alt, $task, false));
	}

	/**
	 * Writes a checkin button for a given option.
	 *
	 * @param   string   $task   An override for the task.
	 * @param   string   $alt    An override for the alt text.
	 * @param   boolean  $check  True if required to check that a standard list item is checked.
	 *
	 * @return  void
	 *
	 * @since   1.7
	 */
	public static function checkin($task = 'checkin', $alt = 'JTOOLBAR_CHECKIN', $check = true)
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new StandardButton('checkin', $alt, $task, $check));
	}

	/**
	 * Writes a cancel button and invokes a cancel operation (eg a checkin).
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function cancel($task = 'cancel', $alt = 'JTOOLBAR_CANCEL')
	{
		$bar = Toolbar::getInstance();

		$bar->appendButton(new StandardButton('cancel', $alt, $task, false));
	}

	/**
	 * Writes a configuration button and invokes a cancel operation (eg a checkin).
	 *
	 * @param   string   $component  The name of the component, eg, com_content.
	 * @param   integer  $height     The height of the popup. [UNUSED]
	 * @param   integer  $width      The width of the popup. [UNUSED]
	 * @param   string   $alt        The name of the button.
	 * @param   string   $path       An alternative path for the configuation xml relative to JPATH_SITE.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function preferences($component, $height = 550, $width = 875, $alt = 'JToolbar_Options', $path = '')
	{
		$component = urlencode($component);
		$path      = urlencode($path);
		$bar       = Toolbar::getInstance();

		$uri    = (string) \JUri::getInstance();
		$return = urlencode(base64_encode($uri));
		$url    = 'index.php?option=com_config&amp;view=component&amp;component=' . $component . '&amp;path=' . $path . '&amp;return=' . $return;

		$bar->appendButton(new LinkButton('options', $alt, $url));
	}

	/**
	 * Writes a version history
	 *
	 * @param   string   $typeAlias  The component and type, for example 'com_content.article'
	 * @param   integer  $itemId     The id of the item, for example the article id.
	 * @param   integer  $height     The height of the popup.
	 * @param   integer  $width      The width of the popup.
	 * @param   string   $alt        The name of the button.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function versions($typeAlias, $itemId, $height = 800, $width = 500, $alt = 'JTOOLBAR_VERSIONS')
	{
		\JHtml::_('behavior.modal', 'a.modal_jform_contenthistory');

		/** @var  \JTableContenttype  $contentTypeTable */
		$contentTypeTable = \JTable::getInstance('Contenttype');
		$typeId           = $contentTypeTable->getTypeId($typeAlias);

		// Options array for JLayout
		$options = array();

		$options['title']     = \JText::_($alt);
		$options['height']    = $height;
		$options['width']     = $width;
		$options['itemId']    = $itemId;
		$options['typeId']    = $typeId;
		$options['typeAlias'] = $typeAlias;

		$bar    = Toolbar::getInstance();
		$layout = new \JLayoutFile('sellacious.toolbar.versions');
		$html   = $layout->render($options);

		$bar->appendButton(new CustomButton($html, 'versions'));
	}

	/**
	 * Displays a modal button
	 *
	 * @param   string  $targetModalId  ID of the target modal box
	 * @param   string  $icon           Icon class to show on modal button
	 * @param   string  $alt            Title for the modal button
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public static function modal($targetModalId, $icon, $alt)
	{
		\JHtml::_('bootstrap.framework');

		$title = \JText::_($alt);
		$html  = "<button data-toggle='modal' data-target='#" . $targetModalId . "' class='btn btn-small'>" .
				 "<span class='" . $icon . "' title='" . $title . "'></span> " . $title . "</button>";

		$bar = Toolbar::getInstance();

		$bar->appendButton(new CustomButton($html, $alt));
	}
}
