<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class FileController extends acymController
{
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultTask('select');
    }

    public function select()
    {
        $config = acym_config();
        $uploadFolders = $config->get('uploadfolder', ACYM_UPLOAD_FOLDER);
        $uploadFolder = acym_getVar('string', 'currentFolder', $uploadFolders);
        $uploadPath = acym_cleanPath(ACYM_ROOT.trim(str_replace('/', DS, trim($uploadFolder)), DS));
        $map = acym_getVar('string', 'id');
        acym_setVar('layout', 'select');

        $folders = acym_generateArborescence(array($uploadFolders));


        $uploadedFile = acym_getVar('array', 'uploadedFile', array(), 'files');
        if (!empty($uploadedFile) && !empty($uploadedFile['name'])) {
            $uploaded = acym_importFile($uploadedFile, $uploadPath, false);
            if ($uploaded) {
            }
        }



        $allowedExtensions = explode(',', $config->get('allowed_files'));
        $imageExtensions = array('jpg', 'jpeg', 'png', 'gif', 'ico', 'bmp', 'svg');
        $displayType = acym_getVar('string', 'displayType', 'icons');

        $files = array();
        if (file_exists($uploadPath)) {
            $files = acym_getFiles($uploadPath);
        }
        
        $data = array(
            'files' => $files,
            'uploadFolder' => $uploadFolder,
            'map' => $map,
            'displayType' => $displayType,
            'imageExtensions' => $imageExtensions,
            'allowedExtensions' => $allowedExtensions,
            'folders' => $folders,
        );

        parent::display($data);
    }
}
