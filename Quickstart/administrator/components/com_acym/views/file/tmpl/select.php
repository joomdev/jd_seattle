<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form id="acym_form" enctype="multipart/form-data" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl')); ?>" method="post" name="acyForm">
    <div id="acym__file__select">
        <div class="acym__file__select grid-x">
            <div class="acym__file__select__arbo acym__file__select__area cell grid-x">
                <?php
                $fileTreeType = acym_get('type.fileTree');
                echo $fileTreeType->display($data['folders'], $data['uploadFolder'], 'currentFolder');
                ?>
            </div>
            <div class="acym__file__select__files acym__file__select__area cell grid-x large-up-4 medium-up-3 small-up-2 grid-margin-x">
                <?php
                if (empty($data['files'])) {
                    echo acym_translation('ACYM_NO_FILE_HERE');
                } else {
                    foreach ($data['files'] as $k => $file) {
                        $ext = strtolower(substr($file, strrpos($file, '.') + 1));

                        if (!in_array($ext, $data['allowedExtensions'])) {
                            continue;
                        }

                        if (in_array($ext, $data['imageExtensions'])) {
                            $srcImg = ACYM_LIVE.$data['uploadFolder'].'/'.$file;
                        } else {
                            $srcImg = ACYM_LIVE.ACYM_MEDIA_FOLDER.'/images/file.png';
                        }

                        if (strlen($file) > 20) {
                            $title = '<span class="cell acym__file__select__title" title="'.str_replace('"', '', $file).'">'.substr(rtrim($file, $ext), 0, 12).'...'.$ext.'</span>';
                        } else {
                            $title = '<span class="cell acym__file__select__title">'.$file.'</span>';
                        }
                        ?>

                        <div class="cell acym__file__select__onepic text-center">
                            <a href="#" class="acym__file__select__add grid-x" mapdata="<?php echo $file; ?>">
                                <?php echo $title; ?>
                                <div class="cell">
                                    <img src="<?php echo $srcImg; ?>"/>
                                </div>
                            </a>
                        </div>
                        <?php
                    }
                }
                ?>
                <input type="hidden" id="acym__file__select__mapid" value="<?php echo $data['map']; ?>">
            </div>
            <div class="acym__file__select__area cell grid-x text-center">
                <?php echo acym_inputFile('uploadedFile', '', '', 'cell medium-shrink') ?>
                <input type="hidden" name="currentFolder" value="<?php echo htmlspecialchars($data['uploadFolder'], ENT_COMPAT, 'UTF-8'); ?>"/>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($data['map'], ENT_COMPAT, 'UTF-8'); ?>"/>
                <div class="cell medium-auto hide-for-small-only"></div>
                <button type="button" class="cell medium-shrink button button-secondary acy_button_submit" id="acym__file__select__area__import" type="submit" data-task="select"> <?php echo acym_translation('ACYM_IMPORT'); ?> </button>
            </div>
        </div>
    </div>
    <?php echo acym_formOptions(true); ?>
</form>
