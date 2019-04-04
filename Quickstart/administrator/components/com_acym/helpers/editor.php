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

use Joomla\CMS\Editor\Editor AS Editor;

class acymeditorHelper
{
    var $width = '95%';
    var $height = '600';

    var $cols = 100;
    var $rows = 30;

    var $editor = '';
    var $name = 'editor_content';
    var $settings = 'editor_settings';
    var $stylesheet = 'editor_stylesheet';
    var $thumbnail = 'editor_thumbnail';
    var $content = '';
    var $editorContent = '';
    var $editorConfig = array();
    var $mailId = 0;

    public function display()
    {

        if ($this->isDragAndDrop()) {
            acym_addScript(false, ACYM_JS.'tinymce/tinymce.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'tinymce/tinymce.min.js'));
            include(ACYM_VIEW.'mails'.DS.'tmpl'.DS.'editor_wysid.php');
        } else {
            echo ACYM_CMS != 'WordPress' ? '</div></div><div class="acym_no_foundation"><div>' : '</div><div class="acym_no_foundation"><div>';
            $method = 'displayJoomla';
            $this->$method();
        }
    }

    public function isDragAndDrop()
    {
        return strpos($this->content, 'acym__wysid__template') !== false || $this->editor == 'acyEditor';
    }

    private function displayJoomla()
    {
        $this->editor = acym_getCMSConfig('editor', 'tinymce');

        $this->myEditor = Editor::getInstance($this->editor);
        $this->myEditor->initialise();

        $this->editorConfig['extended_elements'] = 'table[background|cellspacing|cellpadding|width|align|bgcolor|border|style|class|id],tr[background|width|bgcolor|style|class|id|valign],td[background|width|align|bgcolor|valign|colspan|rowspan|height|style|class|id|nowrap]';

        if (!empty($this->mailId)) {
            $cssurl = acym_completeLink((acym_isAdmin() ? '' : 'front').'mails&task=loadCSS&id='.$this->mailId.'&time='.time());
            $classMail = acym_get('class.mail');
            $filepath = $classMail->createTemplateFile($this->mailId);

            if ($this->editor == 'tinymce') {
                $this->editorConfig['content_css_custom'] = $cssurl.'&local=http';
                $this->editorConfig['content_css'] = '0';
            } elseif ($this->editor == 'jckeditor' || $this->editor == 'fckeditor') {
                $this->editorConfig['content_css_custom'] = $filepath;
                $this->editorConfig['content_css'] = '0';
                $this->editorConfig['editor_css'] = '0';
            } else {
                $fileurl = ACYM_MEDIA_FOLDER.'/templates/css/template_'.$this->mailId.'.css?time='.time();
                $this->editorConfig['custom_css_url'] = $cssurl;
                $this->editorConfig['custom_css_file'] = $fileurl;
                $this->editorConfig['custom_css_path'] = $filepath;
                acym_setVar('acycssfile', $fileurl);
            }
        }


        if (empty($this->editorContent)) {
            $this->content = htmlspecialchars($this->content, ENT_COMPAT, 'UTF-8');
            ob_start();
            echo $this->myEditor->display($this->name, $this->content, $this->width, $this->height, $this->cols, $this->rows, array('pagebreak', 'readmore'), null, 'com_content', null, $this->editorConfig);

            $this->editorContent = ob_get_clean();
        }

        if (method_exists($this->myEditor, 'save')) {
            acym_addScript(true, 'function acyOnSaveEditor(){'.$this->myEditor->save($this->name).'}');
        }

        echo $this->editorContent;
    }

    private function displayWordPress()
    {
        add_filter('mce_external_plugins', [$this, 'addPlugins']);
        add_filter('mce_buttons', [$this, 'addButtons']);
        add_filter('mce_buttons_2', [$this, 'addButtonsToolbar']);

        $mailClass = acym_get('class.mail');

        $mail = $mailClass->getOneById($this->mailId);
        $stylesheet = empty($mail) ? '' : trim(preg_replace('/\s\s+/', ' ', $mailClass->buildCSS($mail->stylesheet)));

        $options = array(
            'editor_css' => '<style type="text/css">
                                .alignleft{float:left;margin:0.5em 1em 0.5em 0;}
                                .aligncenter{display: block;margin-left: auto;margin-right: auto;}
                                .alignright{float: right;margin: 0.5em 0 0.5em 1em;}
                             </style>',
            'editor_height' => $this->height,
            'textarea_rows' => $this->rows,
            "wpautop" => false,
            'tinymce' => array(
                'content_css' => '',
                'content_style' => '.alignleft{float:left;margin:0.5em 1em 0.5em 0;} .aligncenter{display: block;margin-left: auto;margin-right: auto;} .alignright{float: right;margin: 0.5em 0 0.5em 1em;}'.$stylesheet,
            ),
        );

        wp_editor($this->content, $this->name, $options);
    }

    private function getWYSIDSettings()
    {
        if ($this->settings != 'editor_settings') {
            return $this->settings;
        }
        if (acym_getVar('int', 'id')) {
            if (acym_getVar('string', 'ctrl') == 'mails') {
                $query = 'SELECT settings FROM #__acym_mail WHERE id = '.acym_getVar('int', 'id');
            } elseif (acym_getVar('string', 'ctrl') == 'campaigns') {
                $query = 'SELECT settings FROM #__acym_mail AS mail JOIN #__acym_campaign AS campaign ON mail.id = campaign.mail_id WHERE campaign.id = '.acym_getVar('int', 'id');
            }

            return !empty(acym_loadObject($query)->settings) ? acym_loadObject($query)->settings : '{}';
        } else {
            return null;
        }
    }

    private function getWYSIDStylesheet()
    {
        if ($this->stylesheet != 'editor_stylesheet') {
            return $this->stylesheet;
        }
        if (acym_getVar('int', 'id')) {
            if (acym_getVar('string', 'ctrl') == 'mails') {
                $query = 'SELECT stylesheet FROM #__acym_mail WHERE id = '.acym_getVar('int', 'id');
            } elseif (acym_getVar('string', 'ctrl') == 'campaigns') {
                $query = 'SELECT stylesheet FROM #__acym_mail AS mail JOIN #__acym_campaign AS campaign ON mail.id = campaign.mail_id WHERE campaign.id = '.acym_getVar('int', 'id');
            }

            return !empty(acym_loadObject($query)->stylesheet) ? acym_loadObject($query)->stylesheet : '';
        } else {
            return null;
        }
    }

    private function getWYSIDThumbnail()
    {
        if ($this->thumbnail != 'editor_thumbnail') {
            return $this->thumbnail;
        }
        if (acym_getVar('int', 'id')) {
            $query = 'SELECT thumbnail FROM #__acym_mail WHERE id = '.acym_getVar('int', 'id');

            return !empty(acym_loadObject($query)->thumbnail) ? acym_loadObject($query)->thumbnail : '';
        } else {
            return null;
        }
    }

    private function addButtonAtPosition(&$buttons, $newButton, $after)
    {
        $position = array_search($after, $buttons);

        if ($position === false) {
            array_push($buttons, 'separator', $newButton);
        } else {
            array_splice($buttons, $position + 1, 0, $newButton);
        }
    }

    public function addPlugins($plugins)
    {
        $plugins['table'] = ACYM_JS.'tinymce/table.min.js';

        return $plugins;
    }

    public function addButtons($buttons)
    {
        $position = array_search('wp_more', $buttons);
        if ($position !== false) {
            $buttons[$position] = '';
        }

        array_unshift($buttons, 'separator', 'fontsizeselect');
        array_unshift($buttons, 'separator', 'fontselect');
        array_push($buttons, 'separator', 'table');

        $this->addButtonAtPosition($buttons, 'alignjustify', 'alignright');
        $this->addButtonAtPosition($buttons, 'underline', 'italic');
        $this->addButtonAtPosition($buttons, 'strikethrough', 'underline');

        return $buttons;
    }

    public function addButtonsToolbar($buttons)
    {
        $position = array_search('strikethrough', $buttons);
        if ($position !== false) {
            $buttons[$position] = '';
        }
        $this->addButtonAtPosition($buttons, 'backcolor', 'forecolor');

        return $buttons;
    }
}
