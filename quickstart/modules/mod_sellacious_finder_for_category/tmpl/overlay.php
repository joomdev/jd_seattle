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
JHtml::_('stylesheet', 'mod_sellacious_finder_for_category/template.css', false, true, false);
JHtml::_('stylesheet', 'mod_sellacious_finder_for_category/overlay.css', false, true, false);

JHtml::addIncludePath(JPATH_SITE . '/components/com_finder/helpers/html');

JHtml::_('jquery.framework');
JHtml::_('script', 'media/jui/js/jquery.autocomplete.js', false, false, false, false, true);

$LABEL   = JText::_('COM_SELLACIOUS_SEARCH_PREFIX_CATEGORIES');
$noResult= JText::_('MOD_SELLACIOUS_FINDER_FOR_CATEGORY_NO_RESULT');
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

// Product in seller result
$isSellerImg = '';
$sellerHtml    = '<div class="search-hint seller">';

if ($show_seller_image):
	$sellerHtml .= '<div class="search-hint-icon"><img src="#SRC#"/></div>';
	$isSellerImg = 'withimage';
endif;

$sellerHtml .= '<div class="search-hint-content ' . $isSellerImg . '">';
$sellerHtml .= '<span class="search-hint-title">#KEYWORD# <strong>sold</strong> by <strong>#TITLE#</strong></span>';
$sellerHtml .= '</div><div class="clearfix"></div></div>';

$sellerHtml = json_encode($sellerHtml);

// Sellers result
$isSellersImg = '';
$sellersHtml    = '<div class="search-hint seller">';

if ($show_sellers_image):
	$sellersHtml .= '<div class="search-hint-icon"><img src="#SRC#"/></div>';
	$isSellersImg = 'withimage';
endif;

$sellersHtml .= '<div class="search-hint-content ' . $isSellersImg . '">';
$sellersHtml .= '<span class="search-hint-title">Store: #TITLE#</strong></span>';
$sellersHtml .= '</div><div class="clearfix"></div></div>';

$sellersHtml = json_encode($sellersHtml);

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
				var product   	= [];
				var seller    	= [];
				var sellers     = [];
				
				for(var k in response) {
				   for (var i in response[k]) {
				   	  if (response[k][i].type == 'product') {
				      	product.push(response[k][i]);
				      } else if (response[k][i].type == 'seller') {
				      	seller.push(response[k][i]);
				      } else if (response[k][i].type == 'sellers') {
				      	sellers.push(response[k][i]);
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
				} else if (suggestion.type == 'seller') {
					var sellerHtml = $sellerHtml;
					
					return sellerHtml
						.replace('#KEYWORD#', currentValue)
						.replace('#TITLE#', suggestion.value)
						.replace('#SRC#', suggestion.image);
				} else if (suggestion.type == 'sellers') {
					var sellersHtml = $sellersHtml;
					var value = suggestion.value;
					var regex = new RegExp('('+currentValue+')', 'gi');
					value = value.replace(regex, '<strong>$1</strong>');
					
					return sellersHtml
						.replace('#TITLE#', value)
						.replace('#SRC#', suggestion.image);
				} 
			},
			onSelect: function(suggestion) {
				if (suggestion.type == 'product') {
					window.location.href = suggestion.link;
				} else if (suggestion.type == 'seller') {
					" . ($seller_redirect == 1 ? "
					window.location.href = 'index.php?option=com_sellacious&view=search&i=$integration&q=' + suggestion.q + '&seller=' + suggestion.uid;
					" : ($seller_redirect == 2 ? "
					window.location.href = suggestion.link;
					" : "
					window.location.href = suggestion.slink;
					")) . "
				} else if (suggestion.type == 'sellers') {
				    " . ($sellers_redirect == 1 ? "
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
	<div class="search-category-actionbtn">
		<i class="search-icon fa fa-search"></i>
		<i id="search_close" class="remove-search fa fa-times"></i>
	</div>
	<div class="clearfix"></div>
	<div class="sella-category-searchwrapper">
		<div class="sella-searchbar">
			<form id="finder-for-category-search" action="index.php" method="get" class="sellacious-search">
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
		$(".search-category-actionbtn .search-icon").on('click', function () {
			$(".sella-category-searchwrapper").fadeIn(200);
			$(".search-category-actionbtn .remove-search").show();
			$(".search-category-actionbtn .search-icon").hide();
			$("#finder_overlay").focus();
		});

		$("#search_close").on('click', function () {
			$(".sella-category-searchwrapper").fadeOut(200);
			$(".search-category-actionbtn .remove-search").hide();
			$(".search-category-actionbtn .search-icon").show();
		});

		// press esc to hide search
		$(document).keyup(function (e) {
			if (e.keyCode == 27) {
				$(".sella-category-searchwrapper").fadeOut(200);
				$(".search-category-actionbtn .remove-search").fadeOut(200);
				$(".search-category-actionbtn .search-icon").delay(200).fadeIn(200);
			}
		});
	});
</script>
