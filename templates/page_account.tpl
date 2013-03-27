	<div class="page-header">
		<h1>{tr}My Shopping Information{/tr}</h1>
	</div>
	{formfeedback warning=$accountMessage}

	{include file="bitpackage:bitcommerce/center_orders_history.tpl"}

	<h2>{tr}Shopping Preferences{/tr}</h2>
	<dl>
		<dt>{biticon iname="address-book-new" ipackage="icons" ilabel="Address Book"}
			<a href="{$smarty.const.BITCOMMERCE_PKG_SSL_URI}index.php?main_page=address_book">{tr}View or change entries in my address book.{/tr}</a></dt>
	{if $smarty.const.SHOW_NEWSLETTER_UNSUBSCRIBE_LINK=='true'}
		<dt>{biticon iname="internet-mail" ipackage="icons" ilabel="Address Book"}
			<a href="{$smarty.const.BITCOMMERCE_PKG_SSL_URI}index.php?main_page=account">{tr}Subscribe or unsubscribe from newsletters.{/tr}</a></dt>
	{/if}
	{if $smarty.const.CUSTOMERS_PRODUCTS_NOTIFICATION_STATUS == '1'}
		<dt>{biticon iname="software-update-available" ipackage="icons" iexplain="Product Notifications"}
			<a href="{$smarty.const.BITCOMMERCE_PKG_SSL_URI}index.php?main_page=account">{tr}EMAIL_NOTIFICATIONS_PRODUCTS{/tr}</a></dt>
	{/if}
	</dl>

	{if $gvBalance} 
	<h2>{tr}Gift Certificate Account{/tr}</h2>
	<div class="row">
		{formlabel label="Gift Certificate Balance"}
		{forminput}
          {$gvBalance}
          <a href="{$smarty.const.BITCOMMERCE_PKG_SSL_URI}index.php?main_page=gv_send">{tr}Send Gift Certificate{/tr}</a>
		{/forminput}
	</div>
  {/if}
