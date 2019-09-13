<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.2.2
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
        $this->name = 'seblod';
    }

    function insertOptions()
    {
        $plugin = new stdClass();
        $plugin->name = 'Seblod';
        $plugin->icon = ACYM_DYNAMICS_URL.basename(__DIR__).'/icon.svg';
        $plugin->plugin = __CLASS__;

        return $plugin;
    }

    function contentPopup()
    {
        $this->categories = acym_loadObjectList('SELECT id, parent_id, title FROM #__categories WHERE extension = "com_content" ORDER BY `id` DESC');

        $tabHelper = acym_get('helper.tab');
        $tabHelper->startTab(acym_translation('ACYM_ONE_BY_ONE'));

        $fields = [
            'title' => ['ACYM_TITLE', true],
            'introtext' => ['ACYM_INTRO_TEXT', true],
            'fulltext' => ['ACYM_FULL_TEXT', false],
            'created' => ['ACYM_DATE_CREATED', false],
            'pubdate' => ['ACYM_PUBLISHING_DATE', false],
            'image' => ['ACYM_IMAGE', true],
        ];

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
                if (in_array($onefield->name, ['art_introtext', 'art_fulltext', 'cat_description'])) {
                    continue;
                }

                $fields[$onefield->name] = [$onefield->title, false];
            }
        }

        $displayOptions = [
            [
                'title' => 'ACYM_FIELDS_TO_DISPLAY',
                'type' => 'checkbox',
                'name' => 'displays',
                'options' => $fields,
                'separator' => '; ',
            ],
            [
                'title' => 'ACYM_CLICKABLE_TITLE',
                'type' => 'boolean',
                'name' => 'clickable',
                'default' => true,
            ],
            [
                'title' => 'ACYM_DISPLAY_PICTURES',
                'type' => 'pictures',
                'name' => 'pictures',
            ],
        ];

        echo $this->acympluginHelper->displayOptions($displayOptions, $this->name);

        echo $this->getFilteringZone();

        $this->displayListing();

        $tabHelper->endTab();

        $tabHelper->startTab(acym_translation('ACYM_BY_CATEGORY'));

        $catOptions = [
            [
                'title' => 'ACYM_ORDER_BY',
                'type' => 'select',
                'name' => 'order',
                'options' => [
                    'id' => 'ACYM_ID',
                    'created' => 'ACYM_DATE_CREATED',
                    'modified' => 'ACYM_MODIFICATION_DATE',
                    'title' => 'ACYM_TITLE',
                    'rand' => 'ACYM_RANDOM',
                ],
            ],
            [
                'title' => 'ACYM_MAX_NB_ELEMENTS',
                'type' => 'text',
                'name' => 'max',
                'default' => 20,
            ],
            [
                'title' => 'ACYM_COLUMNS',
                'type' => 'text',
                'name' => 'cols',
                'default' => 1,
            ],
        ];

        $displayOptions = array_merge($displayOptions, $catOptions);

        echo $this->acympluginHelper->displayOptions($displayOptions, 'auto'.$this->name, 'grouped');

        echo $this->getCategoryListing();

        $tabHelper->endTab();

        $tabHelper->display('plugin');
    }

    function displayListing()
    {
        $querySelect = 'SELECT a.*,b.*,c.*,a.id AS gID, a.title AS gtitle ';
        $query = 'FROM `#__content` AS a 
                    JOIN #__categories AS b ON a.catid = b.id 
                    LEFT JOIN `#__users` AS c ON a.created_by = c.id';
        $filters = [];

        $this->pageInfo = new stdClass();
        $this->pageInfo->limit = acym_getCMSConfig('list_limit');
        $this->pageInfo->page = acym_getVar('int', 'pagination_page_ajax', 1);
        $this->pageInfo->start = ($this->pageInfo->page - 1) * $this->pageInfo->limit;
        $this->pageInfo->search = acym_getVar('string', 'plugin_search', '');
        $this->pageInfo->filter_cat = acym_getVar('int', 'plugin_category', 0);
        $this->pageInfo->order = 'a.id';
        $this->pageInfo->orderdir = 'DESC';

        $searchFields = ['a.id', 'a.title', 'b.title', 'c.username'];
        if (!empty($this->pageInfo->search)) {
            $searchVal = '%'.acym_getEscaped($this->pageInfo->search, true).'%';
            $filters[] = implode(" LIKE ".acym_escapeDB($searchVal)." OR ", $searchFields)." LIKE ".acym_escapeDB($searchVal);
        }
        $filters[] = "a.state != -2";
        if (!empty($this->pageInfo->filter_cat)) {
            $filters[] = "a.catid = ".intval($this->pageInfo->filter_cat);
        }
        if (!empty($filters)) {
            $query .= ' WHERE ('.implode(') AND (', $filters).')';
        }
        if (!empty($this->pageInfo->order)) {
            $query .= ' ORDER BY '.acym_secureDBColumn($this->pageInfo->order).' '.acym_secureDBColumn($this->pageInfo->orderdir);
        }

        $rows = acym_loadObjectList($querySelect.$query, '', $this->pageInfo->start, $this->pageInfo->limit);
        $this->pageInfo->total = acym_loadResult('SELECT COUNT(*) '.$query);


        foreach ($rows as $i => $row) {
            if (strpos($row->created, ': ') != false) {
                $rows[$i]->created = str_replace('/', '', strrchr(strip_tags($row->created), '/'));
            }
        }

        $listingOptions = [
            'header' => [
                'gtitle' => [
                    'label' => 'ACYM_TITLE',
                    'size' => '5',
                ],
                'created' => [
                    'label' => 'ACYM_DATE_CREATED',
                    'size' => '3',
                    'type' => 'date',
                ],
                'title' => [
                    'label' => 'ACYM_CATEGORY',
                    'size' => '3',
                ],
                'gID' => [
                    'label' => 'ACYM_ID',
                    'size' => '1',
                    'class' => 'text-center',
                ],
            ],
            'id' => 'gID',
            'rows' => $rows,
        ];

        echo $this->getElementsListing($listingOptions);
    }

    function replaceContent(&$email)
    {
        $this->_replaceAuto($email);
        $this->_replaceOne($email);
    }

    private function _replaceOne(&$email)
    {
        $tags = $this->acympluginHelper->extractTags($email, $this->name);
        if (empty($tags)) return;

        require_once(JPATH_SITE.DS.'plugins'.DS.'content'.DS.'cck'.DS.'cck.php');
        require_once(__DIR__.DS.'acyseblodfield.php');

        $menuid = acym_loadResult('SELECT id FROM #__menu WHERE link LIKE "%index.php?option=com_content&view=article%" LIMIT 1');
        $this->itemId = empty($menuid) ? '' : '&Itemid='.$menuid;


        acym_loadLanguageFile('com_cck_default', JPATH_SITE);

        $this->addedcss = false;

        $tagsReplaced = [];
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
            $tag->displays = ['title'];
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
        $varFields = [];

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

        $answer = [];
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

        $params = [];
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
        $tags = $this->acympluginHelper->extractTags($email, 'auto'.$this->name);
        $return = new stdClass();
        $return->status = true;
        $return->message = '';
        $this->tags = [];

        if (empty($tags)) {
            return $return;
        }

        foreach ($tags as $oneTag => $parameter) {
            if (isset($this->tags[$oneTag])) {
                continue;
            }
            $allcats = explode('-', $parameter->id);
            $selectedArea = [];
            foreach ($allcats as $oneCat) {
                if (empty($oneCat)) {
                    continue;
                }
                $selectedArea[] = intval($oneCat);
            }
            $query = 'SELECT id FROM #__content';
            $where = [];

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

            $this->tags[$oneTag] = $this->finalizeCategoryFormat($this->name, $allArticles, $parameter);
        }

        return $return;
    }
}

