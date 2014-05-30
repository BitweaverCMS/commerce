<div class="floaticon">
{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='icon'}
{if $smarty.const.SHOW_PRODUCT_INFO_TELL_A_FRIEND == '1'}
	<a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page={$smarty.const.FILENAME_TELL_A_FRIEND}&amp;products_id={$gBitProduct->mProductsId}">{booticon ipackage="icons" iexplain="Tell a Friend" iname="icon-share-sign"}</a>
{/if}

{if $gBitUser->hasPermission('p_commerce_admin')}
		<a title="{tr}Edit{/tr}" href="{$smarty.const.BITCOMMERCE_PKG_URL}/admin/product.php?page=1&product_type={$gBitProduct->getField('products_type')}&cPath={$gBitProduct->getField('categories_id')}&pID={$gBitProduct->getField('products_id')}&action=new_product">{booticon ipackage="bitcommerce" iname="icon-pencil" iexplain="Edit Product"}</a>
		<a title="{tr}Options{/tr}" href="{$smarty.const.BITCOMMERCE_PKG_URL}/admin/products_options.php?products_id={$gBitProduct->getField('products_id')}">{booticon iname="icon-cogs" iexplain="Edit Product Options"}</a>
		<a title="{tr}Prices{/tr}" href="{$smarty.const.BITCOMMERCE_PKG_URL}/admin/products_price_manager.php?product_type={$gBitProduct->getField('products_type')}&current_category_id={$gBitProduct->getField('categories_id')}">{booticon  iname="icon-money" iexplain="Edit Product Prices"}</a>
{/if}
</div>

		<header class="page-header">
			<h1>{$gBitProduct->getTitle()}</h1>
		</header>

<div class="row">
	{if $gBitProduct->getImageUrl(0,'medium')}	
	<div class="col-md-4">
		<a rel="nofollow" href="{$gBitProduct->getImageUrl('large')}" {if !$gBitSystem->isFeatureActive( 'site_fancy_zoom' )}target="_new"{/if} >
		<img class="thumb" src="{$gBitProduct->getImageUrl('medium')}" alt="{$gBitProduct->mInfo.products_name|escape:html}" id="productthumb" /></a>
	</div>
	{/if}
		
	<div class="col-md-8">
		{form name='cart_quantity' action="`$smarty.const.BITCOMMERCE_PKG_URL`index.php?products_id=`$smarty.get.products_id`&amp;action=add_product" method='post' enctype='multipart/form-data'}

		<div class="row">
			{if $gBitProduct->getField('products_description')}
			<div class="col-md-7">
				<div class="content">
					{if $smarty.const.SHOW_PRODUCT_INFO_MODEL == '1' && $gBitProduct->getField('products_model')}
						<div class="control-group">
							{$gBitProduct->getField('products_model')}
						</div>
					{/if}

					{if $smarty.const.SHOW_PRODUCT_INFO_MANUFACTURER == '1' and $gBitProduct->getField('manufacturers_name')}
						<div class="control-group">
							{$gBitProduct->getField('manufacturers_name')}
						</div>
					{/if}

					{$gBitProduct->getField('products_description')}
				</div>
			</div>
			{/if}
			<div class="col-md-5">
				<div class="box addtocart">
					{if $gBitProduct->getBasePrice() > 0}
					<div class="control-group">
						<h2>
							{if $gBitProduct->getField('show_onetime_charges_description') && $gBitProduct->getField('show_onetime_charges_description') == 'true'}
								<div class="smallText">{$smarty.const.TEXT_ONETIME_CHARGE_SYMBOL}{$smarty.const.TEXT_ONETIME_CHARGE_DESCRIPTION}</div>
							{/if}
							{if $gBitProduct->hasAttributes() and $smarty.const.SHOW_PRODUCT_INFO_STARTING_AT == '1'}{tr}Starting at{/tr}:{/if}
							{$gBitProduct->getDisplayPrice()}
							{if $gBitProduct->getCommissionUserDiscount() }
								(<span class="success">{tr}Retail Price{/tr} {$gBitProduct->getPrice('products')}</span>)
							{/if}
						</h2>
					</div>
					{/if}

				{if $smarty.const.SHOW_PRODUCT_INFO_WEIGHT == '1' && $gBitProduct->getField('products_weight')} 
					<div class="control-group">
						{$smarty.const.TEXT_PRODUCT_WEIGHT}{$gBitProduct->getField('products_weight')}{$smarty.const.TEXT_PRODUCT_WEIGHT_UNIT}
					</div>
				{/if}

				{include file='bitpackage:bitcommerce/product_options_inc.tpl'}

				{if $smarty.const.SHOW_PRODUCT_INFO_QUANTITY == '1' && !$gBitProduct->getField( 'products_virtual' )}
					<div class="control-group">
						{$gBitProduct->getField('products_quantity')} {$smarty.const.TEXT_PRODUCT_QUANTITY}
					</div>
				{/if}

				{if $gBitProduct->getField('products_discount_type')}
				<div class="control-group">
					{assign var="modDir" value=$smarty.const.FILENAME_PRODUCTS_DISCOUNT_PRICES|zen_get_module_directory}
					{include_php file="`$smarty.const.DIR_FS_MODULES``$modDir`"}
				</div>
				{/if}

				{if $smarty.const.CUSTOMERS_APPROVAL == '3' and $smarty.const.TEXT_LOGIN_FOR_PRICE_BUTTON_REPLACE_SHOWROOM == ''}
					&nbsp;
				{else}
					<div class="control-group">
						{assign var="qtyInCart" value=$gBitProduct->quantityInCart()}
						
							<input type="hidden" name="products_id" value="{$gBitProduct->mProductsId}" />
						{if !$gBitProduct->getField('products_qty_box_status') or $gBitProduct->getField('products_quantity_order_max') == '1'}
							<input type="hidden" name="cart_quantity" value="1" />
						{else}
							<label>{tr}Quantity:{/tr}
							{if $smarty.const.SHOW_PRODUCT_INFO_IN_CART_QTY == '1' && $qtyInCart}
								({$qtyInCart} {tr}In Cart{/tr})
							{/if}
							</label>
							<input class="input-mini" type="text" name="cart_quantity" value="{$gBitProduct->mProductsId|zen_get_buy_now_qty}"/> {$gBitProduct->mProductsId|zen_get_products_quantity_min_units_display}
						{/if}
					</div>
					<div class="control-group">
						<input type="submit" class="btn btn-lg btn-primary" name="{tr}{$smarty.request.sub|default:"Add to Cart"}{/tr}" value="{tr}{$smarty.request.sub|default:"Add to Cart"}{/tr}" />
					</div>
				{/if}
			</div>
		</div>


		{if $smarty.const.PRODUCT_INFO_PREVIOUS_NEXT == '2' || $smarty.const.PRODUCT_INFO_PREVIOUS_NEXT == '3'}
		<div class="control-group">
			{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`includes/templates/template_default/common/tpl_products_next_previous.php"}
		</div>
		{/if}

		{if $smarty.const.SHOW_PRODUCT_INFO_DATE_AVAILABLE == '1' && $gBitProduct->getField('products_date_available') > date('Y-m-d H:i:s')}
		<div class="control-group">
			<span class="warning">{tr}This product will be in stock on{/tr} {$gBitProduct->getField('products_date_available')|zen_date_long}</span>
		</div>
		{elseif $smarty.const.SHOW_PRODUCT_INFO_DATE_ADDED == '1'}
		<div class="control-group">
			{tr}This product was added to our catalog on{/tr} {$gBitProduct->getField('products_date_added')|zen_date_long}
		</div>
		{/if}

		{if $gBitProduct->getField('products_url') && $smarty.const.SHOW_PRODUCT_INFO_URL == '1'}
		<div class="control-group">
		{*  <?php echo sprintf(TEXT_MORE_INFORMATION, zen_href_link(FILENAME_REDIRECT, 'action=url&goto=' . urlencode($products_url), 'NONSSL', true, false)); ?> *}
		</div>
		{/if}

		{/form}
	</div>
</div>

<div class="row">
{if $smarty.const.SHOW_PRODUCT_INFO_REVIEWS == '1' AND $gBitSystem->isFeatureActive( 'wiki_comments' )}
	<div class="span-6">
		<div class="control-group">
			{include file="bitpackage:liberty/comments.tpl"}
		</div>
	</div>
{/if}
<div>


