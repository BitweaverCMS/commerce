<header class="page-header">
	<h1>{tr}My Personal Address Book{/tr}</h1>
</header>

{$messageStack->output('address')}

<div class="row">
	<div class="col-md-6">
		{if count( $addresses )}
			<ul class="unstyled data">
				{section name=ix loop=$addresses}
				<li class="item">
					{if $addresses[ix].address_book_id == $sendToAddressId}
						{assign var=checked value=$addresses[ix].address_book_id}
						{assign var=class value="row selected"}
					{else}
						{assign var=class value="row"}
					{/if}
					<div class="floaticon">
						<a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=address_book&edit={$addresses[ix].address_book_id}"><i class="icon-pencil"></i></a> <a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=address_book&delete={$addresses[ix].address_book_id}"><i class="icon-trash"></i></a> 
					</div>
					{include file="bitpackage:bitcommerce/address_display_inc.tpl" address=$addresses[ix]}
				</li>
				{/section}
			</ul>
		{/if}
	</div>
	
	<div class="col-md-6">
		{form class="form-horizontal" action="`$smarty.const.BITCOMMERCE_PKG_URL`index.php?main_page=address_book"}
			{legend legend=$formTitle}
				{include file="bitpackage:bitcommerce/address_edit_inc.tpl" address=$editAddress}
				<div class="control-group clear">
					<input type="submit" class="btn btn-default" name="save_address" value="{tr}Save Address{/tr}"/>
				</div>
			{/legend}
		{/form}
	</div>
</div>

