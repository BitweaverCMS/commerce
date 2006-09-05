<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>{$browserTitle} - {$gBitSystem->getConfig('site_title')}</title>

	<link rel="stylesheet" title="basic" type="text/css" href="/themes/styles/basic/basic.css" media="all" />

</head>
<body{if $gBitSystem->mOnload} onload="{foreach from=$gBitSystem->mOnload item=loadString}{$loadString}{/foreach}"{/if}>

<!-- body_text //-->
{$smarty.const.STORE_NAME_ADDRESS|nl2br}
<table>
  <tr>
	<td valign="top" style="width:50%">
{*		<h2>{tr}Sold To{/tr}:</h2>
		{$gBitOrder->getFormattedAddress('billing')}
		{$gBitOrder->customer.telephone}
		<br/><a href="mailto:{$gBitOrder->customer.email_address}">{$gBitOrder->customer.email_address}</a>
*}
	</td>
	<td valign="top" style="width:50%">
		<h2>{tr}Ship To{/tr}:</h2>
		{$gBitOrder->getFormattedAddress('delivery')}
	</td>
  </tr>
</table>

<div class="row">
	{formlabel label="Order #"}
	{forminput}
		{$gBitOrder->mOrdersId}
	{/forminput}
</div>

<div class="row">
	{formlabel label="Date Ordered"}
	{forminput}
		{$gBitOrder->info.date_purchased|bit_long_date}
	{/forminput}
</div>

<div class="row">
	{formlabel label="Products"}
	{forminput}
<table class="data" style="border:0">
{section loop=$gBitOrder->products name=ix}
<tr>
	{cycle assign="oddeven" values="even,odd"}
	<td class="item {$oddeven}" valign="top" align="right" width="48"><img src="{$gBitProduct->getImageUrl($gBitOrder->products[ix].products_id,'icon')}" />
	<td class="item {$oddeven}" valign="top" align="right">{$gBitOrder->products[ix].quantity}&nbsp;x</td>
	<td class="item {$oddeven}" valign="top" width="90%">
		{$gBitOrder->products[ix].name} [ {$gBitOrder->products[ix].model} ]
		{if !empty($gBitOrder->products[ix].attributes)}
			{section loop=$gBitOrder->products[ix].attributes name=ax}
				<br><nobr><small>&nbsp;<i>{$gBitOrder->products[ix].attributes[ax].option}: {$gBitOrder->products[ix].attributes[ax].value}</i></small></nobr>
			{/section}
		{/if}
	</td>
</tr>
{/section}
</table>
	{/forminput}
</div>

</body>
</html>

