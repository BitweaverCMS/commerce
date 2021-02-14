<div class="floaticon">
{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='icon'}
{if $smarty.const.SHOW_PRODUCT_INFO_TELL_A_FRIEND == '1'}
	<a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page={$smarty.const.FILENAME_TELL_A_FRIEND}&amp;products_id={$gBitProduct->mProductsId}">{booticon ipackage="icons" iexplain="Tell a Friend" iname="icon-share-sign"}</a>
{/if}

{if $gBitUser->hasPermission('p_commerce_admin')}
		<a title="{tr}Options{/tr}" href="{$smarty.const.BITCOMMERCE_PKG_URL}/admin/products_options.php?products_id={$gBitProduct->getField('products_id')}">{booticon iname="icon-check" iexplain="Edit Product Options"}</a>
		<a title="{tr}Prices{/tr}" href="{$smarty.const.BITCOMMERCE_PKG_URL}/admin/products_price_manager.php?product_type={$gBitProduct->getField('products_type')}&current_category_id={$gBitProduct->getField('categories_id')}">{booticon  iname="icon-money" iexplain="Edit Product Prices"}</a>
		<a title="{tr}Edit{/tr}" href="{$smarty.const.BITCOMMERCE_PKG_URL}/admin/product.php?page=1&product_type={$gBitProduct->getField('products_type')}&cPath={$gBitProduct->getField('categories_id')}&products_id={$gBitProduct->getField('products_id')}&action=new_product">{booticon ipackage="bitcommerce" iname="icon-pencil" iexplain="Edit Product"}</a>
{/if}
</div>

<header class="page-header">
	<h1>{$gBitProduct->getTitle()}</h1>
</header>

<div class="row">
	{assign var=thumbUrl value=$gBitProduct->getImageUrl('large')}	
	{if $thumbUrl}
	<div class="col-sm-4">
		<a rel="nofollow" href="{$thumbUrl}" {if !$gBitSystem->isFeatureActive( 'site_fancy_zoom' )}target="_new"{/if} ><img src="{$thumbUrl}" class="img-responsive"  alt="{$gBitProduct->mInfo.products_name|escape:html}" id="productthumb"/></a>
	</div>
	{/if}
		
	<div class="col-sm-{if $thumbUrl}8{else}12{/if}">
		{form name='cart_quantity' action="`$smarty.const.BITCOMMERCE_PKG_URL`index.php?products_id=`$smarty.get.products_id`&amp;action=add_product" method='post' enctype='multipart/form-data'}

		<div class="row">
			{if $gBitProduct->getField('products_description')}
			<div class="col-md-8 col-sm-7 col-xs-12">
				<div class="content">
					{if $smarty.const.SHOW_PRODUCT_INFO_MODEL == '1' && $gBitProduct->getField('products_model')}
						<div class="form-group">
							{$gBitProduct->getField('products_model')}
						</div>
					{/if}

					{if $smarty.const.SHOW_PRODUCT_INFO_MANUFACTURER == '1' and $gBitProduct->getField('manufacturers_name')}
						<div class="form-group">
							{$gBitProduct->getField('manufacturers_name')}
						</div>
					{/if}

					{$gBitProduct->getField('products_description')}
				</div>
			</div>
			{/if}
			<div class="col-md-4 col-sm-5 col-xs-12">
				<div class="product-options well">
					{if $gBitProduct->getBasePrice() > 0}
					<div class="form-group">
						{assign var=displayPrice value=$gBitProduct->getDisplayPrice()}
						{if $displayPrice}
						<h2>
							{if $gBitProduct->getField('show_onetime_charges_description') && $gBitProduct->getField('show_onetime_charges_description') == 'true'}
								<div class="smallText">{$smarty.const.TEXT_ONETIME_CHARGE_SYMBOL}{$smarty.const.TEXT_ONETIME_CHARGE_DESCRIPTION}</div>
							{/if}
							{if $gBitProduct->hasAttributes() and $smarty.const.SHOW_PRODUCT_INFO_STARTING_AT == '1'}{tr}Starting at{/tr}:{/if}
							{$displayPrice}
							{if $gBitProduct->getCommissionUserDiscount() }
								(<span class="success">{tr}Retail Price{/tr} {$gBitProduct->getPrice('products')}</span>)
							{/if}
						</h2>
						{/if}
					</div>
					{/if}

				{if $smarty.const.SHOW_PRODUCT_INFO_WEIGHT == '1' && $gBitProduct->getField('products_weight')} 
					<div class="form-group">
						{$smarty.const.TEXT_PRODUCT_WEIGHT}{$gBitProduct->getField('products_weight')}{$smarty.const.TEXT_PRODUCT_WEIGHT_UNIT}
					</div>
				{/if}

				{include file='bitpackage:bitcommerce/product_options_inc.tpl'}

				{if $smarty.const.SHOW_PRODUCT_INFO_QUANTITY == '1' && !$gBitProduct->getField( 'products_virtual' )}
					<div class="form-group">
						{$gBitProduct->getField('products_quantity')} {$smarty.const.TEXT_PRODUCT_QUANTITY}
					</div>
				{/if}

				{if $gBitProduct->getField('products_discount_type')}
					<div class="form-group">
						{include_php file=$smarty.const.FILENAME_PRODUCTS_DISCOUNT_PRICES|zen_get_module_path}
					</div>
				{/if}

				{if $smarty.const.CUSTOMERS_APPROVAL == '3' and $smarty.const.TEXT_LOGIN_FOR_PRICE_BUTTON_REPLACE_SHOWROOM == ''}
					&nbsp;
				{else}
					<div class="form-group">
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
							<input class="input-mini form-control" type="number" name="cart_quantity" value="{$gBitProduct->getBuyNowQuantity()}"/> {$gBitProduct->getQuantityMinUnitsDisplay()}
						{/if}
					</div>
					<div class="form-group">
						<input type="submit" class="btn btn-lg btn-primary" name="{tr}{$smarty.request.sub|default:"Add to Cart"}{/tr}" value="{tr}{$smarty.request.sub|default:"Add to Cart"}{/tr}" />
					</div>
				{/if}
				</div>
				{if $smarty.const.PRODUCT_INFO_PREVIOUS_NEXT == '2' || $smarty.const.PRODUCT_INFO_PREVIOUS_NEXT == '3'}
				<div class="form-group">
					{include_php file="`$smarty.const.BITCOMMERCE_PKG_INCLUDE_PATH`templates/template_default/common/tpl_products_next_previous.php"}
				</div>
				{/if}

				{if $smarty.const.SHOW_PRODUCT_INFO_DATE_AVAILABLE == '1' && $gBitProduct->getField('products_date_available') > date('Y-m-d H:i:s')}
				<div class="form-group">
					<span class="warning">{tr}This product will be in stock on{/tr} {$gBitProduct->getField('products_date_available')|zen_date_long}</span>
				</div>
				{elseif $smarty.const.SHOW_PRODUCT_INFO_DATE_ADDED == '1'}
				<div class="form-group">
					{tr}This product was added to our catalog on{/tr} {$gBitProduct->getField('products_date_added')|zen_date_long}
				</div>
				{/if}

			</div>
		</div>
		{/form}
	</div>
</div>

{if $smarty.const.SHOW_PRODUCT_INFO_REVIEWS == '1' AND $gBitSystem->isFeatureActive( 'wiki_comments' )}
			{include file="bitpackage:liberty/comments.tpl"}
{/if}


