<?php
/**
 * @version     1.6.1
 * @package     Sellacious Finder Module
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access

defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.core');
JHtml::_('formbehavior.chosen');
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'mod_sellacious_finder_for_store/template.css', false, true, false);
JHtml::_('stylesheet', 'mod_sellacious_finder_for_store/overlay.css', false, true, false);

JHtml::addIncludePath(JPATH_SITE . '/components/com_finder/helpers/html');

JHtml::_('jquery.framework');
JHtml::_('script', 'media/jui/js/jquery.autocomplete.js', false, false, false, false, true);

$LABEL   = JText::_('COM_SELLACIOUS_SEARCH_PREFIX_CATEGORIES');
$noResult= JText::_('MOD_SELLACIOUS_FINDER_FOR_STORE_NO_RESULT');
$isImg = '';

// Product result
$html = '<div class="search-hint">';
if ($show_product_image):
	$html .= '<div class="search-hint-icon"><img src="#SRC#"/></div>';
	$isImg = 'withimage';
endif;
$html .= '<div class="search-hint-content ' . $isImg . '">';
$html .= '<span class="search-hint-title">#TITLE#</span>';
if ($show_product_price):
	$html .= '<span class="pull-right">#PRICE#</span>';
endif;
if ($show_product_category):
	$html .= '<span class="search-hint-category"><strong>' . $LABEL . '</strong>#CATEGORIES#</span>';
endif;
$html .= '</div><div class="clearfix"></div></div>';

$html = json_encode($html);

// Product in category result
$isCategoryImg = '';
$categoryHtml  = '<div class="search-hint category">';

if ($show_category_image):
	$categoryHtml .= '<div class="search-hint-icon"><img src="#SRC#"/></div>';
	$isCategoryImg = 'withimage';
endif;

$categoryHtml .= '<div class="search-hint-content ' . $isCategoryImg . '">';
$categoryHtml .= '<span class="search-hint-title">#KEYWORD# in <strong>#TITLE#</strong></span>';
$categoryHtml .= '</div><div class="clearfix"></div></div>';

$categoryHtml = json_encode($categoryHtml);

// Categories result
$isCategoriesImg = '';
$categoriesHtml  = '<div class="search-hint category">';

if ($show_categories_image):
	$categoriesHtml .= '<div class="search-hint-icon"><img src="#SRC#"/></div>';
	$isCategoriesImg = 'withimage';
endif;

$categoriesHtml .= '<div class="search-hint-content ' . $isCategoriesImg . '">';
$categoriesHtml .= '<span class="search-hint-title">Category: #TITLE#</span>';
$categoriesHtml .= '</div><div class="clearfix"></div></div>';

$categoriesHtml = json_encode($categoriesHtml);

// Ordering
$orderScript = "";

foreach ($search_order as $item => $order)
{
	$orderScript .= "
		suggestions = suggestions.concat(" . $item . ");
	";
}

$script = "
	var boxwidth = ('#finder$module->id').width;
	jQuery(function($) {
		$('#finder$module->id').devbridgeAutocomplete({
			serviceUrl: '$ajaxUrl',
			paramName: 'q',
			minChars: 1,
			noCache: true,
			maxHeight: 280,
			width: boxwidth,
			zIndex: 9999,
			showNoSuggestionNotice: true,
			noSuggestionNotice: '$noResult',
			triggerSelectOnValidInput: false,
			containerClass: 'search-autocomplete-suggestions',
			appendTo:'#sella_results$module->id',
			deferRequestBy: 500,
			transformResult: function(response) {
				if (typeof response === 'string'){
					response = $.parseJSON(response);
				}  

				var suggestions = [];
				var category 	= [];
				var product   	= [];
				var categories  = [];
				
				for(var k in response) {
				   for (var i in response[k]) {
				   	  if (response[k][i].type == 'category') {
				      	category.push(response[k][i]);
				      } else if (response[k][i].type == 'product') {
				      	product.push(response[k][i]);
				      } else if (response[k][i].type == 'categories') {
				      	categories.push(response[k][i]);
				      } 
				   }
				}
				
				" . $orderScript . "
				
				response.suggestions = suggestions;
				
				return response;
			},
			formatResult: function (suggestion, currentValue) {
				if (!currentValue) return suggestion.value;
				if (suggestion.type == 'product') {
					var html = $html;
					
					return html
						.replace('#SRC#', suggestion.image)
						.replace('#TITLE#', suggestion.value)
						.replace('#PRICE#', suggestion.price)
						.replace('#CATEGORIES#', suggestion.categories);
				} else if (suggestion.type == 'category') {
					var categoryHtml = $categoryHtml;
					
					return categoryHtml
						.replace('#KEYWORD#', currentValue)
						.replace('#TITLE#', suggestion.value)
						.replace('#SRC#', suggestion.image);
				} else if (suggestion.type == 'categories') {
					var categoriesHtml = $categoriesHtml;
					var value = suggestion.value;
					var regex = new RegExp('('+currentValue+')', 'gi');
					value = value.replace(regex, '<strong>$1</strong>');
					
					return categoriesHtml
						.replace('#TITLE#', value)
						.replace('#SRC#', suggestion.image);
				}  
			},
			onSelect: function(suggestion) {
				if (suggestion.type == 'product') {
					window.location.href = suggestion.link;
				} else if (suggestion.type == 'category') {
				   	" . ($category_redirect == 1 ? "
					window.location.href = 'index.php?option=com_sellacious&view=search&i=$integration&q=' + suggestion.q + '&category=' + suggestion.category_id;
					" : ($category_redirect == 2 ? "
					window.location.href = suggestion.link;
					" : "
					window.location.href = suggestion.clink;
					")) . "
				} else if (suggestion.type == 'categories') {
				    " . ($categories_redirect == 1 ? "
					window.location.href = suggestion.link;
					" : "
					window.location.href = suggestion.plink;
					") . "
				} 
			}
		});
	});
";

JFactory::getDocument()->addScriptDeclaration($script);

$query = JFactory::getApplication()->input->getString('q');
?>
<div class="sella-search finder-overlay" id="sella<?php echo $module->id; ?>">
	<div class="search-store-actionbtn">
		<i class="search-icon fa fa-search"></i>
		<i id="search_close" class="remove-search fa fa-times"></i>
	</div>
	<div class="clearfix"></div>
	<div class="sella-store-searchwrapper">
		<div class="sella-searchbar">
			<form id="finder-for-store-search" action="index.php" method="get" class="sellacious-search">
				<div class="sellainputarea">
					<input type="text" name="q" id="finder<?php echo $module->id; ?>" value="<?php echo htmlspecialchars($query, ENT_COMPAT, 'UTF-8'); ?>"
						   placeholder="<?php echo ($finder_placeholder != '') ? $finder_placeholder : '' ?>" class="inputbox"/>
					<button type="submit" class="btn btn-primary findersubmit"><span class="fa fa-search"></span></button>
				</div>
				<input type="hidden" name="option" value="com_sellacious"/>
				<input type="hidden" name="view" value="search"/>
				<input type="hidden" name="i" value="<?php echo $integration; ?>"/>
				<div id="sella_results<?php echo $module->id; ?>"></div>
			</form>
		</div>
	</div>
</div>

<script>
	jQuery(document).ready(function ($) {
		$(".search-store-actionbtn .search-icon").on('click', function () {
			$(".sella-store-searchwrapper").fadeIn(200);
			$(".search-store-actionbtn .remove-search").show();
			$(".search-store-actionbtn .search-icon").hide();
			$("#finder_overlay").focus();
		});

		$("#search_close").on('click', function () {
			$(".sella-store-searchwrapper").fadeOut(200);
			$(".search-store-actionbtn .remove-search").hide();
			$(".search-store-actionbtn .search-icon").show();
		});

		// press esc to hide search
		$(document).keyup(function (e) {
			if (e.keyCode == 27) {
				$(".sella-store-searchwrapper").fadeOut(200);
				$(".search-store-actionbtn .remove-search").fadeOut(200);
				$(".search-store-actionbtn .search-icon").delay(200).fadeIn(200);
			}
		});
	});
</script>
