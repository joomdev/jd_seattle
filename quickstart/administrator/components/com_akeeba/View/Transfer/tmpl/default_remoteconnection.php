<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/** @var  $this  \Akeeba\Backup\Admin\View\Transfer\Html */

?>

<div class="akeeba-panel--primary">
	<header class="akeeba-block-header">
		<h3>
			<?php echo \JText::_('COM_AKEEBA_TRANSFER_HEAD_REMOTECONNECTION'); ?>
        </h3>
	</header>

    <form class="akeeba-form--horizontal">
        <div id="akeeba-transfer-main-container">
            <div class="akeeba-form-group">
                <label  for="akeeba-transfer-url">
				    <?php echo \JText::_('COM_AKEEBA_TRANSFER_LBL_NEWURL'); ?>
                </label>

                <div class="akeeba-input-group">
                    <input type="text" id="akeeba-transfer-url" placeholder="http://www.example.com"
                           value="<?php echo $this->escape($this->newSiteUrl); ?>">
                    <span class="akeeba-input-group-btn">
                        <button onclick="akeeba.Transfer.onUrlChange(true); return false;" class="akeeba-btn--inverse" id="akeeba-transfer-btn-url">
		                    <?php echo \JText::_('COM_AKEEBA_TRANSFER_ERR_NEWURL_BTN'); ?>
                        </button>
                    </span>
                </div>
            </div>

            <div id="akeeba-transfer-row-url" class="akeeba-form-group--pull-right">
                <img src="<?php echo $this->escape(JUri::base()); ?>../media/com_akeeba/icons/loading.gif" id="akeeba-transfer-loading" style="display: none;" />

                <br/>

                <div id="akeeba-transfer-lbl-url" class="akeeba-help-text">
                    <p>
				        <?php echo \JText::_('COM_AKEEBA_TRANSFER_LBL_NEWURL_TIP'); ?>
                    </p>
                </div>
                <div id="akeeba-transfer-err-url-same" class="akeeba-block--failure" style="display: none;">
			        <?php echo \JText::_('COM_AKEEBA_TRANSFER_ERR_NEWURL_SAME'); ?>
                    <p style="text-align: center">
                        <iframe width="560" height="315" src="https://www.youtube.com/embed/vo_r0r6cZNQ" frameborder="0" allowfullscreen></iframe>
                    </p>
                </div>
                <div id="akeeba-transfer-err-url-invalid" class="akeeba-block--failure" style="display: none;">
			        <?php echo \JText::_('COM_AKEEBA_TRANSFER_ERR_NEWURL_INVALID'); ?>
                </div>
                <div id="akeeba-transfer-err-url-notexists" class="akeeba-block--failure" style="display: none;">
                    <p>
				        <?php echo \JText::_('COM_AKEEBA_TRANSFER_ERR_NEWURL_NOTEXISTS'); ?>
                    </p>
                    <p>
                        <button type="button" class="akeeba-btn--red" id="akeeba-transfer-err-url-notexists-btn-ignore">
                            &#9888;
					        <?php echo \JText::_('COM_AKEEBA_TRANSFER_ERR_NEWURL_BTN_IGNOREERROR'); ?>
                        </button>
                    </p>
                </div>
            </div>
        </div>

        <div id="akeeba-transfer-ftp-container" style="display: none">
            <div class="akeeba-form-group">
                <label for="akeeba-transfer-ftp-method" >
				    <?php echo \JText::_('COM_AKEEBA_TRANSFER_LBL_TRANSFERMETHOD'); ?>
                </label>

	            <?php echo \JHtml::_('select.genericlist', $this->transferOptions, 'akeeba-transfer-ftp-method', array(), 'value', 'text', $this->transferOption, 'akeeba-transfer-ftp-method'); ?>
	            <?php if($this->hasFirewalledMethods): ?>
                    <div class="akeeba-block--warning">
                        <h5>
				            <?php echo \JText::_('COM_AKEEBA_TRANSFER_WARN_FIREWALLED_HEAD'); ?>
                        </h5>
                        <p>
				            <?php echo \JText::_('COM_AKEEBA_TRANSFER_WARN_FIREWALLED_BODY'); ?>
                        </p>
                    </div>
	            <?php endif; ?>
            </div>

            <div class="akeeba-form-group">
                <label for="akeeba-transfer-ftp-host" >
				    <?php echo \JText::_('COM_AKEEBA_TRANSFER_LBL_FTP_HOST'); ?>
                </label>

                <input type="text" value="<?php echo $this->escape($this->ftpHost); ?>" id="akeeba-transfer-ftp-host" placeholder="ftp.example.com"/>
            </div>

            <div class="akeeba-form-group">
                <label for="akeeba-transfer-ftp-port" >
				    <?php echo \JText::_('COM_AKEEBA_TRANSFER_LBL_FTP_PORT'); ?>
                </label>
                <input type="text" value="<?php echo $this->escape($this->ftpPort); ?>" id="akeeba-transfer-ftp-port" placeholder="21"/>
            </div>

            <div class="akeeba-form-group">
                <label for="akeeba-transfer-ftp-username" >
				    <?php echo \JText::_('COM_AKEEBA_TRANSFER_LBL_FTP_USERNAME'); ?>
                </label>
                <input type="text" value="<?php echo $this->escape($this->ftpUsername); ?>" id="akeeba-transfer-ftp-username" placeholder="myUserName"/>
            </div>

            <div class="akeeba-form-group">
                <label for="akeeba-transfer-ftp-password" >
				    <?php echo \JText::_('COM_AKEEBA_TRANSFER_LBL_FTP_PASSWORD'); ?>
                </label>
                <input type="password" value="<?php echo $this->escape($this->ftpPassword); ?>" id="akeeba-transfer-ftp-password" placeholder="myPassword"/>
            </div>

            <div class="akeeba-form-group">
                <label for="akeeba-transfer-ftp-pubkey" >
				    <?php echo \JText::_('COM_AKEEBA_TRANSFER_LBL_FTP_PUBKEY'); ?>
                </label>
                <input type="text" value="<?php echo $this->escape($this->ftpPubKey); ?>" id="akeeba-transfer-ftp-pubkey" placeholder="<?php echo $this->escape(JPATH_SITE . DIRECTORY_SEPARATOR); ?>id_rsa.pub"/>
            </div>

            <div class="akeeba-form-group">
                <label for="akeeba-transfer-ftp-privatekey" >
				    <?php echo \JText::_('COM_AKEEBA_TRANSFER_LBL_FTP_PRIVATEKEY'); ?>
                </label>
                <input type="text" value="<?php echo $this->escape($this->ftpPrivateKey); ?>" id="akeeba-transfer-ftp-privatekey" placeholder="<?php echo $this->escape(JPATH_SITE . DIRECTORY_SEPARATOR); ?>id_rsa"/>
            </div>

            <div class="akeeba-form-group">
                <label for="akeeba-transfer-ftp-directory" >
				    <?php echo \JText::_('COM_AKEEBA_TRANSFER_LBL_FTP_DIRECTORY'); ?>
                </label>
                <input type="text" value="<?php echo $this->escape($this->ftpDirectory); ?>" id="akeeba-transfer-ftp-directory"
                       placeholder="public_html"/>
                <!--
					<button class="btn" type="button" id="akeeba-transfer-ftp-directory-browse">
						<?php echo \JText::_('COM_AKEEBA_CONFIG_UI_BROWSE'); ?>
					</button>
					<button class="btn" type="button" id="akeeba-transfer-ftp-directory-detect">
						<?php echo \JText::_('COM_AKEEBA_TRANSFER_BTN_FTP_DETECT'); ?>
					</button>
					-->
            </div>

			<!-- Chunk method -->
			<div class="akeeba-form-group">
				<label for="akeeba-transfer-chunkmode">
			        <?php echo JText::_('COM_AKEEBA_TRANSFER_LBL_TRANSFERMODE'); ?>
				</label>
				<?php echo \JHtml::_('select.genericlist', $this->chunkOptions, 'akeeba-transfer-chunkmode', array(), 'value', 'text', $this->chunkMode, 'akeeba-transfer-chunkmode'); ?>
				<p class="akeeba-help-text">
			        <?php echo JText::_('COM_AKEEBA_TRANSFER_LBL_TRANSFERMODE_INFO'); ?>
				</p>
			</div>

			<!-- Chunk size -->
			<div class="akeeba-form-group">
				<label for="akeeba-transfer-chunksize">
			        <?php echo JText::_('COM_AKEEBA_TRANSFER_LBL_CHUNKSIZE'); ?>
				</label>
				<?php echo \JHtml::_('select.genericlist', $this->chunkSizeOptions, 'akeeba-transfer-chunksize', array(), 'value', 'text', $this->chunkSize, 'akeeba-transfer-chunksize'); ?>
				<p class="akeeba-help-text">
			        <?php echo JText::_('COM_AKEEBA_TRANSFER_LBL_CHUNKSIZE_INFO'); ?>
				</p>
			</div>

            <div class="akeeba-form-group" id="akeeba-transfer-ftp-passive-container">
                <label for="akeeba-transfer-ftp-passive" >
				    <?php echo \JText::_('COM_AKEEBA_TRANSFER_LBL_FTP_PASSIVE'); ?>
                </label>
                <div class="akeeba-form-group--radio">
	                <?php echo \JHtml::_('FEFHelper.select.booleanlist', 'akeeba-transfer-ftp-passive', array(), $this->ftpPassive ? 1 : 0, 'JYES', 'JNO', 'akeeba-transfer-ftp-passive') ?>
                </div>
            </div>

            <div class="akeeba-form-group" id="akeeba-transfer-ftp-passive-fix-container">
                <label for="akeeba-transfer-ftp-passive-fix" >
				    <?php echo \JText::_('COM_AKEEBA_CONFIG_ENGINE_ARCHIVER_DIRECTFTPCURL_PASVWORKAROUND_TITLE'); ?>
                </label>
                <div class="akeeba-form-group--radio">
				    <?php echo \JHtml::_('FEFHelper.select.booleanlist', 'akeeba-transfer-ftp-passive-fix', array(), $this->ftpPassiveFix ? 1 : 0, 'JYES', 'JNO', 'akeeba-transfer-ftp-passive-fix') ?>
                </div>
                <p class="akeeba-help-text">
                    <?php echo \JText::_('COM_AKEEBA_CONFIG_ENGINE_ARCHIVER_DIRECTFTPCURL_PASVWORKAROUND_DESCRIPTION'); ?>
                </p>
            </div>

            <div class="akeeba-block--failure" id="akeeba-transfer-ftp-error" style="display:none;">
                <p id="akeeba-transfer-ftp-error-body">MESSAGE</p>

                <a href="index.php?option=com_akeeba&view=Transfer&force=1" class="akeeba-btn--orange" style="display:none" id="akeeba-transfer-ftp-error-force">
				    <?php echo JText::_('COM_AKEEBA_TRANSFER_ERR_OVERRIDE'); ?>
                </a>
            </div>

            <div class="akeeba-form-group--pull-right">
                <div class="akeeba-form-group--actions">
                    <button type="button" class="akeeba-btn--primary" id="akeeba-transfer-btn-apply">
			            <?php echo \JText::_('COM_AKEEBA_TRANSFER_BTN_FTP_PROCEED'); ?>
                    </button>
                </div>
            </div>

            <div id="akeeba-transfer-apply-loading" class="akeeba-block--info" style="display: none;">
                <h4>
			        <?php echo \JText::_('COM_AKEEBA_TRANSFER_LBL_VALIDATING'); ?>
                </h4>
                <p style="text-align: center;">
                    <img src="<?php echo $this->escape(JUri::base()); ?>../media/com_akeeba/icons/loading.gif"/>
                </p>
            </div>
        </div>
    </form>
</div>
