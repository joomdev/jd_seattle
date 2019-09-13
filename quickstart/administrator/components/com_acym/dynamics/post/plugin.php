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

class plgAcymPost extends acymPlugin
{
    function __construct()
    {
        parent::__construct();
        $this->cms = 'WordPress';
        $this->name = 'post';
        $this->rootCategoryId = 0;
    }

    function insertOptions()
    {
        $plugin = new stdClass();
        $plugin->name = acym_translation('ACYM_ARTICLE');
        $plugin->icon = '<i class="cell fa fa-wordpress"></i>';
        $plugin->icontype = 'raw';
        $plugin->plugin = __CLASS__;

        return $plugin;
    }

    function contentPopup()
    {
        $this->categories = acym_loadObjectList(
            "SELECT cat.term_taxonomy_id AS id, cat.parent AS parent_id, catdetails.name AS title 
            FROM `#__term_taxonomy` AS cat 
            JOIN `#__terms` AS catdetails ON cat.term_id = catdetails.term_id
            WHERE cat.taxonomy = 'category'"
        );

        $tabHelper = acym_get('helper.tab');
        $tabHelper->startTab(acym_translation('ACYM_ONE_BY_ONE'));

        $displayOptions = [
            [
                'title' => 'ACYM_DISPLAY',
                'type' => 'checkbox',
                'name' => 'display',
                'options' => [
                    'title' => ['ACYM_TITLE', true],
                    'image' => ['ACYM_FEATURED_IMAGE', true],
                    'content' => ['ACYM_CONTENT', true],
                    'cats' => ['ACYM_CATEGORIES', false],
                ],
            ],
            [
                'title' => 'ACYM_CLICKABLE_TITLE',
                'type' => 'boolean',
                'name' => 'clickable',
                'default' => true,
            ],
            [
                'title' => 'ACYM_TRUNCATE',
                'type' => 'intextfield',
                'name' => 'wrap',
                'text' => 'ACYM_TRUNCATE_AFTER',
                'default' => 0,
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
                    'ID' => 'ACYM_ID',
                    'post_date' => 'ACYM_PUBLISHING_DATE',
                    'post_modified' => 'ACYM_MODIFICATION_DATE',
                    'post_title' => 'ACYM_TITLE',
                    'rand' => 'ACYM_RANDOM',
                ],
            ],
            [
                'title' => 'ACYM_COLUMNS',
                'type' => 'text',
                'name' => 'cols',
                'default' => 1,
            ],
            [
                'title' => 'ACYM_MAX_NB_ELEMENTS',
                'type' => 'text',
                'name' => 'max',
                'default' => 20,
            ],
        ];

        $displayOptions = array_merge($displayOptions, $catOptions);

        echo $this->acympluginHelper->displayOptions($displayOptions, 'auto'.$this->name, 'grouped');

        acym_display(acym_translation('ACYM_SPECIAL_CONTENT_WARNING'), 'warning', false);

        echo $this->getCategoryListing();

        $tabHelper->endTab();

        $tabHelper->display('plugin');
    }

    function displayListing()
    {
        $querySelect = 'SELECT post.ID, post.post_title, post.post_date, post.post_content ';
        $query = 'FROM #__posts AS post ';
        $filters = [];

        $this->pageInfo = new stdClass();
        $this->pageInfo->limit = acym_getCMSConfig('list_limit');
        $this->pageInfo->page = acym_getVar('int', 'pagination_page_ajax', 1);
        $this->pageInfo->start = ($this->pageInfo->page - 1) * $this->pageInfo->limit;
        $this->pageInfo->search = acym_getVar('string', 'plugin_search', '');
        $this->pageInfo->filter_cat = acym_getVar('int', 'plugin_category', 0);
        $this->pageInfo->order = 'post.ID';
        $this->pageInfo->orderdir = 'DESC';

        $searchFields = ['post.ID', 'post.post_title'];
        if (!empty($this->pageInfo->search)) {
            $searchVal = '%'.acym_getEscaped($this->pageInfo->search, true).'%';
            $filters[] = implode(" LIKE ".acym_escapeDB($searchVal)." OR ", $searchFields)." LIKE ".acym_escapeDB($searchVal);
        }

        if (!empty($this->pageInfo->filter_cat)) {
            $query .= 'JOIN #__term_relationships AS cat ON post.ID = cat.object_id';
            $filters[] = "cat.term_taxonomy_id = ".intval($this->pageInfo->filter_cat);
        }

        $filters[] = 'post.post_type = "post"';
        $filters[] = 'post.post_status = "publish"';

        $query .= ' WHERE ('.implode(') AND (', $filters).')';
        if (!empty($this->pageInfo->order)) $query .= ' ORDER BY '.acym_secureDBColumn($this->pageInfo->order).' '.acym_secureDBColumn($this->pageInfo->orderdir);

        $rows = acym_loadObjectList($querySelect.$query, '', $this->pageInfo->start, $this->pageInfo->limit);
        $this->pageInfo->total = acym_loadResult('SELECT COUNT(*) '.$query);


        foreach ($rows as $i => $row) {
            if (str_replace(['wp:core-embed', 'wp:shortcode'], '', $row->post_content) !== $row->post_content) {
                $rows[$i]->post_title = acym_tooltip('<i class="fa fa-warning"></i>', acym_translation('ACYM_SPECIAL_CONTENT_WARNING')).$rows[$i]->post_title;
            }
        }

        $listingOptions = [
            'header' => [
                'post_title' => [
                    'label' => 'ACYM_TITLE',
                    'size' => '7',
                ],
                'post_date' => [
                    'label' => 'ACYM_PUBLISHING_DATE',
                    'size' => '4',
                    'type' => 'date',
                ],
                'ID' => [
                    'label' => 'ACYM_ID',
                    'size' => '1',
                    'class' => 'text-center',
                ],
            ],
            'id' => 'ID',
            'rows' => $rows,
        ];

        echo $this->getElementsListing($listingOptions);
    }

    function replaceContent(&$email)
    {
        $this->_replaceAuto($email);
        $this->_replaceOne($email);
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

            $query = 'SELECT DISTINCT post.`ID` 
                    FROM #__posts AS post 
                    LEFT JOIN #__term_relationships AS cat ON post.ID = cat.object_id';

            $where = [];

            if (!empty($selectedArea)) {
                $where[] = 'cat.term_taxonomy_id IN ('.implode(',', $selectedArea).')';
            }

            $where[] = 'post.post_type = "post"';
            $where[] = 'post.post_status = "publish"';

            $query .= ' WHERE ('.implode(') AND (', $where).')';

            if (!empty($parameter->order)) {
                $ordering = explode(',', $parameter->order);
                if ($ordering[0] == 'rand') {
                    $query .= ' ORDER BY rand()';
                } else {
                    $query .= ' ORDER BY post.`'.acym_secureDBColumn(trim($ordering[0])).'` '.acym_secureDBColumn(trim($ordering[1]));
                }
            }

            if (empty($parameter->max)) $parameter->max = 20;
            $query .= ' LIMIT '.intval($parameter->max);

            $allArticles = acym_loadResultArray($query);

            $this->tags[$oneTag] = $this->finalizeCategoryFormat($this->name, $allArticles, $parameter);
        }

        return $return;
    }

    private function _replaceOne(&$email)
    {
        $tags = $this->acympluginHelper->extractTags($email, $this->name);
        if (empty($tags)) return;

        $tagsReplaced = [];
        foreach ($tags as $i => $oneTag) {
            if (isset($tagsReplaced[$i])) {
                continue;
            }
            $tagsReplaced[$i] = $this->_replaceContent($oneTag, $email);
        }

        $this->acympluginHelper->replaceTags($email, $tagsReplaced, true);
    }

    function _replaceContent($tag, &$email)
    {
        $query = 'SELECT post.*
                    FROM #__posts AS post
                    WHERE post.post_type = "post" 
                        AND post.post_status = "publish"
                        AND post.ID = '.intval($tag->id);

        $element = acym_loadObject($query);

        if (empty($element)) {
            if (acym_isAdmin()) {
                acym_enqueueMessage('The post "'.$tag->id.'" could not be found', 'notice');
            }

            return '';
        }

        if (empty($tag->display)) {
            $tag->display = [];
        } else {
            $tag->display = explode(',', $tag->display);
        }

        $varFields = [];
        foreach ($element as $fieldName => $oneField) {
            $varFields['{'.$fieldName.'}'] = $oneField;
        }

        $link = $element->guid;
        $varFields['{link}'] = $link;

        $title = '';
        if (in_array('title', $tag->display)) $title = $element->post_title;

        $afterTitle = '';

        $imagePath = '';
        if (in_array('image', $tag->display)) {
            $imageId = get_post_thumbnail_id($tag->id);
            if (!empty($imageId)) {
                $imagePath = get_the_post_thumbnail_url($tag->id);
            }
        }

        $contentText = '';
        if (in_array('content', $tag->display)) $contentText .= $element->post_content;

        $customFields = [];
        if (in_array('cats', $tag->display)) {
            $customFields[] = [
                get_the_term_list($tag->id, 'category', '', ', '),
                acym_translation('ACYM_CATEGORIES'),
            ];
        }

        $format = new stdClass();
        $format->tag = $tag;
        $format->title = $title;
        $format->afterTitle = $afterTitle;
        $format->afterArticle = '';
        $format->imagePath = $imagePath;
        $format->description = $contentText;
        $format->link = empty($tag->clickable) ? '' : $link;
        $format->cols = empty($tag->nbcols) ? 1 : intval($tag->nbcols);
        $format->customFields = $customFields;
        $result = '<div class="acymailing_content">'.$this->acympluginHelper->getStandardDisplay($format).'</div>';

        return $this->finalizeElementFormat($this->name, $result, $tag, $varFields);
    }
}

