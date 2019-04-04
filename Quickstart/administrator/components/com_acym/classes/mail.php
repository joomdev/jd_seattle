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

class acymmailClass extends acymClass
{
    var $table = 'mail';
    var $pkey = 'id';
    var $templateNames = array();
    var $checkAreas = true;

    public function getMatchingMails($settings)
    {
        $query = 'SELECT mail.* FROM #__acym_mail AS mail';
        $queryCount = 'SELECT COUNT(mail.id) FROM #__acym_mail AS mail';
        $queryStatus = 'SELECT COUNT(mail.type) AS number, mail.type FROM #__acym_mail AS mail';

        $filters = array();

        if (!empty($settings['tag'])) {
            $tagJoin = ' JOIN #__acym_tag AS tag ON mail.id = tag.id_element';
            $query .= $tagJoin;
            $queryCount .= $tagJoin;
            $queryStatus .= $tagJoin;
            $filters[] = 'tag.name = '.acym_escapeDB($settings['tag']);
            $filters[] = 'tag.type = "mail"';
        }

        if (!empty($settings['search'])) {
            $filters[] = 'mail.name LIKE '.acym_escapeDB('%'.$settings['search'].'%');
        }

        if (!empty($settings['type'])) {
            if ($settings['type'] == 'custom') {
                $filters[] .= 'mail.library = 0';
            } else {
                $filters[] .= 'mail.library = 1';
            }
        }

        if (!empty($settings['editor'])) {
            if ($settings['editor'] == 'html') {
                $filters[] .= 'mail.drag_editor = 0';
            } else {
                $filters[] .= 'mail.drag_editor = 1';
            }
        }

        if (empty($settings['onlyStandard'])) {
            $filters[] = 'mail.type != \'notification\'';
        } else {
            $filters[] = 'mail.type = \'standard\'';
        }

        $filters[] = 'mail.template = 1';

        if (!empty($filters)) {
            $queryStatus .= ' WHERE ('.implode(') AND (', $filters).')';
        }

        if (!empty($settings['status'])) {
            $allowedStatus = array(
                'standard',
                'welcome',
                'unsubscribe',
            );

            if (!in_array($settings['status'], $allowedStatus)) {
                die('Injection denied');
            }
            $filters[] = 'mail.type = "'.$settings['status'].'"';
        }

        if (!empty($filters)) {
            $query .= ' WHERE ('.implode(') AND (', $filters).')';
            $queryCount .= ' WHERE ('.implode(') AND (', $filters).')';
        }

        if (!empty($settings['ordering']) && !empty($settings['ordering_sort_order'])) {
            $query .= ' ORDER BY mail.'.acym_secureDBColumn($settings['ordering']).' '.strtoupper($settings['ordering_sort_order']);
        }

        $results['mails'] = acym_loadObjectList($query, '', $settings['offset'], $settings['mailsPerPage']);
        $results['total'] = acym_loadResult($queryCount);

        $mailsPerStatus = acym_loadObjectList($queryStatus.' GROUP BY type', 'type');
        $nbAllMail = 0;
        foreach ($mailsPerStatus as $oneMailType) {
            $nbAllMail += $oneMailType->number;
        }

        $results['status'] = array(
            'all' => $nbAllMail,
            'standard' => !empty($mailsPerStatus['standard']->number) ? $mailsPerStatus['standard']->number : 0,
            'welcome' => !empty($mailsPerStatus['welcome']->number) ? $mailsPerStatus['welcome']->number : 0,
            'unsubscribe' => !empty($mailsPerStatus['unsubscribe']->number) ? $mailsPerStatus['unsubscribe']->number : 0,
        );

        return $results;
    }

    public function getAll()
    {
        $query = 'SELECT * FROM #__acym_mail';

        return acym_loadObjectList($query);
    }

    public function getOneById($id)
    {
        $mail = acym_loadObject('SELECT * FROM #__acym_mail WHERE id = '.intval($id).' LIMIT 1');

        if (!empty($mail)) {
            $tagsClass = acym_get('class.tag');
            $mail->tags = $tagsClass->getAllTagsByElementId('mail', $id);
        }

        return $mail;
    }

    public function getOneByName($name)
    {
        $mail = acym_loadObject('SELECT * FROM #__acym_mail WHERE name = '.acym_escapeDB($name));

        if (!empty($mail)) {
            $tagsClass = acym_get('class.tag');
            $mail->tags = $tagsClass->getAllTagsByElementId('mail', $mail->id);
        }

        return $mail;
    }

    public function getMailsByType($typeMail, $settings)
    {
        if (empty($settings['key'])) {
            $settings['key'] = '';
        }
        if (empty($settings['offset'])) {
            $settings['offset'] = 0;
        }
        if (empty($settings['mailsPerPage'])) {
            $settings['mailsPerPage'] = 12;
        }

        $query = 'SELECT * FROM #__acym_mail AS mail';
        $queryCount = 'SELECT count(*) FROM #__acym_mail AS mail';

        $filters = array();
        $filters[] = 'mail.type = '.acym_escapeDB($typeMail);

        if (!empty($settings['search'])) {
            $filters[] = 'mail.name LIKE '.acym_escapeDB('%'.$settings['search'].'%');
        }

        $query .= ' WHERE ('.implode(') AND (', $filters).')';
        $queryCount .= ' WHERE ('.implode(') AND (', $filters).')';

        $query .= ' ORDER BY id DESC';

        $results['mails'] = acym_loadObjectList($query, $settings['key'], $settings['offset'], $settings['mailsPerPage']);
        $results['total'] = acym_loadResult($queryCount);

        return $results;
    }

    public function getAllListsWithCountSubscribersByMailIds($ids)
    {
        acym_arrayToInteger($ids);
        if (empty($ids)) {
            return array();
        }

        $query = 'SELECT mailLists.list_id, mailLists.mail_id, list.*, COUNT(userLists.user_id) AS subscribers 
                    FROM #__acym_mail_has_list AS mailLists 
                    JOIN #__acym_list AS list ON mailLists.list_id = list.id
                    LEFT JOIN #__acym_user_has_list AS userLists 
                        JOIN #__acym_user AS acyuser ON userLists.user_id = acyuser.id
                        AND userLists.status = 1
                        AND acyuser.active = 1 ';

        $config = acym_config();
        if ($config->get('require_confirmation', 1) == 1) {
            $query .= ' AND acyuser.confirmed = 1 ';
        }

        $query .= 'ON list.id = userLists.list_id    
                    WHERE mailLists.mail_id IN ('.implode(",", $ids).')
                    GROUP BY mailLists.list_id, mailLists.mail_id';

        return acym_loadObjectList($query);
    }

    public function getAllListsByMailId($id)
    {
        $query = 'SELECT list.*
                    FROM #__acym_mail_has_list AS mailLists
                    JOIN #__acym_list AS list ON mailLists.list_id = list.id
                    WHERE mailLists.mail_id IN ('.intval($id).')
                    GROUP BY mailLists.list_id, mailLists.mail_id';

        return acym_loadObjectList($query);
    }

    public function save($mail)
    {
        if (isset($mail->tags)) {
            $tags = $mail->tags;
            unset($mail->tags);
        }

        if (empty($mail->id) && empty($mail->creator_id)) {
            $mail->creator_id = acym_currentUserId();
        }

        foreach ($mail as $oneAttribute => $value) {
            if (empty($value) || in_array($oneAttribute, array('thumbnail', 'settings'))) {
                continue;
            }

            if ($oneAttribute == 'body') {
                $mail->$oneAttribute = preg_replace('#<input[^>]*value="[^"]*"[^>]*>#Uis', '', $mail->$oneAttribute);

                $mail->$oneAttribute = str_replace(' contenteditable="true"', '', $mail->$oneAttribute);
            } else {
                $mail->$oneAttribute = strip_tags($mail->$oneAttribute);
            }
        }

        $mailID = parent::save($mail);

        if (!empty($mailID) && isset($tags)) {
            $tagClass = acym_get('class.tag');
            $tagClass->setTags('mail', $mailID, $tags);
        }

        return $mailID;
    }

    public function delete($elements)
    {
        if (!is_array($elements)) {
            $elements = array($elements);
        }

        if (empty($elements)) {
            return 0;
        }

        $this->deleteMediaFolder($elements);
        acym_arrayToInteger($elements);

        $allThumbnailToDelete = acym_loadResultArray('SELECT thumbnail FROM #__acym_mail WHERE id IN ('.implode(',', $elements).')');

        $link = ACYM_CMS == 'WordPress' ? WP_CONTENT_DIR.DS.'uploads'.DS.'acymailing'.DS : ACYM_MEDIA.'images';

        foreach ($allThumbnailToDelete as $one) {
            if (!empty($one) && file_exists($link.$one)) {
                unlink($link.$one);
            }
        }

        acym_query('UPDATE #__acym_list SET welcome_id = null WHERE welcome_id IN ('.implode(',', $elements).')');
        acym_query('UPDATE #__acym_list SET unsubscribe_id = null WHERE unsubscribe_id IN ('.implode(',', $elements).')');
        acym_query('DELETE FROM #__acym_queue WHERE mail_id IN ('.implode(',', $elements).')');
        acym_query('DELETE FROM #__acym_mail_has_list WHERE mail_id IN ('.implode(',', $elements).')');
        acym_query("DELETE FROM #__acym_tag WHERE type = 'mail' AND id_element IN (".implode(',', $elements).")");
        acym_query('DELETE FROM #__acym_user_stat WHERE mail_id IN ('.implode(',', $elements).')');
        acym_query('DELETE FROM #__acym_url_click WHERE mail_id IN ('.implode(',', $elements).')');
        acym_query('DELETE FROM #__acym_mail_stat WHERE mail_id IN ('.implode(',', $elements).')');

        return parent::delete($elements);
    }

    public function deleteMediaFolder($elements)
    {
        $results = acym_loadResultArray('SELECT media_folder FROM #__acym_mail WHERE id IN ('.implode(',', $elements).')');

        foreach ($results as $template) {
            acym_deleteFolder(ACYM_TEMPLATE.$template);
        }
    }

    public function deleteOneAttachment($mailid, $idAttachment)
    {
        $mailid = intval($mailid);
        if (empty($mailid)) {
            return false;
        }
        $mail = $this->getOneById($mailid);

        $attachments = $mail->attachments;
        if (empty($attachments)) {
            return false;
        }
        $decodedAttach = json_decode($attachments, true);
        unset($decodedAttach[$idAttachment]);
        $attachdb = json_encode($decodedAttach);

        return acym_query('UPDATE #__acym_mail SET attachments = '.acym_escapeDB($attachdb).' WHERE id = '.$mailid.' LIMIT 1');
    }

    public function createTemplateFile($id)
    {
        if (empty($id)) {
            return '';
        }
        $cssfile = ACYM_TEMPLATE.'css'.DS.'template_'.$id.'.css';

        $template = $this->getOneById($id);
        if (empty($template->id)) {
            return '';
        }
        $css = $this->buildCSS($template->stylesheet);

        if (empty($css)) {
            return '';
        }

        acym_createDir(ACYM_TEMPLATE.'css');

        if (acym_writeFile($cssfile, $css)) {
            return $cssfile;
        } else {
            acym_enqueueNotification('Could not create the file '.$cssfile, 'error');

            return '';
        }
    }

    public function buildCSS($stylesheet)
    {
        $inline = '';

        if (preg_match_all('#@import[^;]*;#is', $stylesheet, $results)) {
            foreach ($results[0] as $oneResult) {
                $inline .= trim($oneResult)."\n";
                $stylesheet = str_replace($oneResult, '', $stylesheet);
            }
        }

        $inline .= $stylesheet;

        return $inline;
    }

    public function getAllTemplateForSelect($removeQueue = false)
    {
        $query = 'SELECT id, subject FROM #__acym_mail WHERE template IN(0, 2) AND name != "acy_report"';

        $mails = acym_loadObjectList($query, 'id');

        $return = array();

        $return[] = acym_translation('ACYM_SELECT_A_MAIL');

        foreach ($mails as $id => $mail) {
            $return[$id] = $mail->subject;
        }

        return $return;
    }

    function doupload()
    {
        $importFile = acym_getVar('none', 'uploadedfile', '', 'files');

        $fileError = $importFile['error'];
        if ($fileError > 0) {
            switch ($fileError) {
                case 1:
                    acym_enqueueNotification(acym_translation_sprintf('ACYM_FILE_UPLOAD_ERROR_1'), 'error');

                    return false;
                case 2:
                    acym_enqueueNotification(acym_translation_sprintf('ACYM_FILE_UPLOAD_ERROR_2'), 'error');

                    return false;
                case 3:
                    acym_enqueueNotification(acym_translation_sprintf('ACYM_FILE_UPLOAD_ERROR_3'), 'error');

                    return false;
                case 4:
                    acym_enqueueNotification(acym_translation_sprintf('ACYM_FILE_UPLOAD_ERROR_4'), 'error');

                    return false;
                default:
                    acym_enqueueNotification(acym_translation_sprintf('ACYM_FILE_UPLOAD_ERROR_5', $fileError), 'error');

                    return false;
            }
        }
        if (empty($importFile['name'])) {
            acym_enqueueNotification(acym_translation('ACYM_BROWSE_FILE'), 'error');

            return false;
        }

        $uploadPath = acym_cleanPath(ACYM_ROOT.ACYM_MEDIA_FOLDER.DS.'templates');

        if (!is_writable($uploadPath)) {
            @chmod($uploadPath, '0755');
            if (!is_writable($uploadPath)) {
                acym_enqueueNotification(acym_translation_sprintf('ACYM_WRITABLE_FOLDER', $uploadPath), 'warning');
            }
        }

        if (!(bool)ini_get('file_uploads')) {
            acym_enqueueNotification(acym_translation_sprintf('ACYM_FILE_UPLOAD_ERROR_6'), 'error');

            return false;
        }

        if (!extension_loaded('zlib')) {
            acym_raiseError(E_WARNING, 'SOME_ERROR_CODE', acym_translation('WARNINSTALLZLIB'));

            return false;
        }

        $filename = strtolower(acym_makeSafeFile($importFile['name']));
        $extension = strtolower(substr($filename, strrpos($filename, '.') + 1));

        if (!in_array($extension, array('zip', 'tar.gz'))) {
            acym_enqueueNotification(acym_translation_sprintf('ACYM_ACCEPTED_TYPE', $extension, 'zip,tar.gz'), 'error');

            return false;
        }

        $jpath = acym_getCMSConfig('tmp_path', ACYM_MEDIA.'tmp'.DS);
        $tmp_dest = acym_cleanPath($jpath.DS.$filename);
        $tmp_src = $importFile['tmp_name'];

        $uploaded = acym_uploadFile($tmp_src, $tmp_dest);
        if (!$uploaded) {
            acym_enqueueNotification(acym_translation_sprintf('ACYM_FILE_UPLOAD_ERROR_7', $tmp_src, $tmp_dest), 'error');

            return false;
        }

        $tmpdir = uniqid().'_template';

        $extractdir = acym_cleanPath(dirname($tmp_dest).DS.$tmpdir);

        $result = acym_extractArchive($tmp_dest, $extractdir);
        acym_deleteFile($tmp_dest);

        $allFiles = acym_getFiles($extractdir, '.', true, true, array(), array());
        foreach ($allFiles as $oneFile) {
            if (preg_match('#\.(jpg|gif|png|jpeg|ico|bmp|html|htm|css)$#i', $oneFile)) {
                continue;
            }
            if (acym_deleteFile($oneFile)) {
                acym_enqueueNotification(acym_translation_sprintf('ACYM_FILE_UPLOAD_ERROR_8', $oneFile), 'warning');
            }
        }

        if (!$result) {
            acym_enqueueNotification(acym_translation_sprintf('ACYM_FILE_UPLOAD_ERROR_9', $tmp_dest, $extractdir), 'error');

            return false;
        }

        if ($this->detecttemplates($extractdir)) {

            $messages = $this->templateNames;
            array_unshift($messages, acym_translation_sprintf('ACYM_TEMPLATES_INSTALL', count($this->templateNames)));
            acym_enqueueNotification($messages, 'success');
            if (is_dir($extractdir)) acym_deleteFolder($extractdir);

            return true;
        }

        acym_enqueueNotification(acym_translation_sprintf('ACYM_FILE_UPLOAD_ERROR_10'), 'error');
        if (is_dir($extractdir)) acym_deleteFolder($extractdir);

        return false;
    }

    function detecttemplates($folder)
    {
        $allFiles = acym_getFiles($folder);
        if (!empty($allFiles)) {
            foreach ($allFiles as $oneFile) {
                if (preg_match('#^.*(html|htm)$#i', $oneFile)) {
                    if ($this->installtemplate($folder.DS.$oneFile)) return true;
                }
            }
        }

        $status = false;
        $allFolders = acym_getFolders($folder);
        if (!empty($allFolders)) {
            foreach ($allFolders as $oneFolder) {
                $status = $this->detecttemplates($folder.DS.$oneFolder) || $status;
            }
        }

        return $status;
    }

    function installtemplate($filepath)
    {
        $fileContent = acym_fileGetContent($filepath);

        $newTemplate = new stdClass();
        $newTemplate->name = trim(preg_replace('#[^a-z0-9]#i', ' ', substr(dirname($filepath), strpos($filepath, '_template'))));
        if (preg_match('#< *title[^>]*>(.*)< */ *title *>#Uis', $fileContent, $results) && !empty($results[1])) $newTemplate->name = $results[1];

        if (preg_match('#< *meta *name="description" *content="([^"]*)"#Uis', $fileContent, $results) && !empty($results[1])) $newTemplate->description = $results[1];
        if (preg_match('#< *meta *name="fromname" *content="([^"]*)"#Uis', $fileContent, $results) && !empty($results[1])) $newTemplate->fromname = $results[1];
        if (preg_match('#< *meta *name="fromemail" *content="([^"]*)"#Uis', $fileContent, $results) && !empty($results[1])) $newTemplate->fromemail = $results[1];
        if (preg_match('#< *meta *name="replyname" *content="([^"]*)"#Uis', $fileContent, $results) && !empty($results[1])) $newTemplate->replyname = $results[1];
        if (preg_match('#< *meta *name="replyemail" *content="([^"]*)"#Uis', $fileContent, $results) && !empty($results[1])) $newTemplate->replyemail = $results[1];

        $newFolder = preg_replace('#[^a-z0-9]#i', '_', strtolower($newTemplate->name));
        $newTemplateFolder = $newFolder;
        $i = 1;
        while (is_dir(ACYM_TEMPLATE.$newTemplateFolder)) {
            $newTemplateFolder = $newFolder.'_'.$i;
            $i++;
        }
        $newTemplate->media_folder = $newTemplateFolder;

        $moveResult = acym_copyFolder(dirname($filepath), ACYM_TEMPLATE.$newTemplateFolder);
        if ($moveResult !== true) {
            acym_display(array(acym_translation_sprintf('ACYM_ERROR_COPYING_FOLDER_TO', dirname($filepath), ACYM_TEMPLATE.$newTemplateFolder), $moveResult), 'error');

            return false;
        }

        if (!file_exists(ACYM_TEMPLATE.$newTemplateFolder.DS.'index.html')) {
            $indexFile = '<html><body bgcolor="#FFFFFF"></body></html>';
            acym_writeFile(ACYM_TEMPLATE.$newTemplateFolder.DS.'index.html', $indexFile);
        }

        $fileContent = str_replace(
            array(
                'src="./',
                'src="../',
                'src="images/',
            ),
            array(
                'src="'.ACYM_TEMPLATE_URL.$newTemplateFolder.'/',
                'src="'.ACYM_TEMPLATE_URL,
                'src="'.ACYM_TEMPLATE_URL.$newTemplateFolder.'/images/',
            ),
            $fileContent
        );

        $fileContent = preg_replace('#(src|background)[ ]*=[ ]*\"(?!(https?://|/))(?:\.\./|\./)?#', '$1="'.ACYM_TEMPLATE_URL.$newTemplateFolder.'/', $fileContent);

        if (preg_match('#< *body[^>]*>(.*)< */ *body *>#Uis', $fileContent, $results)) {
            $newTemplate->body = $results[1];
        } else {
            $newTemplate->body = $fileContent;
        }

        $newTemplate->stylesheet = '';
        if (preg_match_all('#< *style[^>]*>(.*)< */ *style *>#Uis', $fileContent, $results)) {
            $newTemplate->stylesheet .= preg_replace('#(<!--|-->)#s', '', implode("\n", $results[1]));
        }
        $cssFiles = array();
        $cssFiles[ACYM_TEMPLATE.$newTemplateFolder] = acym_getFiles(ACYM_TEMPLATE.$newTemplateFolder, '\.css$');
        $subFolders = acym_getFolders(ACYM_TEMPLATE.$newTemplateFolder);
        foreach ($subFolders as $oneFolder) {
            $cssFiles[ACYM_TEMPLATE.$newTemplateFolder.DS.$oneFolder] = acym_getFiles(ACYM_TEMPLATE.$newTemplateFolder.DS.$oneFolder, '\.css$');
        }

        foreach ($cssFiles as $cssFolder => $cssFile) {
            if (empty($cssFile)) continue;
            $newTemplate->stylesheet .= "\n".acym_fileGetContent($cssFolder.DS.reset($cssFile));
        }

        if (!empty($newTemplate->stylesheet)) {
            if (preg_match('#body *\{[^\}]*background-color:([^;\}]*)[;\}]#Uis', $newTemplate->stylesheet, $backgroundresults)) {
                $newTemplate->stylesheet = preg_replace('#(body *\{[^\}]*)background-color:[^;\}]*[;\}]#Uis', '$1', $newTemplate->stylesheet);
            }

            $quickstyle = array('tag_h1' => 'h1', 'tag_h2' => 'h2', 'tag_h3' => 'h3', 'tag_h4' => 'h4', 'tag_h5' => 'h5', 'tag_h6' => 'h6', 'tag_a' => 'a', 'tag_ul' => 'ul', 'tag_li' => 'li', 'acym_unsub' => '\.acym_unsub', 'acym_online' => '\.acym_online', 'acym_title' => '\.acym_title', 'acym_content' => '\.acym_content', 'acym_readmore' => '\.acym_readmore');
            foreach ($quickstyle as $styledb => $oneStyle) {
                if (preg_match('#[^a-z\. ,] *'.$oneStyle.' *{([^}]*)}#Uis', $newTemplate->stylesheet, $quickstyleresults)) {
                    $newTemplate->stylesheet = str_replace($quickstyleresults[0], '', $newTemplate->stylesheet);
                }
            }
        }

        $foldersForPicts = array($newTemplateFolder);
        $otherFolders = acym_getFolders(ACYM_TEMPLATE.$newTemplateFolder);
        foreach ($otherFolders as $oneFold) {
            $foldersForPicts[] = $newTemplateFolder.DS.$oneFold;
        }
        $allPictures = array();
        foreach ($foldersForPicts as $oneFolder) {
            $allPictures[$oneFolder] = acym_getFiles(ACYM_TEMPLATE.$oneFolder);
        }

        $uploadsFolder = ACYM_UPLOAD_FOLDER_THUMBNAIL;

        $config = acym_config();
        $newConfig = new stdClass();
        $thumbNb = intval($config->get('numberThumbnail', 2));

        foreach ($allPictures as $folder => $pictfolders) {
            foreach ($pictfolders as $onePict) {
                if (!preg_match('#\.(jpg|gif|png|jpeg|ico|bmp)$#i', $onePict)) continue;
                if (preg_match('#(thumbnail|screenshot|muestra)#i', $onePict)) {
                    $thumbNb++;
                    $newNamePict = 'thumbnail_'.$thumbNb.'.png';
                    copy(ACYM_TEMPLATE.str_replace(DS, '/', $folder).'/'.$onePict, $uploadsFolder.$newNamePict);
                    $newTemplate->thumbnail = $newNamePict;
                }
            }
        }

        $newConfig->numberThumbnail = $thumbNb;
        $config->save($newConfig);

        $newTemplate->drag_editor = 0;
        $newTemplate->type = "standard";
        $newTemplate->template = 1;
        $newTemplate->library = 0;
        $newTemplate->creation_date = acym_date('now', 'Y-m-d H:i:s');

        $tempid = $this->save($newTemplate);

        $this->templateId = $tempid;
        $this->templateNames[] = $newTemplate->name;

        return true;
    }
}
