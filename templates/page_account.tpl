<div class="page-header">
	<h1>{tr}My Shopping Information{/tr}</h1>
</div>
{formfeedback warning=$accountMessage}

{include file="bitpackage:bitcommerce/center_orders_history.tpl"}

<div>
	<a class="btn btn-default" href="{'address_book'|zen_get_page_url}">{booticon iname="icon-home"} {tr}Address Book{/tr}</a></dt>
{if $smarty.const.SHOW_NEWSLETTER_UNSUBSCRIBE_LINK=='true'}
	<a class="btn btn-default" href="{'account_newsletters'|zen_get_page_url}">{booticon iname="icon-envelope-alt"} {tr}Newsletter Subscriptions{/tr}</a></dt>
{/if}
{if $smarty.const.CUSTOMERS_PRODUCTS_NOTIFICATION_STATUS == '1'}
	<a class="btn btn-default" href="{'account_notifications'|zen_get_page_url}">{booticon iname="icon-exclamation-sign"} {tr}Product Notifications{/tr}</a></dt>
{/if}
</div>

{if $gvBalance} 
<h2>{tr}Gift Certificate Account{/tr}</h2>
<div class="form-group">
	{formlabel label="Gift Certificate Balance"}
	{forminput}
	  {$gvBalance}
	  <a href="{'gv_send'|zen_get_page_url}">{tr}Send Gift Certificate{/tr}</a>
	{/forminput}
</div>
{/if}
