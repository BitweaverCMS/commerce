<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/CalendarPopup.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/PopupWindow.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/AnchorPosition.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/date.js"></script>
<div id="caldiv" style="width:200px;position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></div>

{literal}
<script type="text/javascript">/* <![CDATA[ */
function editOption( pOrdPrdAttId ) {
	alert( pOrdPrdAttId );
	return false;
}
function deleteOption( pOrdPrdAttId, pTitle ) {
	return confirm( "Are you sure you want to delete the option '"+pTitle+"' from this order?" );
}

function getNewOption( pOrdPrdId ) {
	var myAjax = new Ajax.Updater(
		'neworderattr'+pOrdPrdId, 
		"{/literal}{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php{literal}", 
		{
			method: 'get', 
			parameters: 'new_option_id='+$F('neworderoption'+pOrdPrdId)+'&orders_products_id='+pOrdPrdId
		});
}

function saveNewOption( pForm ) {
	pForm.submit();
	window.location.reload();
}

/* ]]> */</script>
{/literal}


<h1 class="header">{$smarty.const.HEADING_TITLE}</h1>

<table>
<tr>
<td style="width:65%;" valign="top">

	{include file="bitpackage:bitcommerce/admin_order_header_inc.tpl"}

	<table>
      <tr>
		<td>
		<table class="data" border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr class="dataTableHeadingRow">
            <th colspan="2">{$smarty.const.TABLE_HEADING_PRODUCTS}</th>
            <th>{$smarty.const.TABLE_HEADING_PRODUCTS_MODEL}</th>
            <th align="right">{$smarty.const.TABLE_HEADING_TAX}</th>
            <th align="right">{$smarty.const.TABLE_HEADING_PRICE_EXCLUDING_TAX}</th>
            <th align="right">{$smarty.const.TABLE_HEADING_PRICE_INCLUDING_TAX}</th>
            <th align="right">{$smarty.const.TABLE_HEADING_TOTAL_EXCLUDING_TAX}</th>
            <th align="right">{$smarty.const.TABLE_HEADING_TOTAL_INCLUDING_TAX}</th>
          </tr>
{foreach from=$order->products item=ordersProduct}
<tr class="dataTableRow">
<td class="dataTableContent" valign="top" align="right">{$ordersProduct.quantity}&nbsp;x</td>
<td class="dataTableContent" valign="top"><a href="{$gBitProduct->getDisplayUrl($ordersProduct.products_id)}">{$ordersProduct.name|default:"Product `$ordersProduct.products_id`"}</a></td>
<td class="dataTableContent" valign="top">{$ordersProduct.model}</td>
<td class="dataTableContent" align="right" valign="top">{$ordersProduct.tax|zen_display_tax_value}%</td>
<td class="dataTableContent" align="right" valign="top">{$currencies->format($ordersProduct.final_price,true,$order->info.currency, $order->info.currency_value)}
	{if $ordersProduct.onetime_charges}<br />{$currencies->format($ordersProduct.onetime_charges, true, $order->info.currency, $order->info.currency_value)}{/if}
</td>
<td class="dataTableContent" align="right" valign="top">
	{assign var=finalPlusTax value=$ordersProduct.final_price|zen_add_tax:$ordersProduct.tax}
	{$currencies->format($finalPlusTax, true, $order->info.currency, $order->info.currency_value)}
	{if $ordersProduct.onetime_charges}<br />
		{assign var=onetimePlusTax value=$ordersProduct.onetime_charges|zen_add_tax:$ordersProduct.tax)}
		{$currencies->format($onetimePlusTax,true,$order->info.currency,$order->info.currency_value)}
	{/if}
</td>
<td class="dataTableContent" align="right" valign="top">
	{assign var=finalQty value=$ordersProduct.final_price*$ordersProduct.quantity}
	{$currencies->format($finalQty, true, $order->info.currency, $order->info.currency_value)}
	{if $ordersProduct.onetime_charges}<br />{$currencies->format($ordersProduct.onetime_charges, true, $order->info.currency, $order->info.currency_value)}{/if}
</td>
<td class="dataTableContent" align="right" valign="top">
	{assign var=finalQtyPlusTax value=$finalPlusTax*$ordersProduct.quantity} 
	{$currencies->format($finalQtyPlusTax,true,$order->info.currency,$order->info.currency_value)}
	{if $ordersProduct.onetime_charges}<br />{$currencies->format($onetimePlusTax,true,$order->info.currency,$order->info.currency_value)}{/if}
	{if $isForeignCurrency} ( {$currencies->format($finalQtyPlusTax,true,$smarty.const.DEFAULT_CURRENCY)} ){/if}
</td>
</tr>
<tr class="dataTableRow">
	<td><a href="product_history.php?products_id={$ordersProduct.products_id}"><img src="/themes/icon_styles/tango/small/appointment-new.png" title="Products History" alt="H" /></a></td>
	<td class="dataTableContent" colspan="7">
{if !empty( $ordersProduct.attributes )}
{section loop=$ordersProduct.attributes name=a}
		<div class="orders products attributes" id="{$ordersProduct.attributes[a].products_attributes_id}att">
			<nobr><em>&bull; {$ordersProduct.attributes[a].option}: {$ordersProduct.attributes[a].value}
				{assign var=sumAttrPrice value=$ordersProduct.attributes[a].final_price*$ordersProduct.quantity}
				{if $ordersProduct.attributes[a].price}({$ordersProduct.attributes[a].prefix}{$currencies->format($sumAttrPrice,true,$order->info.currency,$order->info.currency_value)}){/if}
				{if !empty($ordersProduct.attributes[a].product_attribute_is_free) && $ordersProduct.attributes[a].product_attribute_is_free == '1' and $ordersProduct.product_is_free == '1'}<span class="alert">{tr}FREE{/tr}</span>{/if}
			</em>
{*			<span onclick="editOption({$ordersProduct.attributes[a].orders_products_attributes_id}); return false;">{biticon ipackage="icons" iname="accessories-text-editor" iexplain="edit" iforce="icon"}</span> *}
			<a href="{$smarty.server.REQUEST_URI}&amp;del_ord_prod_att_id={$ordersProduct.attributes[a].orders_products_attributes_id}" onclick="return deleteOption({$ordersProduct.attributes[a].orders_products_attributes_id},'{$ordersProduct.attributes[a].option|escape:'quotes'|escape:'htmlall'}: {$ordersProduct.attributes[a].value|escape:'quotes'|escape:'htmlall'}');">{biticon ipackage="icons" iname="edit-delete" iexplain="edit" iforce="icon"}</a>
			</nobr>
		</div>
{/section}
{/if}
		<form method="post" action="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php">
			<input type="hidden" name="oID" value="{$smarty.request.oID}"/>
			<input type="hidden" name="action" value="save_new_option"/>
			<input type="hidden" name="orders_products_id" value="{$ordersProduct.orders_products_id}"/>
			{html_options name="newOrderOptionType" options=$optionsList id="neworderoption`$ordersProduct.orders_products_id`" onchange="getNewOption(`$ordersProduct.orders_products_id`);" selected="0"}
			<span id="neworderattr{$ordersProduct.orders_products_id}"></span>
		</form>
	</td>
</tr>

{/foreach}
          <tr>
            <td align="right" colspan="8"><table border="0" cellspacing="0" cellpadding="2">
{section loop=$order->totals name=t}
<tr>
	<td align="right" class="{$order->totals[t].class|str_replace:'_':'-'}-Text">{$order->totals[t].title}</td>
	<td align="right" class="{$order->totals[t].class|str_replace:'_':'-'}-Amount">{$order->totals[t].text}
		{if $isForeignCurrency}{$currencies->format($order->totals[t].orders_value,true,$smarty.const.DEFAULT_CURRENCY)}{/if}
	</td>
</tr>
{/section}

            </table></td>
          </tr>
        </table></td>
      </tr>

{php}
  // show downloads
  require(DIR_WS_MODULES . 'orders_download.php');
{/php}

	</table>

<div class="tabsystem tabpane">
{php}
	// scan fulfillment modules
	$fulfillDir = DIR_FS_MODULES . 'fulfillment/';
	if( is_readable( $fulfillDir ) && $fulfillHandle = opendir( $fulfillDir ) ) {
		while( $ffFile = readdir( $fulfillHandle ) ) {
			if( is_file( $fulfillDir.$ffFile.'/admin_order_inc.php' ) ) {
				include( $fulfillDir.$ffFile.'/admin_order_inc.php' );
			}
		}
	}
{/php}
<script type="text/javascript">
	setupAllTabs();
	var tabPane;
</script>
</div>
<div style="margin-top:15px;">
	<a href="{$smarty.const.BITCOMMERCE_PKG_ADMIN_URI}invoice.php?oID={$smarty.request.oID}" class="button">{tr}Invoice{/tr}</a>
	<a href="{$smarty.const.BITCOMMERCE_PKG_ADMIN_URI}packingslip.php?oID={$smarty.request.oID}" class="button">{tr}Packing Slip{/tr}</a>
	<a href="{$smarty.const.BITCOMMERCE_PKG_ADMIN_URI}orders.php?oID={$smarty.request.oID}&amp;action=delete" class="button">{tr}Delete{/tr}</a>
	<form method="post" action="{$smarty.server.BITCOMMERCE_PKG_ADMIN_URI}gv_mail.php">
		<input type="hidden" name="email_to" value="{$order->customer.email_address}" />
		<input type="hidden" name="oID" value="{$smarty.request.oID}" />
		<input type="submit" name="Send" value="Send Gift Certificate" />
	</form>
{form method="post" action="`$smarty.const.BITCOMMERCE_PKG_ADMIN_URI`orders.php?oID=`$smarty.request.oID`&amp;action=combine"}
	{tr}Combine with order{/tr}: <input type="text" name="combine_order_id" style="width:100px;" />
	<input type="submit" name="combine" value="{tr}Combine{/tr}" class="button" />
	<br/><input type=checkbox name="combine_notify" value="on" checked="checked">Notify Customer
	<br/><em class="small">Both orders must have status {$smarty.const.DEFAULT_ORDERS_STATUS_ID|zen_get_order_status_name}. This order will deleted.</em>
{/form}
</div>
	  
