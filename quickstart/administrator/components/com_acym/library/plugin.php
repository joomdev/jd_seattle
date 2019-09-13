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

class acymPlugin
{
    var $acympluginHelper;
    var $cms = 'all';
    var $name = '';

    var $installed = true;
    var $pluginsPath = '';

    var $rootCategoryId = 1;
    var $categories;
    var $catvalues = [];
    var $cats = [];

    var $tags = [];
    var $pageInfo;

    public function __construct()
    {
        $this->acympluginHelper = acym_get('helper.plugin');
        $this->pluginsPath = acym_getPluginsPath(__FILE__, __DIR__);
    }

    protected function getFilteringZone($categoryFilter = true)
    {
        $result = '<div class="grid-x grid-margin-x" id="plugin_listing_filters">
                    <div class="cell medium-6">
                        <input type="text" name="plugin_search" placeholder="'.acym_escape(acym_translation('ACYM_SEARCH')).'"/>
                    </div>
                    <div class="cell medium-6 grid-x">
                        <div class="cell hide-for-small-only medium-auto"></div>
                        <div class="cell medium-shrink">';

        if ($categoryFilter) $result .= $this->getCategoryFilter();

        $result .= '</div>
                    </div>
                </div>';

        return $result;
    }

    protected function getCategoryFilter()
    {
        $filter_cat = acym_getVar('int', 'plugin_category', 0);

        $this->cats = [];
        if (!empty($this->categories)) {
            foreach ($this->categories as $oneCat) {
                $this->cats[$oneCat->parent_id][] = $oneCat;
            }
        }
        $this->catvalues = [];
        $this->catvalues[] = acym_selectOption(0, 'ACYM_ALL');
        $this->handleChildrenCategories($this->rootCategoryId);

        return acym_select($this->catvalues, 'plugin_category', intval($filter_cat), 'class="plugin_category_select"', 'value', 'text');
    }

    protected function handleChildrenCategories($parent_id, $level = 0)
    {
        if (empty($this->cats[$parent_id])) return;

        foreach ($this->cats[$parent_id] as $cat) {
            $this->catvalues[] = acym_selectOption($cat->id, str_repeat(' - - ', $level).$cat->title);
            $this->handleChildrenCategories($cat->id, $level + 1);
        }
    }

    protected function getElementsListing($options)
    {
        $listing = '<div id="plugin_listing" class="acym__popup__listing padding-0">';
        $listing .= '<input type="hidden" name="plugin" value="'.acym_escape(get_class($this)).'" />';

        $listing .= '<div class="cell grid-x hide-for-small-only plugin_listing_headers">';
        foreach ($options['header'] as $oneColumn) {
            $class = empty($oneColumn['class']) ? '' : ' '.$oneColumn['class'];
            $listing .= '<div class="cell medium-'.$oneColumn['size'].$class.'">'.acym_translation($oneColumn['label']).'</div>';
        }
        $listing .= '</div>';

        if (empty($options['rows'])) {
            $listing .= '<h1 class="cell acym__listing__empty__search__modal text-center">'.acym_translation('ACYM_NO_RESULTS_FOUND').'</h1>';
        } else {
            $selected = explode(',', acym_getVar('string', 'selected', ''));
            foreach ($options['rows'] as $row) {
                $class = 'cell grid-x acym__listing__row acym__listing__row__popup';
                if (in_array($row->{$options['id']}, $selected)) $class .= ' selected_row';

                $listing .= '<div class="'.$class.'" data-id="'.intval($row->{$options['id']}).'" onclick="applyContent'.acym_escape($this->name).'('.intval($row->{$options['id']}).', this);">';

                foreach ($options['header'] as $column => $oneColumn) {
                    $value = $row->$column;

                    if (!empty($oneColumn['type']) && $oneColumn['type'] == 'date') {
                        if (!is_numeric($value)) $value = strtotime($value);
                        $value = acym_date($value, 'd F Y H:i');
                    }

                    $class = empty($oneColumn['class']) ? '' : ' '.$oneColumn['class'];
                    $listing .= '<div class="cell medium-'.$oneColumn['size'].$class.'">'.$value.'</div>';
                }

                $listing .= '</div>';
            }
        }

        $pagination = acym_get('helper.pagination');
        $pagination->setStatus($this->pageInfo->total, $this->pageInfo->page, $this->pageInfo->limit);
        $listing .= $pagination->displayAjax();
        $listing .= '</div>';

        return $listing;
    }

    protected function getCategoryListing()
    {
        $listing = '';
        if (empty($this->catvalues)) return $listing;

        $listing .= '<div class="acym__popup__listing padding-0">';
        foreach ($this->catvalues as $oneCat) {
            if (empty($oneCat->value)) continue;

            $listing .= '<div class="cell grid-x acym__listing__row acym__listing__row__popup" data-id="'.intval($oneCat->value).'" onclick="applyContentauto'.acym_escape($this->name).'('.intval($oneCat->value).', this);">
                        <div class="cell medium-5">'.acym_escape($oneCat->text).'</div>
                    </div>';
        }
        $listing .= '</div>';

        return $listing;
    }

    protected function finalizeCategoryFormat($name, $elements, $parameter)
    {
        if (empty($elements)) return '';

        $customLayout = ACYM_MEDIA.'plugins'.DS.$name.'_auto.php';
        if (file_exists($customLayout)) {
            ob_start();
            require $customLayout;

            return ob_get_clean();
        }

        $arrayElements = [];
        unset($parameter->id);
        foreach ($elements as $oneElementId) {
            $args = [];
            $args[] = $name.':'.$oneElementId;
            foreach ($parameter as $oneParam => $val) {
                if (is_bool($val)) {
                    $args[] = $oneParam;
                } else {
                    $args[] = $oneParam.':'.$val;
                }
            }
            $arrayElements[] = '{'.implode('|', $args).'}';
        }

        return $this->acympluginHelper->getFormattedResult($arrayElements, $parameter);
    }

    protected function finalizeElementFormat($name, $result, $options, $data)
    {
        if (file_exists(ACYM_MEDIA.'plugins'.DS.$name.'.php')) {
            ob_start();
            require(ACYM_MEDIA.'plugins'.DS.$name.'.php');
            $result = ob_get_clean();
            $result = str_replace(array_keys($data), $data, $result);
        }

        return $this->acympluginHelper->managePicts($options, $result);
    }
}

