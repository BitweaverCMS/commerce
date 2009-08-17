<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>{$browserTitle} - {$gBitSystem->getConfig('site_title')}</title>

	<link rel="stylesheet" title="basic" type="text/css" href="/themes/styles/basic/basic.css" media="all" />

</head>
<body{if $gBitSystem->mOnload} onload="{foreach from=$gBitSystem->mOnload item=loadString}{$loadString}{/foreach}"{/if}>

<p>&nbsp;</p>

<div class="row">
	{formlabel label="Return Address"}
	{forminput}
		<div style="font-size:larger;">{$smarty.const.STORE_NAME_ADDRESS|nl2br}</div>
	{/forminput}
</div>

<table>
  <tr>
	<td valign="top" style="width:50%">

<div class="row">
	{formlabel label="Order #"}
	{forminput}
		<div style="font-size:large">{$gBitOrder->mOrdersId}</div>
	{/forminput}
</div>

<div class="row">
	{formlabel label="Date Ordered"}
	{forminput}
		{$gBitOrder->info.date_purchased|bit_long_date}
	{/forminput}
</div>

{*		<h2>{tr}Sold To{/tr}:</h2>
		{$gBitOrder->getFormattedAddress('billing')}
		{$gBitOrder->customer.telephone}
		<br/><a href="mailto:{$gBitOrder->customer.email_address}">{$gBitOrder->customer.email_address}</a>
*}
	</td>
	<td valign="top" style="width:50%;">
		<div style="border:1px solid #ccc; padding: 10px;"> 
			<h2>{tr}Ship To{/tr}:</h2>
			<div style="font-size:large;">{$gBitOrder->getFormattedAddress('delivery')}</div>
		</div>
	</td>
  </tr>
</table>

<p>&nbsp;</p>

<div class="row">
	{formlabel label="Products"}
	{forminput}
<table class="data" style="border:0">
{foreach from=$gBitOrder->contents key=opid item=ordersProduct}
<tr>
	{cycle assign="oddeven" values="even,odd"}
	<td class="item" valign="top" align="right" width="48"><img src="{$gBitProduct->getImageUrl($ordersProduct.products_id,'icon')}" />
	<td class="item" valign="top" align="right">{$ordersProduct.quantity}&nbsp;x</td>
	<td class="item" valign="top" width="90%">
		{$ordersProduct.name} [ {$ordersProduct.model} ]
		{if !empty($ordersProduct.attributes)}
			{section loop=$ordersProduct.attributes name=ax}
				<br><nobr><small>&nbsp;<i>{$ordersProduct.attributes[ax].option}: {$ordersProduct.attributes[ax].value}</i></small></nobr>
			{/section}
		{/if}
	</td>
</tr>
{/foreach}
</table>
	{/forminput}
</div>


</body>
</html>

