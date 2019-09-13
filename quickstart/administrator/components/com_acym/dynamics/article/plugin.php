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

class plgAcymArticle extends acymPlugin
{
    public function __construct()
    {
        parent::__construct();
        $this->cms = 'Joomla';
        $this->name = 'article';
    }

    public function insertOptions()
    {
        $plugin = new stdClass();
        $plugin->name = acym_translation('ACYM_ARTICLE');
        $plugin->icon = '<i class="cell fa fa-joomla"></i>';
        $plugin->icontype = 'raw';
        $plugin->plugin = __CLASS__;

        return $plugin;
    }

    public function contentPopup()
    {
        $this->categories = acym_loadObjectList(
            'SELECT id, parent_id, title
            FROM `#__categories` 
            WHERE extension = "com_content"'
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
                    'content' => ['ACYM_CONTENT', true],
                    'cat' => ['ACYM_CATEGORY', false],
                    'readmore' => ['ACYM_READ_MORE', false],
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
                    'id' => 'ACYM_ID',
                    'publish_up' => 'ACYM_PUBLISHING_DATE',
                    'modified' => 'ACYM_MODIFICATION_DATE',
                    'title' => 'ACYM_TITLE',
                    'rand' => 'ACYM_RANDOM',
                ],
            ],
            [
                'title' => 'ACYM_COLUMNS',
                'type' => 'number',
                'name' => 'cols',
                'default' => 1,
                'min' => 1,
                'max' => 10,
            ],
            [
                'title' => 'ACYM_MAX_NB_ELEMENTS',
                'type' => 'number',
                'name' => 'max',
                'default' => 20,
            ],
        ];

        $displayOptions = array_merge($displayOptions, $catOptions);

        echo $this->acympluginHelper->displayOptions($displayOptions, 'auto'.$this->name, 'grouped');

        echo $this->getCategoryListing();

        $tabHelper->endTab();

        $tabHelper->display('plugin');
    }

    public function displayListing()
    {
        $querySelect = 'SELECT article.id, article.title, article.publish_up ';
        $query = 'FROM #__content AS article ';
        $filters = [];

        $this->pageInfo = new stdClass();
        $this->pageInfo->limit = acym_getCMSConfig('list_limit');
        $this->pageInfo->page = acym_getVar('int', 'pagination_page_ajax', 1);
        $this->pageInfo->start = ($this->pageInfo->page - 1) * $this->pageInfo->limit;
        $this->pageInfo->search = acym_getVar('string', 'plugin_search', '');
        $this->pageInfo->filter_cat = acym_getVar('int', 'plugin_category', 0);
        $this->pageInfo->order = 'article.id';
        $this->pageInfo->orderdir = 'DESC';

        $searchFields = ['article.id', 'article.title'];
        if (!empty($this->pageInfo->search)) {
            $searchVal = '%'.acym_getEscaped($this->pageInfo->search, true).'%';
            $filters[] = implode(' LIKE '.acym_escapeDB($searchVal).' OR ', $searchFields).' LIKE '.acym_escapeDB($searchVal);
        }

        if (!empty($this->pageInfo->filter_cat)) {
            $filters[] = "article.catid = ".intval($this->pageInfo->filter_cat);
        }

        $filters[] = 'article.state = 1';

        $query .= ' WHERE ('.implode(') AND (', $filters).')';
        if (!empty($this->pageInfo->order)) $query .= ' ORDER BY '.acym_secureDBColumn($this->pageInfo->order).' '.acym_secureDBColumn($this->pageInfo->orderdir);

        $rows = acym_loadObjectList($querySelect.$query, '', $this->pageInfo->start, $this->pageInfo->limit);
        $this->pageInfo->total = acym_loadResult('SELECT COUNT(*) '.$query);


        $listingOptions = [
            'header' => [
                'title' => [
                    'label' => 'ACYM_TITLE',
                    'size' => '7',
                ],
                'publish_up' => [
                    'label' => 'ACYM_PUBLISHING_DATE',
                    'size' => '4',
                    'type' => 'date',
                ],
                'id' => [
                    'label' => 'ACYM_ID',
                    'size' => '1',
                    'class' => 'text-center',
                ],
            ],
            'id' => 'id',
            'rows' => $rows,
        ];

        echo $this->getElementsListing($listingOptions);
    }

    public function replaceContent(&$email)
    {
        $this->replaceAuto($email);
        $this->replaceOne($email);
    }

    private function replaceAuto(&$email)
    {
        $this->generateByCategory($email);
        if (empty($this->tags)) return;
        $this->acympluginHelper->replaceTags($email, $this->tags, true);
    }

    private function generateByCategory(&$email)
    {
        $tags = $this->acympluginHelper->extractTags($email, 'auto'.$this->name);
        $return = new stdClass();
        $return->status = true;
        $return->message = '';
        $this->tags = [];
        $time = time();

        if (empty($tags)) return $return;

        foreach ($tags as $oneTag => $parameter) {
            if (isset($this->tags[$oneTag])) continue;

            $allcats = explode('-', $parameter->id);
            $selectedArea = [];
            foreach ($allcats as $oneCat) {
                if (empty($oneCat)) continue;

                $selectedArea[] = intval($oneCat);
            }

            $query = 'SELECT DISTINCT article.`id` FROM #__content AS article ';

            $where = [];

            if (!empty($selectedArea)) {
                $where[] = 'article.catid IN ('.implode(',', $selectedArea).')';
            }

            $where[] = 'article.state = 1';
            $where[] = '`publish_up` < '.acym_escapeDB(date('Y-m-d H:i:s', $time - date('Z')));
            $where[] = '`publish_down` > '.acym_escapeDB(date('Y-m-d H:i:s', $time - date('Z'))).' OR `publish_down` = 0';

            $query .= ' WHERE ('.implode(') AND (', $where).')';

            if (!empty($parameter->order)) {
                $ordering = explode(',', $parameter->order);
                if ($ordering[0] == 'rand') {
                    $query .= ' ORDER BY rand()';
                } else {
                    $query .= ' ORDER BY article.`'.acym_secureDBColumn(trim($ordering[0])).'` '.acym_secureDBColumn(trim($ordering[1]));
                }
            }

            if (empty($parameter->max)) $parameter->max = 20;
            $query .= ' LIMIT '.intval($parameter->max);

            $allArticles = acym_loadResultArray($query);

            $this->tags[$oneTag] = $this->finalizeCategoryFormat($this->name, $allArticles, $parameter);
        }

        return $return;
    }

    private function replaceOne(&$email)
    {
        $tags = $this->acympluginHelper->extractTags($email, $this->name);
        if (empty($tags)) return;

        require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php';

        $tagsReplaced = [];
        foreach ($tags as $i => $oneTag) {
            if (isset($tagsReplaced[$i])) continue;

            $tagsReplaced[$i] = $this->replaceIndividualContent($oneTag);
        }

        $this->acympluginHelper->replaceTags($email, $tagsReplaced, true);
    }

    private function replaceIndividualContent($tag)
    {
        $query = 'SELECT article.*
                    FROM #__content AS article
                    WHERE article.state = 1
                        AND article.id = '.intval($tag->id);

        $element = acym_loadObject($query);

        if (empty($element)) {
            if (acym_isAdmin()) {
                acym_enqueueMessage('The article "'.$tag->id.'" could not be found', 'notice');
            }

            return '';
        }

        if (empty($tag->display)) {
            $tag->display = [];
        } else {
            $tag->display = explode(',', $tag->display);
        }

        $varFields = [];
        $varFields['{picthtml}'] = '';
        foreach ($element as $fieldName => $oneField) {
            $varFields['{'.$fieldName.'}'] = $oneField;
        }

        $completeId = $element->id;
        if (!empty($element->alias)) $completeId .= ':'.$element->alias;

        $link = ContentHelperRoute::getArticleRoute($completeId, $element->catid);
        $link = acym_frontendLink($link, false);
        $varFields['{link}'] = $link;

        $title = $element->title;

        $afterTitle = '';
        $afterArticle = '';

        $imagePath = '';
        if (!empty($tag->pict) && !empty($element->images)) {
            $images = json_decode($element->images);
            $pictVar = empty($images->image_fulltext) ? 'image_intro' : 'image_fulltext';
            if (!empty($images->$pictVar)) {
                $imagePath = acym_rootURI().$images->$pictVar;
                $varFields['{picthtml}'] = '<img alt="" src="'.acym_escape($imagePath).'" />';
            }
        }

        $contentText = '';
        if (in_array('content', $tag->display)) $contentText .= $element->introtext.$element->fulltext;

        $customFields = [];
        if (in_array('cat', $tag->display)) {
            $category = acym_loadResult('SELECT title FROM #__categories WHERE id = '.intval($element->catid));
            $customFields[] = [
                '<a href="index.php?option=com_content&view=category&id='.$element->catid.'" target="_blank">'.acym_escape($category).'</a>',
                acym_translation('ACYM_CATEGORY'),
            ];
        }

        $readMoreText = empty($tag->readmore) ? acym_translation('ACYM_READ_MORE') : $tag->readmore;
        $varFields['{readmore}'] = '<a class="acymailing_readmore_link" style="text-decoration:none;" target="_blank" href="'.$link.'"><span class="acymailing_readmore">'.acym_escape($readMoreText).'</span></a>';
        if (in_array('readmore', $tag->display)) $afterArticle .= $varFields['{readmore}'];

        $format = new stdClass();
        $format->tag = $tag;
        $format->title = $title;
        $format->afterTitle = $afterTitle;
        $format->afterArticle = $afterArticle;
        $format->imagePath = $imagePath;
        $format->description = $contentText;
        $format->link = empty($tag->clickable) ? '' : $link;
        $format->cols = empty($tag->nbcols) ? 1 : intval($tag->nbcols);
        $format->customFields = $customFields;
        $result = '<div class="acymailing_content">'.$this->acympluginHelper->getStandardDisplay($format).'</div>';

        return $this->finalizeElementFormat($this->name, $result, $tag, $varFields);
    }
}
