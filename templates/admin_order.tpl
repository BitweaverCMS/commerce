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
{section loop=$order->products name=p}
<tr class="dataTableRow">
<td class="dataTableContent" valign="top" align="right">{$order->products[p].quantity}&nbsp;x</td>
<td class="dataTableContent" valign="top"><a href="{$gBitProduct->getDisplayUrl($order->products[p].products_id)}">{$order->products[p].name}</a></td>
<td class="dataTableContent" valign="top">{$order->products[p].model}</td>
<td class="dataTableContent" align="right" valign="top">{$order->products[p].tax|zen_display_tax_value}%</td>
<td class="dataTableContent" align="right" valign="top">{$currencies->format($order->products[p].final_price,true,$order->info.currency, $order->info.currency_value)}
	{if $order->products[p].onetime_charges}<br />{$currencies->format($order->products[p].onetime_charges, true, $order->info.currency, $order->info.currency_value)}{/if}
</td>
<td class="dataTableContent" align="right" valign="top">
	{assign var=finalPlusTax value=$order->products[p].final_price|zen_add_tax:$order->products[p].tax}
	{$currencies->format($finalPlusTax, true, $order->info.currency, $order->info.currency_value)}
	{if $order->products[p].onetime_charges}<br />
		{assign var=onetimePlusTax value=$order->products[p].onetime_charges|zen_add_tax:$order->products[p].tax)}
		{$currencies->format($onetimePlusTax,true,$order->info.currency,$order->info.currency_value)}
	{/if}
</td>
<td class="dataTableContent" align="right" valign="top">
	{assign var=finalQty value=$order->products[p].final_price*$order->products[p].quantity}
	{$currencies->format($finalQty, true, $order->info.currency, $order->info.currency_value)}
	{if $order->products[p].onetime_charges}<br />{$currencies->format($order->products[p].onetime_charges, true, $order->info.currency, $order->info.currency_value)}{/if}
</td>
<td class="dataTableContent" align="right" valign="top">
	{assign var=finalQtyPlusTax value=$finalPlusTax*$order->products[p].quantity} 
	{$currencies->format($finalQtyPlusTax,true,$order->info.currency,$order->info.currency_value)}
	{if $order->products[p].onetime_charges}<br />{$currencies->format($onetimePlusTax,true,$order->info.currency,$order->info.currency_value)}{/if}
	{if $isForeignCurrency} ( {$currencies->format($finalQtyPlusTax,true,$smarty.const.DEFAULT_CURRENCY)} ){/if}
</td>
</tr>
{if !empty( $order->products[p].attributes )}
<tr class="dataTableRow">
	<td><a href="product_history.php?products_id={$order->products[p].products_id}"><img src="/themes/icon_styles/tango/small/appointment-new.png" title="Products History" alt="H" /></a></td>
	<td class="dataTableContent" colspan="7">
{section loop=$order->products[p].attributes name=a}
		<div class="orders products attributes" id="{$order->products[p].attributes[a].products_attributes_id}att">
			<nobr><em>&bull; {$order->products[p].attributes[a].option}: {$order->products[p].attributes[a].value}
				{assign var=sumAttrPrice value=$order->products[p].attributes[a].final_price*$order->products[p].quantity}
				{if $order->products[p].attributes[a].price}({$order->products[p].attributes[a].prefix}{$currencies->format($sumAttrPrice,true,$order->info.currency,$order->info.currency_value)}){/if}
				{if !empty($order->products[p].attributes[a].product_attribute_is_free) && $order->products[p].attributes[a].product_attribute_is_free == '1' and $order->products[p].product_is_free == '1'}<span class="alert">{tr}FREE{/tr}</span>{/if}
			</em>
{*			<span onclick="editOption({$order->products[p].attributes[a].orders_products_attributes_id}); return false;">{biticon ipackage="icons" iname="accessories-text-editor" iexplain="edit" iforce="icon"}</span> *}
			<a href="{$smarty.server.REQUEST_URI}&amp;del_ord_prod_att_id={$order->products[p].attributes[a].orders_products_attributes_id}" onclick="return deleteOption({$order->products[p].attributes[a].orders_products_attributes_id},'{$order->products[p].attributes[a].option|escape:'quotes'|escape:'htmlall'}: {$order->products[p].attributes[a].value|escape:'quotes'|escape:'htmlall'}');">{biticon ipackage="icons" iname="edit-delete" iexplain="edit" iforce="icon"}</a>
			</nobr>
		</div>
{/section}
		<form method="post" action="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php">
			<input type="hidden" name="oID" value="{$smarty.request.oID}"/>
			<input type="hidden" name="action" value="save_new_option"/>
			<input type="hidden" name="orders_products_id" value="{$order->products[p].orders_products_id}"/>
			{html_options name="newOrderOptionType" options=$optionsList id="neworderoption`$order->products[p].orders_products_id`" onchange="getNewOption(`$order->products[p].orders_products_id`);" selected="0"}
			<span id="neworderattr{$order->products[p].orders_products_id}"></span>
		</form>
	</td>
</tr>

{/if}
{/section}
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

