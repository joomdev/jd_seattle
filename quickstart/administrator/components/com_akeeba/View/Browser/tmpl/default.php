<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/** @var \Akeeba\Backup\Admin\View\Browser\Html $this */

$rootDirWarning = JText::_('COM_AKEEBA_CONFIG_UI_ROOTDIR', true);

$this->addJavascriptInline(<<<JS

	;// This comment is intentionally put here to prevent badly written plugins from causing a Javascript error
	// due to missing trailing semicolon and/or newline in their code.
	function akeeba_browser_useThis()
	{
		var rawFolder = document.forms.adminForm.folderraw.value;
		if( rawFolder == '[SITEROOT]' )
		{
			alert('$rootDirWarning');
			rawFolder = '[SITETMP]';
		}
		window.parent.akeeba.Configuration.onBrowserCallback( rawFolder );
	}

JS
	, 'text/javascript');

?>

<?php if (empty($this->folder)): ?>
    <form action="index.php" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="com_akeeba"/>
        <input type="hidden" name="view" value="Browser"/>
        <input type="hidden" name="format" value="html"/>
        <input type="hidden" name="tmpl" value="component"/>
        <input type="hidden" name="folder" id="folder" value=""/>
        <input type="hidden" name="processfolder" id="processfolder" value="0"/>
        <input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1"/>
    </form>
<?php endif; ?>

<?php if (!(empty($this->folder))):
	$writeableText = \JText::_($this->writable ? 'COM_AKEEBA_CPANEL_LBL_WRITABLE' : 'COM_AKEEBA_CPANEL_LBL_UNWRITABLE');
	$writeableIcon = $this->writable ? 'akion-checkmark-circled' : 'akion-ios-close';
	$writeableClass = $this->writable ? 'akeeba-label--green' : 'akeeba-label--red';
	?>
    <div class="akeeba-panel--100 akeeba-panel--primary">
        <div>
            <form action="index.php" method="get" name="adminForm" id="adminForm" class="akeeba-form--inline akeeba-form--with-hidden">
                <span title="<?php echo $writeableText; ?>" class="<?php echo $writeableClass ?>">
                    <span class="<?php echo $writeableIcon; ?>"></span>
                </span>
                <input type="text" name="folder" id="folder" value="<?php echo $this->escape($this->folder); ?>"/>

                <button class="akeeba-btn--primary" onclick="document.form.adminForm.submit(); return false;">
                    <span class="akion-folder"></span>
					<?php echo \JText::_('COM_AKEEBA_BROWSER_LBL_GO'); ?>
                </button>

                <button class="akeeba-btn--green" onclick="akeeba_browser_useThis(); return false;">
                    <span class="akion-share"></span>
					<?php echo \JText::_('COM_AKEEBA_BROWSER_LBL_USE'); ?>
                </button>

                <div class="akeeba-hidden-fields-container">
                    <input type="hidden" name="folderraw" id="folderraw"
                           value="<?php echo $this->escape($this->folder_raw); ?>"/>
                    <input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1"/>
                    <input type="hidden" name="option" value="com_akeeba"/>
                    <input type="hidden" name="view" value="Browser"/>
                    <input type="hidden" name="tmpl" value="component"/>
                </div>
            </form>
        </div>
    </div>

	<?php if (count($this->breadcrumbs)): ?>
    <div class="akeeba-panel--100 akeeba-panel--information">
        <div>
            <ul class="akeeba-breadcrumb">
				<?php $i = 0 ?>
				<?php foreach ($this->breadcrumbs as $crumb): ?>
					<?php $i++; ?>
                    <li class="<?php echo ($i < count($this->breadcrumbs)) ? '' : 'active'; ?>">
						<?php if ($i < count($this->breadcrumbs)): ?>
                            <a href="<?php echo $this->escape(JUri::base() . "index.php?option=com_akeeba&view=Browser&tmpl=component&folder=" . urlencode($crumb['folder'])); ?>">
								<?php echo $this->escape($crumb['label']); ?>

                            </a>
                            <span class="divider">&bull;</span>
						<?php else: ?>
							<?php echo $this->escape($crumb['label']); ?>

						<?php endif; ?>
                    </li>
				<?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>

    <div class="akeeba-panel--100 akeeba-panel">
        <div>
			<?php if (count($this->subfolders)): ?>
                <table class="akeeba-table akeeba-table--striped">
                    <tr>
                        <td>
                            <a class="akeeba-btn--dark--small"
                               href="<?php echo $this->escape(JUri::base()); ?>index.php?option=com_akeeba&view=Browser&tmpl=component&folder=<?php echo $this->escape($this->parent); ?>">
                                <span class="akion-arrow-up-a"></span>
								<?php echo \JText::_('COM_AKEEBA_BROWSER_LBL_GOPARENT'); ?>
                            </a>
                        </td>
                    </tr>
					<?php foreach ($this->subfolders as $subfolder): ?>
                        <tr>
                            <td>
                                <a href="<?php echo $this->escape(JUri::base()); ?>index.php?option=com_akeeba&view=Browser&tmpl=component&folder=<?php echo $this->escape($this->folder . '/' . $subfolder); ?>"><?php echo $this->escape($subfolder); ?></a>
                            </td>
                        </tr>
					<?php endforeach; ?>
                </table>
			<?php else: ?>
				<?php if (!$this->exists): ?>
                    <div class="akeeba-block--failure">
						<?php echo \JText::_('COM_AKEEBA_BROWSER_ERR_NOTEXISTS'); ?>
                    </div>
				<?php elseif (!$this->inRoot): ?>
                    <div class="akeeba-block--warning">
						<?php echo \JText::_('COM_AKEEBA_BROWSER_ERR_NONROOT'); ?>
                    </div>
				<?php elseif ($this->openbasedirRestricted): ?>
                    <div class="akeeba-block--failure">
						<?php echo \JText::_('COM_AKEEBA_BROWSER_ERR_BASEDIR'); ?>
                    </div>
				<?php else: ?>
                    <table class="akeeba-table--striped">
                        <tr>
                            <td>
                                <a class="akeeba-btn--dark--small"
                                   href="<?php echo $this->escape(JUri::base()); ?>index.php?option=com_akeeba&view=Browser&tmpl=component&folder=<?php echo $this->escape($this->parent); ?>">
                                    <span class="akion-arrow-up-a"></span>
									<?php echo \JText::_('COM_AKEEBA_BROWSER_LBL_GOPARENT'); ?>
                                </a>
                            </td>
                        </tr>
                    </table>
				<?php endif; ?><?php /* secondary block */ ?>
			<?php endif; ?> <?php /* count($this->subfolders) */ ?>
        </div>
    </div>
<?php endif; ?>
