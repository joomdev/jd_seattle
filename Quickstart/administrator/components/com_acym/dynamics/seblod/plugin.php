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

class plgAcymSeblod extends acymPlugin
{
    function __construct()
    {
        parent::__construct();
        $this->cms = 'Joomla';
        if (!defined('JPATH_ADMINISTRATOR') || !file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_cck'.DS)) {
            $this->installed = false;
        }
    }

    function insertOptions()
    {
        $plugins = new stdClass();
        $plugins->name = 'Seblod';
        $plugins->icon = ACYM_DYNAMICS_URL.basename(__DIR__).'/icon.svg';
        $plugins->plugin = __CLASS__;

        return $plugins;
    }

    function _categories($filter_cat)
    {
        $mosetCats = acym_loadObjectList('SELECT id,parent_id,title,extension FROM #__categories WHERE extension = "com_content" ORDER BY `id` DESC');
        $this->cats = array();
        if (!empty($mosetCats)) {
            foreach ($mosetCats as $oneCat) {
                $this->cats[$oneCat->parent_id][] = $oneCat;
            }
        }
        $this->catvalues = array();
        $this->catvalues[] = acym_selectOption(0, acym_translation('ACYM_ALL'));
        $this->_handleChildren();

        return acym_select($this->catvalues, 'plugin_category', (int)$filter_cat, 'style="width: 150px;"', 'value', 'text');
    }

    function _handleChildren($parent_id = 1, $level = 0)
    {
        if (empty($this->cats[$parent_id])) {
            return;
        }
        foreach ($this->cats[$parent_id] as $cat) {
            if ($cat->title != "ROOT" && $cat->extension == "com_content") {
                $this->catvalues[] = acym_selectOption($cat->id, str_repeat(" - - ", $level).$cat->title);
            }
            $this->_handleChildren($cat->id, $level + 1);
        }
    }

    function contentPopup()
    {
        $tabHelper = acym_get('helper.tab');
        $tabHelper->startTab(acym_translation('ACYM_ONE_BY_ONE'));

        $fields = array(
            'title' => array('ACYM_TITLE', true),
            'introtext' => array('ACYM_INTRO_TEXT', true),
            'fulltext' => array('ACYM_FULL_TEXT', false),
            'created' => array('ACYM_DATE_CREATED', false),
            'pubdate' => array('ACYM_PUBLISHING_DATE', false),
            'image' => array('ACYM_IMAGE', true),
        );

        $query = 'SELECT a.name, a.title 
                    FROM `#__cck_core_fields` AS a 
                    WHERE a.published = 1 
                        AND (a.storage LIKE "custom" 
                            OR a.storage_table LIKE "#__cck_store_item_content" 
                            OR a.storage_field LIKE "introtext" 
                            OR a.folder = 1) 
                    ORDER BY a.title';
        $customFields = acym_loadObjectList($query);

        if (!empty($customFields)) {
            foreach ($customFields as $onefield) {
                if (in_array($onefield->name, array('art_introtext', 'art_fulltext', 'cat_description'))) {
                    continue;
                }

                $fields[$onefield->name] = array($onefield->title, false);
            }
        }

        $displayOptions = array(
            array(
                'title' => 'ACYM_FIELDS_TO_DISPLAY',
                'type' => 'checkbox',
                'name' => 'displays',
                'options' => $fields,
                'separator' => '; ',
            ),
            array(
                'title' => 'ACYM_CLICKABLE_TITLE',
                'type' => 'boolean',
                'name' => 'clickable',
                'default' => true,
            ),
            array(
                'title' => 'ACYM_DISPLAY_PICTURES',
                'type' => 'pictures',
                'name' => 'pictures',
            ),
        );

        echo $this->acympluginHelper->displayOptions($displayOptions, 'Seblod');

        $filter_cat = acym_getVar('int', 'plugin_category', 0);
        echo '<div class="grid-x grid-margin-x" id="plugin_listing_filters">
                <div class="cell medium-6">
                    <input type="text" name="plugin_search" placeholder="'.acym_escape(acym_translation('ACYM_SEARCH')).'"/>
                </div>
                <div class="cell medium-6 grid-x">
                    <div class="cell hide-for-small-only medium-auto"></div>
                    <div class="cell medium-shrink">
                    '.$this->_categories($filter_cat).'
                    </div>
                </div>
            </div>';
        echo '<div id="plugin_listing" class="acym__popup__listing padding-0">';
        $this->displayListing();
        echo '</div>';
        $tabHelper->endTab();

        $tabHelper->startTab(acym_translation('ACYM_BY_CATEGORY'));

        $catOptions = array(
            array(
                'title' => 'ACYM_ORDER_BY',
                'type' => 'select',
                'name' => 'order',
                'options' => array(
                    'id' => 'ACYM_ID',
                    'created' => 'ACYM_DATE_CREATED',
                    'modified' => 'ACYM_MODIFICATION_DATE',
                    'title' => 'ACYM_TITLE',
                    'rand' => 'ACYM_RANDOM',
                ),
            ),
            array(
                'title' => 'ACYM_MAX_NB_ELEMENTS',
                'type' => 'text',
                'name' => 'max',
                'default' => 20,
            ),
            array(
                'title' => 'ACYM_COLUMNS',
                'type' => 'text',
                'name' => 'cols',
                'default' => 1,
            ),
        );

        $displayOptions = array_merge($displayOptions, $catOptions);

        echo $this->acympluginHelper->displayOptions($displayOptions, 'autoSeblod', 'grouped');

        if (!empty($this->catvalues)) {
            echo '<div class="acym__popup__listing padding-0">';
            foreach ($this->catvalues as $oneCat) {
                if (empty($oneCat->value)) {
                    continue;
                }
                echo '<div class="cell grid-x acym__listing__row acym__listing__row__popup" data-id="'.$oneCat->value.'" onclick="applyContentautoSeblod('.$oneCat->value.', this);">
                        <div class="cell medium-5">'.$oneCat->text.'</div>
                    </div>';
            }
            echo '</div>';
        }

        $tabHelper->endTab();

        $tabHelper->display('plugin');
    }

    function displayListing()
    {
        echo '<input type="hidden" name="plugin" value="'.__CLASS__.'" />';
        $query = 'SELECT SQL_CALC_FOUND_ROWS a.*,b.*,c.*,a.id AS gID, a.title AS gtitle 
                    FROM `#__content` AS a 
                    JOIN #__categories AS b ON a.catid = b.id 
                    LEFT JOIN `#__users` AS c ON a.created_by = c.id';

        $pageInfo = new stdClass();
        $pageInfo->limit = acym_getCMSConfig('list_limit');
        $pageInfo->page = acym_getVar('int', 'pagination_page_ajax', 1);
        $pageInfo->start = ($pageInfo->page - 1) * $pageInfo->limit;
        $pageInfo->search = acym_getVar('string', 'plugin_search', '');
        $pageInfo->filter_cat = acym_getVar('int', 'plugin_category', 0);
        $pageInfo->order = 'a.id';
        $pageInfo->orderdir = 'DESC';

        $searchFields = array('a.id', 'a.title', 'b.title', 'c.username');
        if (!empty($pageInfo->search)) {
            $searchVal = '%'.acym_getEscaped($pageInfo->search, true).'%';
            $filters[] = implode(" LIKE ".acym_escapeDB($searchVal)." OR ", $searchFields)." LIKE ".acym_escapeDB($searchVal);
        }
        $filters[] = "a.state != -2";
        if (!empty($pageInfo->filter_cat)) {
            $filters[] = "a.catid = ".intval($pageInfo->filter_cat);
        }
        if (!empty($filters)) {
            $query .= ' WHERE ('.implode(') AND (', $filters).')';
        }
        if (!empty($pageInfo->order)) {
            $query .= ' ORDER BY '.$pageInfo->order.' '.$pageInfo->orderdir;
        }

        $rows = acym_loadObjectList($query, '', $pageInfo->start, $pageInfo->limit);
        $pageInfo->total = acym_loadResult('SELECT FOUND_ROWS()');

        $selected = explode(',', acym_getVar('string', 'selected', ''));

        echo '<div class="cell grid-x hide-for-small-only">
                <div class="cell medium-5">'.acym_translation('ACYM_TITLE').'</div>
                <div class="cell medium-3">'.acym_translation('ACYM_DATE_CREATED').'</div>
                <div class="cell medium-3">'.acym_translation('ACYM_CATEGORY').'</div>
                <div class="cell medium-1">'.acym_translation('ACYM_ID').'</div>
            </div>';
        foreach ($rows as $row) {
            if (strpos($row->created, ': ') != false) {
                $row->created = str_replace('/', '', strrchr(strip_tags($row->created), '/'));
            }
            $class = 'cell grid-x acym__listing__row acym__listing__row__popup';
            if (in_array($row->gID, $selected)) {
                $class .= ' selected_row';
            }
            echo '<div class="'.$class.'" data-id="'.$row->gID.'" onclick="applyContentSeblod('.$row->gID.', this);">
                    <div class="cell medium-5">'.$row->gtitle.'</div>
                    <div class="cell medium-3">'.$row->created.'</div>
                    <div class="cell medium-3">'.$row->title.'</div>
                    <div class="cell medium-1">'.$row->gID.'</div>
                </div>';
        }

        if (empty($rows)) {
            echo '<h1 class="cell acym__listing__empty__search__modal text-center">'.acym_translation('ACYM_NO_RESULTS_FOUND').'</h1>';
        }

        $pagination = acym_get('helper.pagination');
        $pagination->setStatus($pageInfo->total, $pageInfo->page, $pageInfo->limit);
        echo $pagination->displayAjax();
    }

    function replaceContent(&$email)
    {
        $this->_replaceAuto($email);
        $this->_replaceOne($email);
    }

    private function _replaceOne(&$email)
    {
        $tags = $this->acympluginHelper->extractTags($email, 'Seblod');
        if (empty($tags)) {
            return;
        }

        require_once(JPATH_SITE.DS.'plugins'.DS.'content'.DS.'cck'.DS.'cck.php');
        require_once(__DIR__.DS.'acyseblodfield.php');

        $menuid = acym_loadResult('SELECT id FROM #__menu WHERE link LIKE "%index.php?option=com_content&view=article%" LIMIT 1');
        $this->itemId = empty($menuid) ? '' : '&Itemid='.$menuid;


        acym_loadLanguageFile('com_cck_default', JPATH_SITE);

        $this->addedcss = false;

        if (!empty($email->language)) {
            $this->newslanguage = acym_loadObject('SELECT lang_id, lang_code FROM #__languages WHERE sef = '.acym_escapeDB($email->language).' LIMIT 1');
        }

        $this->readmore = empty($email->template->readmore) ? acym_translation('ACYM_READ_MORE') : '<img src="'.ACYM_LIVE.$email->template->readmore.'" alt="'.acym_translation('ACYM_READ_MORE', true).'" />';

        $tagsReplaced = array();
        foreach ($tags as $i => $oneTag) {
            if (isset($tagsReplaced[$i])) {
                continue;
            }
            $tagsReplaced[$i] = $this->_replaceContent($oneTag);
        }

        $this->acympluginHelper->replaceTags($email, $tagsReplaced, true);
    }

    function _replaceContent(&$tag)
    {
        if (!empty($tag->displays)) {
            $tag->displays = explode(';', $tag->displays);
        } else {
            $tag->displays = array('title');
        }

        foreach ($tag->displays as $i => $oneField) {
            $tag->displays[$i] = trim($oneField);
        }
        $query = 'SELECT a.*,b.alias AS catalias,c.name AS username FROM #__content AS a ';
        $query .= 'JOIN #__categories AS b ON a.catid = b.id ';
        $query .= 'LEFT JOIN #__users AS c ON a.created_by = c.id ';
        $query .= 'WHERE a.id = '.intval($tag->id).' LIMIT 1';
        $article = acym_loadObject($query);
        $result = '';
        $varFields = array();

        if (empty($article)) {
            if (acym_isAdmin()) {
                acym_enqueueMessage('The article "'.$tag->id.'" could not be loaded', 'notice');
            }

            return $result;
        }

        $link = 'index.php?option=com_content&view=article&id='.$article->id.'&catid='.$article->catid.''.$this->itemId;
        $varFields['{link}'] = $link;
        $resultTitle = $article->title;
        $created = '';
        if (in_array('title', $tag->displays)) {
            if (!empty($tag->clickable)) {
                $resultTitle = '<a href="'.$link.'" target="_blank" >'.$resultTitle.'</a>';
            }
            $resultTitle = '<tr><td colspan="2"><h2 class="acym_title">'.$resultTitle.'</h2></td></tr>';
        }
        $varFields['{created}'] = acym_getDate(acym_getTime($article->created), acym_translation('ACYM_DATE_FORMAT_LC1'));
        if (in_array('created', $tag->displays)) {
            $created = '<tr><td>'.acym_translation('ACYM_DATE_CREATED').' : </td><td>'.$varFields['{created}'].'</td></tr>';
        }

        $pubdate = '';
        $varFields['{pubdate}'] = acym_getDate(acym_getTime($article->publish_up), acym_translation('ACYM_DATE_FORMAT_LC1'));
        if (in_array('pubdate', $tag->displays)) {
            $pubdate = '<tr><td>'.acym_translation('ACYM_PUBLISHING_DATE').' : </td><td>'.$varFields['{pubdate}'].'</td></tr>';
        }

        $answer = array();
        preg_match_all('#::([^/:]+)::(.*)::/#Uis', $article->introtext, $fields);

        if (!empty($fields)) {
            foreach ($fields[1] as $i => $property) {
                $answer[$property] = $fields[2][$i];
            }
        }

        $description = '';

        if (in_array('image', $tag->displays) && !empty($article->images) && !empty($tag->pict)) {
            $images = json_decode($article->images);
            $pictVar = 'image_intro';
            if (empty($images->$pictVar)) {
                $pictVar = 'image_fulltext';
            }
            if (!empty($images->$pictVar)) {
                $varFields['{picthtml}'] = '<img style="float:left;padding-right:10px;padding-bottom:10px;" alt="" border="0" src="'.acym_rootURI().$images->$pictVar.'" />';
                $result .= $varFields['{picthtml}'];
            }
        }

        if (in_array('introtext', $tag->displays) && !empty($answer['introtext'])) {
            $varFields['{introtext}'] = $answer['introtext'];
            $description .= '<tr><td colspan="2">'.$answer['introtext'].'</td></tr>';
        }
        if (in_array('fulltext', $tag->displays) && !empty($answer['fulltext'])) {
            $varFields['{fulltext}'] = $answer['fulltext'];
            $description .= '<tr><td colspan="2">'.$answer['fulltext'].'</td></tr>';
        }
        $article->text = $article->introtext.$article->fulltext;

        $params = array();
        $acyCCK = new AcyplgContentCCK();
        $acyCCK->acyDisplays = $tag->displays;
        $acyCCK->onContentPrepare('com_content.article', $article, $params, 0);

        foreach ($article as $fieldName => $oneField) {
            $varFields['{'.$fieldName.'}'] = $oneField;
        }

        $result .= $article->text;

        if (strlen(strip_tags($description)) < 3) {
            $description = '';
        }

        $result = '<div class="acym_content" style="clear:both"><table cellspacing="0" cellpadding="5" border="0" style="width:100%;">'.$resultTitle.$description.$created.$pubdate.'<tr><td colspan="2">'.$result.'</td></tr></table></div>';
        $result = preg_replace('#administrator/#', '', $result);

        $result = str_replace('&nbsp;', ' ', $result);
        $result = preg_replace('#<iframe[^>]*(http[^"]*embed/)([^"]*)[^<]*</iframe>#', '<a href="$1$2" target="_blank"><img src="http://img.youtube.com/vi/$2/1.jpg"/></a>', $result);
        $result = str_replace('/embed/', '/watch?v=', $result);

        if (!empty($tag->tmpl) && file_exists(ACYM_MEDIA.'plugins'.DS.'seblod_'.$tag->tmpl.'.php')) {
            ob_start();
            require(ACYM_MEDIA.'plugins'.DS.'seblod_'.$tag->tmpl.'.php');
            $result = ob_get_clean();
            $result = str_replace(array_keys($varFields), $varFields, $result);
        } elseif (file_exists(ACYM_MEDIA.'plugins'.DS.'seblod.php')) {
            ob_start();
            require(ACYM_MEDIA.'plugins'.DS.'seblod.php');
            $result = ob_get_clean();
            $result = str_replace(array_keys($varFields), $varFields, $result);
        }


        if (!$this->addedcss) {
            $result .= '<style type="text/css">
							div.cck_value, div.cck_label {
								vertical-align: top;
								display: inline-block;
							}

							div.cck_label {
								min-width: 50px;
							}
							</style>';
            $this->addedcss = true;
        }

        $result = $this->acympluginHelper->managePicts($tag, $result);

        return $result;
    }

    function _replaceAuto(&$email)
    {
        $this->generateByCategory($email);
        if (empty($this->tags)) {
            return;
        }
        $this->acympluginHelper->replaceTags($email, $this->tags, true);
    }

    function generateByCategory(&$email)
    {
        $tags = $this->acympluginHelper->extractTags($email, 'autoSeblod');
        $return = new stdClass();
        $return->status = true;
        $return->message = '';
        $this->tags = array();

        if (empty($tags)) {
            return $return;
        }

        foreach ($tags as $oneTag => $parameter) {
            if (isset($this->tags[$oneTag])) {
                continue;
            }
            $allcats = explode('-', $parameter->id);
            $selectedArea = array();
            foreach ($allcats as $oneCat) {
                if (empty($oneCat)) {
                    continue;
                }
                $selectedArea[] = intval($oneCat);
            }
            $query = 'SELECT id FROM #__content';
            $where = array();

            if (!empty($selectedArea)) {
                $where[] = 'catid IN ('.implode(',', $selectedArea).')';
            }

            $where[] = "state = 1";
            $query .= ' WHERE ('.implode(') AND (', $where).')';
            if (!empty($parameter->order)) {
                $ordering = explode(',', $parameter->order);
                if ($ordering[0] == 'rand') {
                    $query .= ' ORDER BY rand()';
                } else {
                    $query .= ' ORDER BY `'.acym_secureDBColumn(trim($ordering[0])).'` '.acym_secureDBColumn(trim($ordering[1]));
                }
            }

            if (!empty($parameter->max)) {
                $query .= ' LIMIT '.intval($parameter->max);
            } else {
                $query .= ' LIMIT 20';
            }

            $allArticles = acym_loadResultArray($query);
            $stringTag = '';
            if (!empty($allArticles)) {
                if (file_exists(ACYM_MEDIA.'plugins'.DS.'autoseblod.php')) {
                    ob_start();
                    require(ACYM_MEDIA.'plugins'.DS.'autoseblod.php');
                    $stringTag = ob_get_clean();
                } else {
                    $arrayElements = array();
                    foreach ($allArticles as $oneArticleId) {
                        $args = array();
                        $args[] = 'Seblod:'.$oneArticleId;
                        if (!empty($parameter->displays)) {
                            $args[] = 'displays:'.$parameter->displays;
                        }
                        if (!empty($parameter->clickable)) {
                            $args[] = 'clickable:'.$parameter->clickable;
                        }
                        if (isset($parameter->tmpl)) {
                            $args[] = 'tmpl:'.$parameter->tmpl;
                        }
                        if (isset($parameter->pict)) {
                            $args[] = 'pict:'.$parameter->pict;
                        }
                        if (!empty($parameter->maxwidth)) {
                            $args[] = 'maxwidth:'.$parameter->maxwidth;
                        }
                        if (!empty($parameter->maxheight)) {
                            $args[] = 'maxheight:'.$parameter->maxheight;
                        }
                        $arrayElements[] = '{'.implode('|', $args).'}';
                    }
                    $stringTag = $this->acympluginHelper->getFormattedResult($arrayElements, $parameter);
                }
            }
            $this->tags[$oneTag] = $stringTag;
        }

        return $return;
    }
}
