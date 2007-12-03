{literal}
<script language="javascript" type="text/javascript"><!--
function popupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=280,screenX=150,screenY=150,top=150,left=150')
}
function popupWindowPrice(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=600,height=400,screenX=150,screenY=150,top=150,left=150')
}
--></script>
{/literal}

<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td>
		<a rel="nofollow" href="javascript:popupWindow('{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=popup_image&amp;products_id={$gBitProduct->mProductsId}&amp;style=basic')"><img class="thumb" src="{$gBitProduct->getImageUrl(0,'medium')}" alt="{$gBitProduct->mInfo.products_name|escape:html}" id="productthumb" /></a>

{if $smarty.const.SHOW_PRODUCT_INFO_REVIEWS == '1' AND $gBitSystem->isFeatureActive( 'wiki_comments' )}
	<div class="row">
		{include file="bitpackage:liberty/comments.tpl"}
	</div>
{/if}

    </td>
    <td>

{form name='cart_quantity' action="`$smarty.const.BITCOMMERCE_PKG_URL`index.php?products_id=`$smarty.get.products_id`&amp;action=add_product" method='post' enctype="multipart/form-data"'}



<div class="floaticon">
{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='icon'}
{if $smarty.const.SHOW_PRODUCT_INFO_TELL_A_FRIEND == '1'}
	<a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page={$smarty.const.FILENAME_TELL_A_FRIEND}&amp;products_id={$gBitProduct->mProductsId}">{biticon ipackage="icons" iexplain="Tell a Friend" iname="mail-reply-all"}</a>
{/if}

{if $gBitProduct->hasEditPermission()}
		<a title="{tr}Edit{/tr}" href="{$smarty.const.PRODUCTS_PKG_URL}edit.php?products_id={$gBitProduct->getField('products_id')}">{biticon ipackage="icons" iname="accessories-text-editor" iexplain="Edit Product"}</a>
{/if}
</div>

<div class="header">
		<h1>{$gBitProduct->getTitle()}</h1>
		{if $gBitProduct->getField('user_id')!=$smarty.const.ROOT_USER_ID}{tr}By{/tr} {displayname hash=$gBitProduct->mInfo}{/if}
</div>


<div class="cartBox">
	<div class="row">
		<h2>
			{if $gBitProduct->getField('show_onetime_charges_description') && $gBitProduct->getField('show_onetime_charges_description') == 'true'}
				<div class="smallText">{$smarty.const.TEXT_ONETIME_CHARGE_SYMBOL}{$smarty.const.TEXT_ONETIME_CHARGE_DESCRIPTION}</div>
			{/if}
			{if $gBitProduct->hasAttributes() and $smarty.const.SHOW_PRODUCT_INFO_STARTING_AT == '1'}{tr}Starting at{/tr}:{/if}
			{$gBitProduct->getDisplayPrice()}
			{if $gBitProduct->getCommissionDiscount() }
				(<span class="success">{tr}Retail Price{/tr} {$gBitProduct->getPrice('products')}</span>)
			{/if}
		</h2>


	</div>
{if $smarty.const.SHOW_PRODUCT_INFO_MODEL == '1' && $gBitProduct->getField('products_model')}
	<div class="row">
		{$gBitProduct->getField('products_model')}
	</div>
{/if}

{if $smarty.const.SHOW_PRODUCT_INFO_WEIGHT == '1' && $gBitProduct->getField('products_weight')} 
	<div class="row">
	    {$smarty.const.TEXT_PRODUCT_WEIGHT}{$gBitProduct->getField('products_weight')}{$smarty.const.TEXT_PRODUCT_WEIGHT_UNIT}
	</div>
{/if}

{include file='bitpackage:bitcommerce/product_options_inc.tpl'}

{if $smarty.const.SHOW_PRODUCT_INFO_QUANTITY == '1' && !$gBitProduct->getField( 'products_virtual' )}
	<div class="row">
    	{$gBitProduct->getField('products_quantity')} {$smarty.const.TEXT_PRODUCT_QUANTITY}
	</div>
{/if}

{if $smarty.const.SHOW_PRODUCT_INFO_MANUFACTURER == '1' and $gBitProduct->getField('manufacturers_name')}
	<div class="row">
    	{$gBitProduct->getField('manufacturers_name')}
	</div>
{/if}

{if $smarty.const.CUSTOMERS_APPROVAL == '3' and $smarty.const.TEXT_LOGIN_FOR_PRICE_BUTTON_REPLACE_SHOWROOM == ''}
	&nbsp;
{else}
	<div class="row">
			{assign var="qtyInCart" value=$gBitProduct->quantityInCart()}
            {if $smarty.const.SHOW_PRODUCT_INFO_IN_CART_QTY == '1' && $qtyInCart}
				{tr}Quantity in Cart{/tr}: {$qtyInCart}<br/>
			{/if}
            
            	<input type="hidden" name="products_id" value="{$gBitProduct->mProductsId}" />
            {if !$gBitProduct->getField('products_qty_box_status') or $gBitProduct->getField('products_quantity_order_max') == '1'}
            	<input type="hidden" name="cart_quantity" value="1" />
            {else}
            	{$smarty.const.PRODUCTS_ORDER_QTY_TEXT} <input type="text" name="cart_quantity" value="{$gBitProduct->mProductsId|zen_get_buy_now_qty}" maxlength="6" size="4" /> {$gBitProduct->mProductsId|zen_get_products_quantity_min_units_display}
            {/if}
	</div>
	<div class="row">
			<input type="submit" class="button" name="{tr}{$smarty.request.sub|default:"Add to Cart"}{/tr}" value="{tr}{$smarty.request.sub|default:"Add to Cart"}{/tr}" />
	</div>
{/if}

{if $gBitProduct->getField('products_discount_type')}
<div class="row">';
    {* include_php file="`$smarty.const.DIR_FS_MODULES``$smarty.const.FILENAME_PRODUCTS_DISCOUNT_PRICES|zen_get_module_directory`" *}
</div>
{/if}
</div>



	<div class="content">
		{$gBitProduct->getField('products_description')}
	</div>
{*
    </td>
  </tr>
<?php require(DIR_FS_PAGES . $current_page_base . '/main_template_vars_images_additional.php'); ?>
<?php if (PRODUCT_INFO_PREVIOUS_NEXT == '2' or PRODUCT_INFO_PREVIOUS_NEXT == '3') { ?>
  <tr>
    <td colspan="2" align="center">
      <?php require($template->get_template_dir('/tpl_products_next_previous.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_products_next_previous.php'); ?>
    </td>
  </tr>
<?php } ?>
<?php
  if ($products_date_available > date('Y-m-d H:i:s')) {
    if (SHOW_PRODUCT_INFO_DATE_AVAILABLE == '1') {
?>
  <tr>
    <td colspan="2" align="center" class="smallText"><?php echo sprintf(TEXT_DATE_AVAILABLE, zen_date_long($products_date_available)); ?></td>
  </tr>
<?php
    }
  } else {
    if (SHOW_PRODUCT_INFO_DATE_ADDED == '1') {
?>
  <tr>
    <td colspan="2" align="center" class="smallText"><?php echo sprintf(TEXT_DATE_ADDED, zen_date_long($products_date_added)); ?></td>
  </tr>
<?php
    } // SHOW_PRODUCT_INFO_DATE_ADDED
  }
?>
<?php
  if (zen_not_null($products_url)) {
    if (SHOW_PRODUCT_INFO_URL == '1') {
?>
  <tr>
    <td class="main" align="center" colspan="2">
      <?php echo sprintf(TEXT_MORE_INFORMATION, zen_href_link(FILENAME_REDIRECT, 'action=url&goto=' . urlencode($products_url), 'NONSSL', true, false)); ?>
    </td>
  </tr>
 <?php
    } // SHOW_PRODUCT_INFO_URL
  }
?>
*}


</table>

{/form}

