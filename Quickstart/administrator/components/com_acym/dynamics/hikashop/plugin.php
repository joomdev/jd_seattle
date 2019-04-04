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

class plgAcymHikashop extends acymPlugin
{
    function __construct()
    {
        parent::__construct();
        $this->cms = 'Joomla';
        if (!defined('JPATH_ADMINISTRATOR') || !file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_hikashop'.DS)) {
            $this->installed = false;
        }
    }

    function insertOptions()
    {
        $plugins = new stdClass();
        $plugins->name = 'HikaShop';
        $plugins->icon = ACYM_DYNAMICS_URL.basename(__DIR__).'/icon.ico';
        $plugins->plugin = __CLASS__;

        return $plugins;
    }

    function _categories($filter_cat)
    {
        $cats = acym_loadObjectList("SELECT category_id AS id, category_parent_id AS parent_id, category_name AS title FROM `#__hikashop_category` WHERE category_type = 'product'", 'id');
        $this->cats = array();
        if (!empty($cats)) {
            foreach ($cats as $oneCat) {
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
            $this->catvalues[] = acym_selectOption($cat->id, str_repeat(" - - ", $level).$cat->title);
            $this->_handleChildren($cat->id, $level + 1);
        }
    }

    function contentPopup()
    {
        acym_loadLanguageFile('com_hikashop', JPATH_SITE);
        $tabHelper = acym_get('helper.tab');
        $tabHelper->startTab(acym_translation('ACYM_ONE_BY_ONE'));

        $displayOptions = array(
            array(
                'title' => 'ACYM_DISPLAY',
                'type' => 'radio',
                'name' => 'type',
                'options' => array(
                    'title' => 'ACYM_TITLE_ONLY',
                    'intro' => 'ACYM_INTRO_ONLY',
                    'full' => 'ACYM_FULL_TEXT',
                ),
                'default' => 'full',
            ),
            array(
                'title' => 'ACYM_PRICE',
                'type' => 'radio',
                'name' => 'price',
                'options' => array(
                    'full' => 'ACYM_APPLY_DISCOUNTS',
                    'no_discount' => 'ACYM_NO_DISCOUNT',
                    'none' => 'ACYM_NO',
                ),
                'default' => 'full',
            ),
        );

        echo $this->acympluginHelper->displayOptions($displayOptions, 'hikashop_product');


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
                    'product_id' => 'ACYM_ID',
                    'product_created' => 'ACYM_DATE_CREATED',
                    'product_modified' => 'ACYM_MODIFICATION_DATE',
                    'product_name' => 'ACYM_TITLE',
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

        echo $this->acympluginHelper->displayOptions($displayOptions, 'hikashop_auto_product', 'grouped');

        if (!empty($this->catvalues)) {
            echo '<div class="acym__popup__listing padding-0">';
            foreach ($this->catvalues as $oneCat) {
                if (empty($oneCat->value)) {
                    continue;
                }
                echo '<div class="cell grid-x acym__listing__row acym__listing__row__popup" data-id="'.$oneCat->value.'" onclick="applyContenthikashop_auto_product('.$oneCat->value.', this);">
                        <div class="cell medium-5">'.$oneCat->text.'</div>
                    </div>';
            }
            echo '</div>';
        }

        $tabHelper->endTab();
        $tabHelper->startTab(acym_translation('HIKA_ABANDONED_CART'));

        $methods = acym_loadObjectList('SELECT payment_id, payment_name FROM #__hikashop_payment', 'payment_id');

        $paymentMethods = array('' => 'ALL_PAYMENT_METHODS');
        foreach ($methods as $method) {
            $paymentMethods[$method->payment_id] = $method->payment_name;
        }

        $displayOptions = array(
            array(
                'title' => 'ACYM_DISPLAY',
                'type' => 'radio',
                'name' => 'type',
                'options' => array(
                    'title' => 'ACYM_TITLE_ONLY',
                    'intro' => 'ACYM_INTRO_ONLY',
                    'full' => 'ACYM_FULL_TEXT',
                ),
                'default' => 'full',
            ),
            array(
                'title' => 'PAYMENT_METHOD',
                'type' => 'select',
                'name' => 'paymentcart',
                'options' => $paymentMethods,
            ),
            array(
                'title' => 'ACYM_DATE_CREATED',
                'type' => 'intextfield',
                'name' => 'nbdayscart',
                'text' => 'DAYS_AFTER_ORDERING',
                'default' => 1,
            ),
        );

        echo $this->acympluginHelper->displayOptions($displayOptions, 'hikashop_abandonedcart', 'simple');

        $tabHelper->endTab();
        $tabHelper->startTab(acym_translation('ACYM_COUPON'));

        $query = "SELECT `product_id`, CONCAT(product_name, ' ( ', product_code, ' )') AS `title` 
                            FROM #__hikashop_product 
                            WHERE `product_type`='main' AND `product_published` = 1  
                            ORDER BY `product_code` ASC";
        $results = acym_loadObjectList($query);

        $products = array(0 => 'ACYM_NONE');
        foreach ($results as $result) {
            $products[$result->product_id] = $result->title;
        }

        $parent = acym_loadResult('SELECT category_id FROM #__hikashop_category WHERE category_parent_id = 0');

        $query = 'SELECT a.category_id, a.category_name  
                    FROM #__hikashop_category AS a 
                    WHERE a.category_type = "tax" 
                        AND a.category_published = 1 
                        AND a.category_parent_id != '.$parent.' 
                    ORDER BY a.category_ordering ASC';

        $results = acym_loadObjectList($query);

        $taxes = array(0 => 'ACYM_NONE');
        foreach ($results as $result) {
            $taxes[$result->category_id] = $result->category_name;
        }

        $query = 'SELECT currency_id AS value, CONCAT(currency_symbol, " ", currency_code) AS text FROM #__hikashop_currency WHERE currency_published = 1';
        $currencies = acym_loadObjectList($query);

        $displayOptions = array(
            array(
                'title' => 'DISCOUNT_CODE',
                'type' => 'text',
                'name' => 'code',
                'default' => '[name][key][value]',
                'class' => 'acym_plugin__larger_text_field',
                'large' => true,
            ),
            array(
                'title' => 'DISCOUNT_FLAT_AMOUNT',
                'type' => 'custom',
                'name' => 'flat',
                'output' => '<input type="text" name="flathikashop_coupon" id="flat" onchange="updateDynamichikashop_coupon();" value="0" class="acym_plugin_text_field" style="display: inline-block;" />
                            '.acym_select($currencies, 'currencyhikashop_coupon', null, 'onchange="updateDynamichikashop_coupon();" style="width: 80px;"'),
                'js' => 'otherinfo += "| flat:" + $(\'input[name="flathikashop_coupon"]\').val();
                        otherinfo += "| currency:" + $(\'[name="currencyhikashop_coupon"]\').val();',
            ),
            array(
                'title' => 'DISCOUNT_PERCENT_AMOUNT',
                'type' => 'text',
                'name' => 'percent',
                'default' => '0',
            ),
            array(
                'title' => 'DISCOUNT_START_DATE',
                'type' => 'date',
                'name' => 'start',
                'default' => '',
            ),
            array(
                'title' => 'DISCOUNT_END_DATE',
                'type' => 'date',
                'name' => 'end',
                'default' => '',
            ),
            array(
                'title' => 'MINIMUM_ORDER_VALUE',
                'type' => 'text',
                'name' => 'min',
                'default' => '0',
            ),
            array(
                'title' => 'DISCOUNT_QUOTA',
                'type' => 'text',
                'name' => 'quota',
                'default' => '',
            ),
            array(
                'title' => 'PRODUCT',
                'type' => 'select',
                'name' => 'product',
                'options' => $products,
                'default' => '0',
            ),
            array(
                'title' => 'TAXATION_CATEGORY',
                'type' => 'select',
                'name' => 'tax',
                'options' => $taxes,
                'default' => '0',
            ),
        );

        echo $this->acympluginHelper->displayOptions($displayOptions, 'hikashop_coupon', 'simple');

        $tabHelper->endTab();

        $tabHelper->display('plugin');
    }

    function displayListing()
    {
        echo '<input type="hidden" name="plugin" value="'.__CLASS__.'" />';
        $query = 'SELECT SQL_CALC_FOUND_ROWS a.* FROM #__hikashop_product AS a ';

        $pageInfo = new stdClass();
        $pageInfo->limit = acym_getCMSConfig('list_limit');
        $pageInfo->page = acym_getVar('int', 'pagination_page_ajax', 1);
        $pageInfo->start = ($pageInfo->page - 1) * $pageInfo->limit;
        $pageInfo->search = acym_getVar('string', 'plugin_search', '');
        $pageInfo->filter_cat = acym_getVar('int', 'plugin_category', 0);
        $pageInfo->order = 'a.product_id';
        $pageInfo->orderdir = 'DESC';

        $searchFields = array('a.product_id', 'a.product_name', 'a.product_code');
        if (!empty($pageInfo->search)) {
            $searchVal = '%'.acym_getEscaped($pageInfo->search, true).'%';
            $filters[] = implode(" LIKE ".acym_escapeDB($searchVal)." OR ", $searchFields)." LIKE ".acym_escapeDB($searchVal);
        }
        if (!empty($pageInfo->filter_cat)) {
            $query .= 'JOIN #__hikashop_product_category AS b ON a.product_id = b.product_id';
            $filters[] = "b.category_id = ".intval($pageInfo->filter_cat);
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

        echo '<div class="cell grid-x hide-for-small-only plugin_listing_headers">
                <div class="cell medium-5">'.acym_translation('ACYM_TITLE').'</div>
                <div class="cell medium-3">'.acym_translation('ACYM_DATE_CREATED').'</div>
                <div class="cell medium-1">'.acym_translation('ACYM_ID').'</div>
            </div>';
        foreach ($rows as $row) {
            $class = 'cell grid-x acym__listing__row acym__listing__row__popup';
            if (in_array($row->product_id, $selected)) {
                $class .= ' selected_row';
            }
            echo '<div class="'.$class.'" data-id="'.$row->product_id.'" onclick="applyContenthikashop_product('.$row->product_id.', this);">
                    <div class="cell medium-5">'.$row->product_name.'</div>
                    <div class="cell medium-3">'.acym_getDate($row->product_created).'</div>
                    <div class="cell medium-1">'.$row->product_id.'</div>
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
        $tags = $this->acympluginHelper->extractTags($email, 'hikashop_auto_product');
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

            $query = 'SELECT DISTINCT b.`product_id` FROM #__hikashop_product_category AS a 
                    LEFT JOIN #__hikashop_product AS b ON a.product_id = b.product_id';

            $where = array();

            if (!empty($selectedArea)) {
                $where[] = 'a.category_id IN ('.implode(',', $selectedArea).')';
            }

            $where[] = "b.`product_published` = 1";

            if (!empty($parameter->filter) && !empty($email->params['lastgenerateddate'])) {
                $condition = 'b.`product_created` >\''.$email->params['lastgenerateddate'].'\'';
                if ($parameter->filter == 'modify') {
                    $condition .= ' OR b.`product_modified` >\''.$email->params['lastgenerateddate'].'\'';
                }
                $where[] = $condition;
            }

            $query .= ' WHERE ('.implode(') AND (', $where).')';

            if (!empty($parameter->order)) {
                $ordering = explode(',', $parameter->order);
                if ($ordering[0] == 'rand') {
                    $query .= ' ORDER BY rand()';
                } else {
                    $query .= ' ORDER BY b.`'.acym_secureDBColumn(trim($ordering[0])).'` '.acym_secureDBColumn(trim($ordering[1]));
                }
            }

            if (!empty($parameter->max)) {
                $query .= ' LIMIT '.(int)$parameter->max;
            }
            $allArticles = acym_loadResultArray($query);

            if (!empty($parameter->min) && count($allArticles) < $parameter->min) {
                $return->status = false;
                $return->message = 'Not enough products for the tag '.$oneTag.' : '.count($allArticles).' / '.$parameter->min;
            }

            $stringTag = '';
            if (!empty($allArticles)) {
                if (file_exists(ACYM_TEMPLATE.'plugins'.DS.'hikashop_auto_product.php')) {
                    ob_start();
                    require(ACYM_TEMPLATE.'plugins'.DS.'hikashop_auto_product.php');
                    $stringTag = ob_get_clean();
                } else {
                    $arrayElements = array();
                    foreach ($allArticles as $oneArticleId) {
                        $args = array();
                        $args[] = 'hikashop_product:'.$oneArticleId;
                        if (!empty($parameter->type)) {
                            $args[] = 'type:'.$parameter->type;
                        }
                        if (!empty($parameter->lang)) {
                            $args[] = 'lang:'.$parameter->lang;
                        }
                        if (!empty($parameter->price)) {
                            $args[] = 'price:'.$parameter->price;
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
        $tags = $this->acympluginHelper->extractTags($email, 'hikashop_product');
        if (empty($tags)) {
            return;
        }

        $this->readmore = empty($email->template->readmore) ? JText::_('ACYM_READ_MORE') : '<img src="'.ACYM_LIVE.$email->template->readmore.'" alt="'.JText::_('ACYM_READ_MORE', true).'" />';

        if (!include_once(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) {
            return false;
        }

        $this->hikaConfig = hikashop_config();
        $this->productClass = hikashop_get('class.product');
        $this->imageHelper = hikashop_get('helper.image');
        $this->currencyClass = hikashop_get('class.currency');
        $this->translationHelper = hikashop_get('helper.translation');

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
        if (empty($tag->lang) && !empty($email->language)) {
            $tag->lang = $email->language;
        }

        $query = 'SELECT b.*,a.*
                    FROM #__hikashop_product AS a
                    LEFT JOIN #__hikashop_file AS b ON a.product_id = b.file_ref_id AND file_type = "product"
                    WHERE a.product_id = '.intval($tag->id).'
                    ORDER BY b.file_ordering ASC, b.file_id ASC';

        $product = acym_loadObject($query);

        if (empty($product)) {
            if (acym_isAdmin()) {
                acym_enqueueMessage('The product "'.$tag->id.'" could not be loaded', 'notice');
            }

            return '';
        }

        if ($product->product_type == 'variant') {
            $query = 'SELECT * 
                        FROM #__hikashop_variant AS a 
                        LEFT JOIN #__hikashop__characteristic AS b ON a.variant_characteristic_id = b.characteristic_id 
                        WHERE a.variant_product_id='.intval($tag->id).' 
                        ORDER BY a.ordering';
            $product->characteristics = acym_loadObjectList($query);

            $query = 'SELECT b.*,a.*
                        FROM #__hikashop_product AS a
                        LEFT JOIN #__hikashop_file AS b ON a.product_id = b.file_ref_id AND file_type = "product"
                        WHERE a.product_id = '.intval($product->product_parent_id).'
                        ORDER BY b.file_ordering ASC, b.file_id ASC';
            $parentProduct = acym_loadObject($query);

            $this->productClass->checkVariant($product, $parentProduct);
        }

        if ($this->translationHelper->isMulti(true, false)) {
            $this->acympluginHelper->translateItem($product, $tag, 'hikashop_product');
        }

        $varFields = array();
        foreach ($product as $fieldName => $oneField) {
            $varFields['{'.$fieldName.'}'] = $oneField;
        }

        $tag->itemid = 0;
        $main_currency = $currency_id = (int)$this->hikaConfig->get('main_currency', 1);
        $zone_id = explode(',', $this->hikaConfig->get('main_tax_zone', 0));

        $zone_id = count($zone_id) ? array_shift($zone_id) : 0;

        $ids = array($product->product_id);
        $discount_before_tax = (int)$this->hikaConfig->get('discount_before_tax', 0);
        $this->currencyClass->getPrices($product, $ids, $currency_id, $main_currency, $zone_id, $discount_before_tax);
        $finalPrice = '';
        if (empty($tag->price) || $tag->price == 'full') {
            $finalPrice = @$this->currencyClass->format($product->prices[0]->price_value_with_tax, $product->prices[0]->price_currency_id);
            if (!empty($product->discount)) {
                $finalPrice = '<strike>'.$this->currencyClass->format($product->prices[0]->price_value_without_discount_with_tax, $product->prices[0]->price_currency_id).'</strike> '.$finalPrice;
            }
        } elseif ($tag->price == 'no_discount') {
            $finalPrice = $this->currencyClass->format($product->prices[0]->price_value_without_discount_with_tax, $product->prices[0]->price_currency_id);
        }
        $varFields['{finalPrice}'] = $finalPrice;

        if (empty($tag->type) || $tag->type == 'full') {
            $description = $product->product_description;
        } else {
            $pos = strpos($product->product_description, '<hr id="system-readmore"');
            if ($pos !== false) {
                $description = substr($product->product_description, 0, $pos);
            } else {
                $description = substr($product->product_description, 0, 100).'...';
            }
        }

        $link = 'index.php?option=com_hikashop&ctrl=product&task=show&cid='.$product->product_id;
        if (!empty($tag->lang)) {
            $link .= '&lang='.substr($tag->lang, 0, strpos($tag->lang, ','));
        }
        if (!empty($tag->itemid)) {
            $link .= '&Itemid='.$tag->itemid;
        }
        if (!empty($product->product_canonical)) {
            $link = $product->product_canonical;
        }
        $link = acym_frontendLink($link, false);
        $varFields['{link}'] = $link;

        if (!empty($product->file_path)) {
            $img = $this->imageHelper->getThumbnail($product->file_path, null);
            if ($img->success) {
                $varFields['{pictHTML}'] = $img->url;
            } else {
                $varFields['{pictHTML}'] = $this->imageHelper->display($product->file_path, false, $product->product_name);
            }
        }

        if (file_exists(ACYM_MEDIA.'plugins'.DS.'hikashop_product.php')) {
            ob_start();
            require(ACYM_MEDIA.'plugins'.DS.'hikashop_product.php');
            $result = ob_get_clean();
            $result = str_replace(array_keys($varFields), $varFields, $result);

            return $result;
        }

        $result = '';
        $astyle = '';

        if (empty($tag->type) || $tag->type != 'title') {
            $result .= '<div class="acym_product">';
            $astyle = 'style="text-decoration:none;" name="product-'.$product->product_id.'"';
        }

        $result .= '<a '.$astyle.' target="_blank" href="'.$link.'">';
        if (empty($tag->type) || $tag->type != 'title') {
            $result .= '<h2 class="acym_title">';
        }
        $result .= $product->product_name;
        if (!empty($finalPrice)) {
            $result .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$finalPrice;
        }
        if (empty($tag->type) || $tag->type != 'title') {
            $result .= '</h2>';
        }
        $result .= '</a>';
        if (empty($tag->type) || $tag->type != 'title') {
            if (!empty($product->file_path)) {
                $uploadFolder = ltrim(JPath::clean(html_entity_decode($this->hikaConfig->get('uploadfolder'))), DS);
                $uploadFolder = rtrim($uploadFolder, DS).DS;
                $this->imageHelper->uploadFolder_url = str_replace(DS, '/', $uploadFolder);
                $this->imageHelper->uploadFolder_url = ACYM_LIVE.$this->imageHelper->uploadFolder_url;
                $pictureHTML = $this->imageHelper->display($product->file_path, false, $product->product_name, '', '', $this->hikaConfig->get('thumbnail_x', 100), $this->hikaConfig->get('thumbnail_y', 100));
                $pictureHTML = '<a target="_blank" style="text-decoration:none;border:0" href="'.$link.'" >'.$pictureHTML.'</a>';
                $result .= '<table class="acym_content"><tr><td valign="top" style="padding-right:5px">'.$pictureHTML.'</td><td>'.$description.'</td></tr></table>';
            } else {
                $result .= $description;
            }
        }
        if (empty($tag->type) || $tag->type != 'title') {
            $result .= '</div>';
        }

        return $result;
    }

    function replaceUserInformation(&$email, &$user, $send = true)
    {
        if (!include_once(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) {
            return false;
        }

        $this->hikaConfig = hikashop_config();

        $this->_replaceAbandonedCarts($email, $user);
        $this->_replaceCoupons($email, $user, $send);
    }

    function _replaceAbandonedCarts(&$email, &$user, $send = true)
    {
        $tags = $this->acympluginHelper->extractTags($email, 'hikashop_abandonedcart');
        if (empty($tags)) {
            return;
        }

        $tagsReplaced = array();
        foreach ($tags as $i => $oneTag) {
            if (isset($tagsReplaced[$i])) {
                continue;
            }
            $tagsReplaced[$i] = $this->_replaceAbandonedCart($oneTag, $user);
        }

        $this->acympluginHelper->replaceTags($email, $tagsReplaced, true);

        $this->_replaceOne($email);
    }

    function _replaceAbandonedCart($oneTag, $user)
    {
        if (empty($user->cms_id)) {
            return '';
        }

        $delay = 0;
        if (!empty($oneTag->nbdayscart)) {
            $delay = ($oneTag->nbdayscart * 86400);
        }

        $senddate = (time() - intval($delay));

        $createdstatus = $this->hikaConfig->get('order_created_status', 'created');

        $myquery = 'SELECT c.product_id
					FROM #__hikashop_order AS a
					LEFT JOIN #__hikashop_order AS b
						ON a.order_user_id = b.order_user_id
						AND b.order_id > a.order_id
					JOIN #__hikashop_order_product AS c
						ON a.order_id = c.order_id
					JOIN #__hikashop_user AS hikauser
						ON a.order_user_id = hikauser.user_id ';

        if (!empty($oneTag->paymentcart)) {
            $myquery .= 'JOIN #__hikashop_payment AS payment
														ON payment.payment_type = a.order_payment_method
														AND payment.payment_id = '.intval($oneTag->paymentcart);
        }

        $myquery .= ' WHERE hikauser.user_cms_id = '.intval($user->cms_id).' AND a.order_status = '.acym_escapeDB($createdstatus).' AND b.order_id IS NULL ';
        $myquery .= ' AND FROM_UNIXTIME(a.order_created,"%Y %d %m") = FROM_UNIXTIME('.$senddate.',"%Y %d %m")';

        $Products = acym_loadResultArray($myquery);
        if (empty($Products)) {
            return '';
        }

        $arrayElements = array();
        foreach ($Products as $oneProductId) {
            $args = array();
            $args[] = 'hikashop_product:'.$oneProductId;
            if (!empty($oneTag->type)) {
                $args[] = 'type:'.$oneTag->type;
            }
            if (!empty($oneTag->lang)) {
                $args[] = 'lang:'.$oneTag->lang;
            }
            $arrayElements[] = '{'.implode('|', $args).'}';
        }
        $stringTag = $this->acympluginHelper->getFormattedResult($arrayElements, $oneTag);

        return $stringTag;
    }

    function _replaceCoupons(&$email, &$user, $send = true)
    {
        $tags = $this->acympluginHelper->extractTags($email, 'hikashop_coupon');
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
                $tagsReplaced[$i] = $this->generateCoupon($oneTag, $user, $i);
            }
        }

        $this->acympluginHelper->replaceTags($email, $tagsReplaced, true);
    }

    function generateCoupon($tag, $user, $raw)
    {
        if (empty($tag->code)) {
            list($minimum_order, $quota, $start, $end, $percent_amount, $flat_amount, $currency_id, $code, $product_id, $tax_id) = explode('|', $raw);
            $minimum_order = substr($minimum_order, strpos($minimum_order, ':') + 1);
            $tax_id = intval($tax_id);
        } else {
            $minimum_order = $tag->min;
            $quota = $tag->quota;
            $start = $tag->start;
            $end = $tag->end;
            $percent_amount = $tag->percent;
            $flat_amount = $tag->flat;
            $currency_id = $tag->currency;
            $code = $tag->code;
            $product_id = $tag->product;
            $tax_id = $tag->tax;
        }

        $key = acym_generateKey(5);

        if ($percent_amount > 0) {
            $value = $percent_amount;
        } else {
            $value = $flat_amount;
        }

        $value = str_replace(',', '.', $value);

        if ($start) {
            $start = hikashop_getTime($start);
        }
        if ($end) {
            $end = hikashop_getTime($end);
        }

        $clean_name = strtoupper($user->name);
        $space = strpos($clean_name, ' ');
        if (!empty($space)) {
            $clean_name = substr($clean_name, 0, $space);
        }

        $code = str_replace(
            array(
                '[name]',
                '[clean_name]',
                '[subid]',
                '[email]',
                '[key]',
                '[flat]',
                '[percent]',
                '[value]',
                '[prodid]',
            ),
            array(
                $user->name,
                $clean_name,
                $user->id,
                $user->email,
                $key,
                $flat_amount,
                $percent_amount,
                $value,
                $product_id,
            ),
            $code
        );

        $query = 'INSERT IGNORE INTO #__hikashop_discount (
            `discount_code`,
            `discount_percent_amount`,
            `discount_flat_amount`,
            `discount_type`,
            `discount_start`,
            `discount_end`,
            `discount_minimum_order`,
            `discount_quota`,
            `discount_currency_id`,
            `discount_product_id`,
            `discount_tax_id`,
            `discount_published`
		) VALUES (
		    '.acym_escapeDB($code).',
		    '.acym_escapeDB($percent_amount).',
		    '.acym_escapeDB($flat_amount).',
		    "coupon",
		    '.acym_escapeDB($start).',
		    '.acym_escapeDB($end).',
		    '.acym_escapeDB($minimum_order).',
		    '.acym_escapeDB($quota).',
		    '.acym_escapeDB($currency_id).',
		    '.acym_escapeDB($product_id).',
		    '.acym_escapeDB($tax_id).',
		    1
        )';

        acym_query($query);

        return $code;
    }
}
