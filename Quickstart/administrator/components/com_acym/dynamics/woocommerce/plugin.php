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

class plgAcymWoocommerce extends acymPlugin
{
    function __construct()
    {
        parent::__construct();
        $this->cms = 'WordPress';
        $this->name = 'woocommerce';
        if (!file_exists($this->pluginsPath.'woocommerce')) {
            $this->installed = false;
        }
    }

    function insertOptions()
    {
        $plugins = new stdClass();
        $plugins->name = 'WooCommerce';
        $plugins->icon = ACYM_DYNAMICS_URL.basename(__DIR__).'/icon.png';
        $plugins->plugin = __CLASS__;

        return $plugins;
    }

    function _categories($filter_cat)
    {
        $cats = acym_loadObjectList(
            "SELECT cat.term_taxonomy_id AS id, cat.parent AS parent_id, catdetails.name AS title 
            FROM `#__term_taxonomy` AS cat 
            JOIN `#__terms` AS catdetails ON cat.term_id = catdetails.term_id
            WHERE cat.taxonomy = 'product_cat'"
        );

        $this->categories = array();
        $this->cats = array();
        if (!empty($cats)) {
            foreach ($cats as $oneCat) {
                $this->cats[$oneCat->parent_id][] = $oneCat;
                $this->categories[$oneCat->id] = $oneCat->title;
            }
        }
        $this->catvalues = array();
        $this->catvalues[] = acym_selectOption(0, acym_translation('ACYM_ALL'));
        $this->_handleChildren();

        return acym_select($this->catvalues, 'plugin_category', (int)$filter_cat, 'style="width: 150px;"', 'value', 'text');
    }

    function _handleChildren($parent_id = 0, $level = 0)
    {
        if (empty($this->cats[$parent_id])) {
            return;
        }
        foreach ($this->cats[$parent_id] as $cat) {
            $this->catvalues[] = acym_selectOption($cat->id, str_repeat(" - - ", $level).$cat->title);
            $this->_handleChildren($cat->id, $level + 1);
        }
    }

    function contentPopup()
    {
        $tabHelper = acym_get('helper.tab');
        $tabHelper->startTab(acym_translation('ACYM_ONE_BY_ONE'));

        $displayOptions = array(
            array(
                'title' => 'ACYM_DISPLAY',
                'type' => 'checkbox',
                'name' => 'display',
                'options' => array(
                    'title' => array('ACYM_TITLE', true),
                    'price' => array('ACYM_PRICE', true),
                    'desc' => array('ACYM_DESCRIPTION', true),
                    'shortdesc' => array('ACYM_SHORT_DESCRIPTION', false),
                    'cats' => array('ACYM_CATEGORIES', false),
                    'attribs' => array('ACYM_DETAILS', false),
                ),
            ),
            array(
                'title' => 'ACYM_CLICKABLE_TITLE',
                'type' => 'boolean',
                'name' => 'clickable',
                'default' => true,
            ),
            array(
                'title' => 'ACYM_TRUNCATE',
                'type' => 'intextfield',
                'name' => 'wrap',
                'text' => 'ACYM_TRUNCATE_AFTER',
                'default' => 0,
            ),
            array(
                'title' => 'ACYM_DISPLAY_PICTURES',
                'type' => 'pictures',
                'name' => 'pictures',
            ),
        );

        echo $this->acympluginHelper->displayOptions($displayOptions, $this->name);


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
                    'ID' => 'ACYM_ID',
                    'post_date' => 'ACYM_PUBLISHING_DATE',
                    'post_modified' => 'ACYM_MODIFICATION_DATE',
                    'post_title' => 'ACYM_TITLE',
                    'rand' => 'ACYM_RANDOM',
                ),
            ),
            array(
                'title' => 'ACYM_COLUMNS',
                'type' => 'text',
                'name' => 'cols',
                'default' => 1,
            ),
            array(
                'title' => 'ACYM_MAX_NB_ELEMENTS',
                'type' => 'text',
                'name' => 'max',
                'default' => 20,
            ),
        );

        $displayOptions = array_merge($displayOptions, $catOptions);

        echo $this->acympluginHelper->displayOptions($displayOptions, $this->name.'_auto', 'grouped');

        if (!empty($this->catvalues)) {
            echo '<div class="acym__popup__listing padding-0">';
            foreach ($this->catvalues as $oneCat) {
                if (empty($oneCat->value)) {
                    continue;
                }
                echo '<div class="cell grid-x acym__listing__row acym__listing__row__popup" data-id="'.$oneCat->value.'" onclick="applyContent'.$this->name.'_auto('.$oneCat->value.', this);">
                        <div class="cell medium-5">'.$oneCat->text.'</div>
                    </div>';
            }
            echo '</div>';
        }

        $tabHelper->endTab();
        $tabHelper->startTab(acym_translation('ACYM_COUPON'));

        $displayOptions = array(
            array(
                'title' => 'ACYM_DISCOUNT_CODE',
                'type' => 'text',
                'name' => 'code',
                'default' => '[name][key][value]',
                'class' => 'acym_plugin__larger_text_field',
            ),
            array(
                'title' => __('Coupon expiry date', 'woocommerce'),
                'type' => 'date',
                'name' => 'end',
                'default' => '',
            ),
            array(
                'title' => __('Discount type', 'woocommerce'),
                'type' => 'select',
                'name' => 'type',
                'options' => array(
                    'fixed_cart' => __('Fixed cart discount', 'woocommerce'),
                    'fixed_product' => __('Fixed product discount', 'woocommerce'),
                    'percent' => __('Percentage discount', 'woocommerce'),
                ),
            ),
            array(
                'title' => __('Coupon amount', 'woocommerce'),
                'type' => 'number',
                'name' => 'amount',
                'default' => '0',
            ),
            array(
                'title' => __('Allow free shipping', 'woocommerce'),
                'type' => 'boolean',
                'name' => 'free',
                'default' => false,
            ),
            array(
                'title' => __('Exclude sale items', 'woocommerce'),
                'type' => 'boolean',
                'name' => 'exclsale',
                'default' => false,
            ),
            array(
                'title' => __('Minimum spend', 'woocommerce'),
                'type' => 'number',
                'name' => 'min',
                'default' => '',
            ),
            array(
                'title' => __('Maximum spend', 'woocommerce'),
                'type' => 'number',
                'name' => 'max',
                'default' => '',
            ),
            array(
                'title' => __('Usage limit per coupon', 'woocommerce'),
                'type' => 'number',
                'name' => 'use',
                'default' => '1',
            ),
            array(
                'title' => __('Limit usage to X items', 'woocommerce'),
                'type' => 'number',
                'name' => 'items',
                'default' => '',
            ),
            array(
                'title' => __('Products', 'woocommerce'),
                'type' => 'text',
                'name' => 'prod',
                'class' => 'acym_plugin__larger_text_field',
                'default' => '',
            ),
            array(
                'title' => __('Exclude products', 'woocommerce'),
                'type' => 'text',
                'name' => 'exclprod',
                'class' => 'acym_plugin__larger_text_field',
                'default' => '',
            ),
            array(
                'title' => __('Product categories', 'woocommerce'),
                'type' => 'multiselect',
                'name' => 'cat',
                'options' => $this->categories,
            ),
            array(
                'title' => __('Exclude categories', 'woocommerce'),
                'type' => 'multiselect',
                'name' => 'exclcat',
                'options' => $this->categories,
            ),
        );

        echo $this->acympluginHelper->displayOptions($displayOptions, $this->name.'_coupon', 'simple', '');

        $tabHelper->endTab();

        $tabHelper->display('plugin');
    }

    function displayListing()
    {
        echo '<input type="hidden" name="plugin" value="'.__CLASS__.'" />';
        $query = 'SELECT SQL_CALC_FOUND_ROWS product.ID, product.post_title, product.post_date FROM #__posts AS product ';
        $filters = array();

        $pageInfo = new stdClass();
        $pageInfo->limit = acym_getCMSConfig('list_limit');
        $pageInfo->page = acym_getVar('int', 'pagination_page_ajax', 1);
        $pageInfo->start = ($pageInfo->page - 1) * $pageInfo->limit;
        $pageInfo->search = acym_getVar('string', 'plugin_search', '');
        $pageInfo->filter_cat = acym_getVar('int', 'plugin_category', 0);
        $pageInfo->order = 'product.ID';
        $pageInfo->orderdir = 'DESC';

        $searchFields = array('product.ID', 'product.post_title');
        if (!empty($pageInfo->search)) {
            $searchVal = '%'.acym_getEscaped($pageInfo->search, true).'%';
            $filters[] = implode(" LIKE ".acym_escapeDB($searchVal)." OR ", $searchFields)." LIKE ".acym_escapeDB($searchVal);
        }

        if (!empty($pageInfo->filter_cat)) {
            $query .= 'JOIN #__term_relationships AS cat ON product.ID = cat.object_id';
            $filters[] = "cat.term_taxonomy_id = ".intval($pageInfo->filter_cat);
        }

        $filters[] = 'product.post_type = "product"';
        $filters[] = 'product.post_status = "publish"';

        $query .= ' WHERE ('.implode(') AND (', $filters).')';
        if (!empty($pageInfo->order)) $query .= ' ORDER BY '.$pageInfo->order.' '.$pageInfo->orderdir;

        $rows = acym_loadObjectList($query, '', $pageInfo->start, $pageInfo->limit);
        $pageInfo->total = acym_loadResult('SELECT FOUND_ROWS()');

        $selected = explode(',', acym_getVar('string', 'selected', ''));

        echo '<div class="cell grid-x hide-for-small-only plugin_listing_headers">
                <div class="cell medium-5">'.acym_translation('ACYM_TITLE').'</div>
                <div class="cell medium-3">'.acym_translation('ACYM_DATE_CREATED').'</div>
                <div class="cell medium-1">'.acym_translation('ACYM_ID').'</div>
            </div>';
        foreach ($rows as $row) {
            $class = 'cell grid-x acym__listing__row acym__listing__row__popup';
            if (in_array($row->ID, $selected)) {
                $class .= ' selected_row';
            }
            echo '<div class="'.$class.'" data-id="'.$row->ID.'" onclick="applyContent'.$this->name.'('.$row->ID.', this);">
                    <div class="cell medium-5">'.$row->post_title.'</div>
                    <div class="cell medium-3">'.acym_getDate(strtotime($row->post_date)).'</div>
                    <div class="cell medium-1">'.$row->ID.'</div>
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
        $tags = $this->acympluginHelper->extractTags($email, $this->name.'_auto');
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

            $query = 'SELECT DISTINCT product.`ID` 
                    FROM #__posts AS product 
                    LEFT JOIN #__term_relationships AS cat ON product.ID = cat.object_id';

            $where = array();

            if (!empty($selectedArea)) {
                $where[] = 'cat.term_taxonomy_id IN ('.implode(',', $selectedArea).')';
            }

            $where[] = 'product.post_type = "product"';
            $where[] = 'product.post_status = "publish"';

            $query .= ' WHERE ('.implode(') AND (', $where).')';

            if (!empty($parameter->order)) {
                $ordering = explode(',', $parameter->order);
                if ($ordering[0] == 'rand') {
                    $query .= ' ORDER BY rand()';
                } else {
                    $query .= ' ORDER BY product.`'.acym_secureDBColumn(trim($ordering[0])).'` '.acym_secureDBColumn(trim($ordering[1]));
                }
            }

            if (empty($parameter->max)) $parameter->max = 20;
            $query .= ' LIMIT '.intval($parameter->max);

            $allArticles = acym_loadResultArray($query);

            $stringTag = '';
            if (!empty($allArticles)) {
                if (file_exists(ACYM_TEMPLATE.'plugins'.DS.$this->name.'_auto.php')) {
                    ob_start();
                    require(ACYM_TEMPLATE.'plugins'.DS.$this->name.'_auto.php');
                    $stringTag = ob_get_clean();
                } else {
                    $arrayElements = array();
                    unset($parameter->id);
                    foreach ($allArticles as $oneArticleId) {
                        $args = array();
                        $args[] = $this->name.':'.$oneArticleId;
                        foreach ($parameter as $oneParam => $val) {
                            if (is_bool($val)) {
                                $args[] = $oneParam;
                            } else {
                                $args[] = $oneParam.':'.$val;
                            }
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

    private function _replaceOne(&$email)
    {
        $tags = $this->acympluginHelper->extractTags($email, $this->name);
        if (empty($tags)) return;

        $tagsReplaced = array();
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
        $query = 'SELECT product.*
                    FROM #__posts AS product
                    WHERE product.post_type = "product" 
                        AND product.post_status = "publish"
                        AND product.ID = '.intval($tag->id);

        $element = acym_loadObject($query);
        $product = wc_get_product($tag->id);

        if (empty($element) || empty($product)) {
            if (acym_isAdmin()) {
                acym_enqueueMessage('The product "'.$tag->id.'" could not be found', 'notice');
            }

            return '';
        }

        if (empty($tag->display)) {
            $tag->display = array();
        } else {
            $tag->display = explode(',', $tag->display);
        }

        $varFields = array();
        foreach ($element as $fieldName => $oneField) {
            $varFields['{'.$fieldName.'}'] = $oneField;
        }

        $link = $element->guid;
        $varFields['{link}'] = $link;

        $title = $element->post_title;

        $afterTitle = '';
        if (in_array('price', $tag->display)) {
            $afterTitle .= $product->get_price_html();
        }

        $imagePath = '';
        if (!empty($tag->pict)) {
            $imageHTML = $product->get_image('full');
            if (!empty($imageHTML)) {
                $posURL = strpos($imageHTML, ' src="') + 6;
                $imagePath = substr($imageHTML, $posURL, strpos($imageHTML, '"', $posURL) - $posURL);
            }
        }

        $contentText = '';
        if (in_array('desc', $tag->display)) $contentText .= $element->post_content;
        if (in_array('shortdesc', $tag->display)) $contentText .= $element->post_excerpt;

        $customFields = array();
        if (in_array('cats', $tag->display)) {
            $customFields[] = array(
                0 => acym_translation('ACYM_CATEGORIES'),
                1 => get_the_term_list($tag->id, 'product_cat', '', ', '),
            );
        }

        if (in_array('attribs', $tag->display)) {
            $attributes = acym_loadResult('SELECT meta_value FROM #__postmeta WHERE meta_key = "_product_attributes" AND post_id = '.intval($tag->id));
            if (is_string($attributes)) {
                $attributes = unserialize($attributes);
                if (!empty($attributes)) {
                    foreach ($attributes as $oneAttribute) {
                        if ($oneAttribute['is_visible'] != 1) continue;

                        $customFields[] = array(
                            0 => $oneAttribute['name'],
                            1 => str_replace('|', ', ', $oneAttribute['value']),
                        );
                    }
                }
            }
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

        if (file_exists(ACYM_MEDIA.'plugins'.DS.$this->name.'.php')) {
            ob_start();
            require(ACYM_MEDIA.'plugins'.DS.$this->name.'.php');
            $result = ob_get_clean();
            $result = str_replace(array_keys($varFields), $varFields, $result);
        }

        $result = $this->acympluginHelper->managePicts($tag, $result);

        return $result;
    }

    public function replaceUserInformation(&$email, &$user, $send = true)
    {
        $this->_replaceCoupons($email, $user, $send);
    }

    private function _replaceCoupons(&$email, &$user, $send = true)
    {
        $tags = $this->acympluginHelper->extractTags($email, 'woocommerce_coupon');
        if (empty($tags)) {
            return;
        }

        $tagsReplaced = array();
        foreach ($tags as $i => $oneTag) {
            if (isset($tagsReplaced[$i])) {
                continue;
            }
            if (!$send || empty($user->id)) {
                $tagsReplaced[$i] = '<i>'.acym_translation('ACYM_CHECK_EMAIL_COUPON').'</i>';
            } else {
                $tagsReplaced[$i] = $this->generateCoupon($oneTag, $user);
            }
        }

        $this->acympluginHelper->replaceTags($email, $tagsReplaced, true);
    }

    private function generateCoupon($tag, $user)
    {
        if (empty($tag->code) || empty($tag->amount) || empty($tag->type) || !in_array($tag->type, array('fixed_cart', 'fixed_product', 'percent'))) return '';

        $intAttributes = array('amount', 'free', 'min', 'max', 'exclsale', 'use', 'items');
        foreach ($intAttributes as $oneAttribute) {
            if (empty($tag->$oneAttribute)) $tag->$oneAttribute = 0;
            $tag->$oneAttribute = intval($tag->$oneAttribute);
        }

        if (empty($tag->amount)) return '';


        $clean_name = strtoupper($user->name);
        $space = strpos($clean_name, ' ');
        if (!empty($space)) $clean_name = substr($clean_name, 0, $space);

        $couponCode = str_replace(
            array(
                '[name]',
                '[userid]',
                '[email]',
                '[key]',
                '[value]',
            ),
            array(
                $clean_name,
                $user->id,
                $user->email,
                acym_generateKey(5),
                $tag->amount,
            ),
            $tag->code
        );


        $coupon = array(
            'post_title' => $couponCode,
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'shop_coupon',
        );

        $couponId = wp_insert_post($coupon);

        update_post_meta($couponId, 'discount_type', $tag->type);
        update_post_meta($couponId, 'coupon_amount', $tag->amount);
        update_post_meta($couponId, 'expiry_date', empty($tag->end) ? '' : $tag->end);
        update_post_meta($couponId, 'date_expires', empty($tag->end) ? null : strtotime($tag->end));

        update_post_meta($couponId, 'usage_limit', $tag->use);
        update_post_meta($couponId, 'usage_limit_per_user', 0);
        update_post_meta($couponId, 'limit_usage_to_x_items', $tag->items);
        update_post_meta($couponId, 'usage_count', 0);

        update_post_meta($couponId, 'minimum_amount', empty($tag->min) ? '' : $tag->min);
        update_post_meta($couponId, 'maximum_amount', empty($tag->max) ? '' : $tag->max);

        update_post_meta($couponId, 'free_shipping', empty($tag->free) ? 'no' : 'yes');
        update_post_meta($couponId, 'exclude_sale_items', empty($tag->exclsale) ? 'no' : 'yes');


        update_post_meta($couponId, 'product_ids', implode(',', $this->cleanElements($tag->prod)));
        update_post_meta($couponId, 'exclude_product_ids', implode(',', $this->cleanElements($tag->exclprod)));

        update_post_meta($couponId, 'product_categories', $this->cleanElements($tag->cat));
        update_post_meta($couponId, 'exclude_product_categories', $this->cleanElements($tag->exclcat));


        update_post_meta($couponId, 'individual_use', 'yes');
        update_post_meta($couponId, 'customer_email', array($user->email));


        return $couponCode;
    }

    private function cleanElements($elements)
    {
        $elements = empty($elements) ? array() : explode(',', $elements);
        acym_arrayToInteger($elements);
        foreach ($elements as $i => $oneElement) {
            if (empty($oneElement)) unset($elements[$i]);
        }

        return $elements;
    }
}
