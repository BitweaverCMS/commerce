{if $listProducts}
	<div class="header">
	{form action=$smarty.server.REQUEST_URI}
		<div class="floaticon">
		{tr}Sort by:{/tr}
			<select name="sort_mode" onChange="this.form.submit();">
				<option value="products_name_asc" {if $listInfo.sort_mode == 'products_name_asc'}selected="selected"{/if}>{tr}Product Name{/tr}</option>
				<option value="products_name_desc" {if $listInfo.sort_mode == 'products_name_desc'}selected="selected"{/if}>{tr}Product Name - desc{/tr}</option>
				<option value="products_price_asc" {if $listInfo.sort_mode == 'products_price_asc'}selected="selected"{/if}>{tr}Price - low to high{/tr}</option>
				<option value="products_price_desc" {if $listInfo.sort_mode == 'products_price_desc'}selected="selected"{/if}>{tr}Price - high to low{/tr}</option>
				<option value="products_model_asc" {if $listInfo.sort_mode == 'model_asc'}selected="selected"{/if}>{tr}Model{/tr}</option>
				<option value="products_date_added_desc" {if $listInfo.sort_mode == 'products_date_added_desc'}selected="selected"{/if}>{tr}Date Added - New to Old{/tr}</option>
				<option value="products_date_added_asc" {if $listInfo.sort_mode == 'products_date_added_asc'}selected="selected"{/if}>{tr}Date Added - Old to New{/tr}</option>
			</select>
		</div>
	{/form}
	</div>

	{include file="bitpackage:bitcommerce/commerce_pagination.tpl"}

	<form name="multiple_products_cart_quantity" action="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?action=multiple_products_add_product" method="post" enctype="multipart/form-data">
		<div class="body">
			{if $smarty.const.PRODUCT_LISTING_MULTIPLE_ADD_TO_CART and $runNormal == 'true'}
				{formhelp}To purchase multiple products at once, enter the quantity for each product you would like to purchase, and click "{$smarty.const.SUBMIT_BUTTON_ADD_PRODUCTS_TO_CART}"

				<input type="submit" value="{$smarty.const.SUBMIT_BUTTON_ADD_PRODUCTS_TO_CART}" id="submit1" name="submit1" Class="SubmitBtn">
			{/if}

{*
			{if $smarty.const.PREV_NEXT_BAR_LOCATION == '1' || $smarty.constPREV_NEXT_BAR_LOCATION == '3'}
			no paged display for now - spiderr
			  <tr>
				<td class="pageresults"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></td>
				<td class="pageresults" align="right"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, zen_get_all_get_params(array('page', 'info', 'x', 'y', 'main_page'))); ?></td>
			  </tr>
			{/if}
*}

			<ul class="products clear data">
			{foreach from=$listProducts key=productsId item=prod}
				<li class="item {$prod.content_type_guid} {if !$prod.products_status}unavailable{else}{cycle values='odd,even'}{/if}">
					<div class="image floatleft">
					{if $prod.display_url}
						<a href="{$prod.display_url}">
							<img class="thumb" src="{$prod.products_image_url}" alt="{$prod.title}" title="{$prod.title}" />
						</a>
					{/if}
					</div>

					<div class="floaticon">
						{if $smarty.const.PRODUCT_LISTING_MULTIPLE_ADD_TO_CART && $prod.products_qty_box_status != '0' && $prod.products_quantity_order_max != '1'}
							{tr}Purchase Multiple:{/tr} <input type="text" name="products_id[{$prod.products_id}]" value=0 size="4"><br/>
						{/if}

						{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='list_sort' serviceHash=$prod}
					</div>

					<div class="details">
						<h2><a href="{$prod.display_url}">{$prod.products_name}</a></h2>
						{if !$prod.products_status}<em>{tr}Not Publicly Available{/tr}</em><br/>{/if}

						<div class="creator">
						{if $prod.manufacturers_name}
							<a href="{$smarty.const.BITCOMMERCE_PKG_URL}?manufacturers_id={$prod.manufacturers_id}">{$prod.manufacturers_name}</a><br/>
						{elseif !$gQueryUser}
							{tr}By{/tr} {displayname hash=$prod} <span class="date">{$prod.created|bit_short_date}</span>
						{/if}
						</div>
						{if $prod.products_model}
							<div class="model">{$prod.products_model}</div>
						{/if}

						<div class="price">{if $prod.products_priced_by_attribute}{tr}From{/tr} {/if}<a href="{$prod.display_url}">{$prod.display_price}</a></div>

						{if $smarty.const.PRODUCT_LIST_DESCRIPTION}
							<p>{$listProducts.products_description|truncate:PRODUCT_LIST_DESCRIPTION}</p>
						{/if}
					</div>

					<div class="clear"></div>
				</li>

{*
			if (isset($_GET['manufacturers_id'])) {
				$lc_text = '<a href="' . zen_href_link(zen_get_info_page($listing->fields['products_id']), 'manufacturers_id=' . $_GET['manufacturers_id'] . '&products_id=' . $listing->fields['products_id']) . '">' . $listing->fields['products_name'] . '</a>';
			} else {
				$lc_text = '<a href="' . zen_href_link(zen_get_info_page($listing->fields['products_id']), ($cPath ? 'cPath=' . $cPath . '&' : '') . 'products_id=' . $listing->fields['products_id']) . '">' . $listing->fields['products_name'] . '</a>';
			}
			// add description

			break;

// more info in place of buy now
			$lc_button = '';
			if (zen_has_product_attributes($listing->fields['products_id']) or PRODUCT_LIST_PRICE_BUY_NOW == '0') {
			  $lc_button = '<a href="' . zen_href_link(zen_get_info_page($listing->fields['products_id']), 'products_id=' . $listing->fields['products_id']) . '">' . MORE_INFO_TEXT . '</a>';
			} else {
			  if (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART != 0) {
				$how_many++;
				$lc_button = TEXT_PRODUCT_LISTING_MULTIPLE_ADD_TO_CART . "<input type=\"text\" name=\"products_id[" . $listing->fields['products_id'] . "]\" value=0 size=\"4\">";
			  } else {
				$lc_button = '<a href="' . zen_href_link($_GET['main_page'], zen_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $listing->fields['products_id']) . '">' . zen_image_button(BUTTON_IMAGE_BUY_NOW, BUTTON_BUY_NOW_ALT) . '</a>&nbsp;';
			  }
			}
			$the_button = $lc_button;
			$products_link = '<a href="' . zen_href_link(zen_get_info_page($listing->fields['products_id']), 'products_id=' . $listing->fields['products_id']) . '">' . MORE_INFO_TEXT . '</a>';
			$lc_text .= '<br />' . zen_get_buy_now_button($listing->fields['products_id'], $the_button, $products_link) . '<br />' . zen_get_products_quantity_min_units_display($listing->fields['products_id']);

			break;
		  case 'PRODUCT_LIST_IMAGE':
			$lc_align = 'center';
			if (isset($_GET['manufacturers_id'])) {
			  $lc_text = '<a href="' . zen_href_link(zen_get_info_page($listing->fields['products_id']), 'manufacturers_id=' . $_GET['manufacturers_id'] . '&products_id=' . $listing->fields['products_id']) . '">' . zen_image(  CommerceProduct::getImageUrl( $listing->fields, 'avatar' ), $listing->fields['products_name'] ) . '</a>';
			} else {
			  $lc_text = '&nbsp;<a href="' . zen_href_link(zen_get_info_page($listing->fields['products_id']), ($cPath ? 'cPath=' . $cPath . '&' : '') . 'products_id=' . $listing->fields['products_id']) . '">' . zen_image( CommerceProduct::getImageUrl( $listing->fields, 'avatar' ), $listing->fields['products_name'] ) . '</a>&nbsp;';
			}
			break;
		}
*}
			{/foreach}
			</ul>

			<div class="clear"></div>
		</div>	<!-- end .body -->

	{include file="bitpackage:bitcommerce/commerce_pagination.tpl"}

{*
		{if $smarty.const.PREV_NEXT_BAR_LOCATION == '2' || $smarty.const.PREV_NEXT_BAR_LOCATION == '3'}
			<tr>
				<td class="pageresults"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></td>
				<td class="pageresults" align="right"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, zen_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></td>
			</tr>
		{/if}
*}

		{if $runNormal == 'true' && $smarty.const.PRODUCT_LISTING_MULTIPLE_ADD_TO_CART and $smarty.const.PRODUCT_LISTING_MULTIPLE_ADD_TO_CART >= 2 }
			<input type="submit" align="absmiddle" value="{$smarty.const.SUBMIT_BUTTON_ADD_PRODUCTS_TO_CART}" id="submit1" name="submit1" Class="SubmitBtn">
		{/if}
	</form>
{else}
	<ul>
		<li class="item norecords">
			{tr}No products found.{/tr}
		</li>
	</ul>
{/if}
