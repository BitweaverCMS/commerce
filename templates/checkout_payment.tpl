<header class="page-header">
	<h1>{tr}Step 2 of 3 - Payment Information{/tr}</h1>
</header>

{form class="form-horizontal" name='checkout_payment' action="`$smarty.const.BITCOMMERCE_PKG_URL`index.php?main_page=checkout_confirmation" onsubmit="return check_form();" secure="y"}

{if $messageStack->size('checkout_payment')}
	{$messageStack->output('checkout_payment')}
{/if}
		
{if $smarty.const.DISPLAY_CONDITIONS_ON_CHECKOUT == 'true'}
	<tr>
		<td class="main" align="center" valign="top"><?php echo TABLE_HEADING_CONDITIONS; ?><br /></td>
		<td colspan="2" class="main" valign="top"><?php echo TEXT_CONDITIONS_DESCRIPTION . '<br /><br />' . zen_draw_checkbox_field('conditions', '1', false, 'id="conditions"') . '<label for="conditions">&nbsp;' . TEXT_CONDITIONS_CONFIRM . '</label>'; ?></td>
	</tr>
{/if}
<div class="row-fluid">
	<div class="span6">
	{if count($paymentSelection) > 1}
		{tr}Please select a payment method for this order.{/tr}
	{/if}
			{if $smarty.const.SHOW_ACCEPTED_CREDIT_CARDS != '0'}
				<div>
				{if $smarty.const.SHOW_ACCEPTED_CREDIT_CARDS == '1'}
					{$smarty.const.TEXT_ACCEPTED_CREDIT_CARDS} {1|zen_get_cc_enabled}
				{/if}
				{if $smart.const.SHOW_ACCEPTED_CREDIT_CARDS == '2'}
					{$smarty.const.TEXT_ACCEPTED_CREDIT_CARDS} {zen_get_cc_enabled('IMAGE_')}
				{/if}
				</div>
			{/if}

	{foreach from=$paymentSelection item=selection}
		<fieldset>
			<legend>
		{if count($paymentSelection) > 1}
			<input type="radio" name="payment" value="{$selection.id}" /> 
		{else}
			<input type="hidden" name="payment" value="{$selection.id}" />
		{/if}
				{$selection.module}
			</legend>
			{formfeedback error=$selection.error}
		{if $smarty.const.MODULE_ORDER_TOTAL_COD_STATUS == 'true' and $selection.id == 'cod'}
			<div class="alert">{tr}<strong>Note:</strong> COD fees may apply{/tr}</div>
		{/if}
		{if $selection.fields && is_array($selection.fields)}
			{foreach from=$selection.fields item=selectionField}
				<div class="control-group">
				<label class="control-label" for="{$selectionField.id}">{$selectionField.title}</label>
				<div class="controls">
					{$selectionField.field}
				</div>
				</div>
			{/foreach}
		{/if}
		</fieldset>
	{/foreach}

	{if count($creditSelection)}
		{foreach from=$creditSelection item=selection}
			<fieldset>
				<legend>
					{$selection.module|tra}
				</legend>
				{if !empty($selection.checkbox)}
					{$selection.checkbox}
				{/if}
				{if !empty($smarty.request.credit_class_error_code) && $smarty.request.credit_class_error_code == $selection.id}
					{formfeedback error=$smarty.request.credit_class_error}
				{/if}
				{foreach from=$selection.fields item=selectionField}
					<div class="control-group">
						<label class="control-label" for="{$selectionField.id}">{$selectionField.title}</label>
						<div class="controls">
							{$selectionField.field}
						</div>
					</div>
				{/foreach}
			</fieldset>
		{/foreach}
	{/if}

		<div class="control-group">
			<h3>{tr}Continue to Step 3{/tr}</h3>
			<p>{tr}- to confirm your order.{/tr} </p>
			<div class="control-group submit">
				<input type="submit" class="btn btn-primary" name="Continue" value="{tr}Continue{/tr}" onclick="submitFunction('<?=$gBitCustomer->getGiftBalance()?>','<?=$order->info['total']?>')"/>
			</div>
		</div>
	</div>
	<div class="span6">
		{legend legend="Billing Address"}
					{zen_address_label($smarty.session.customer_id, $smarty.session.billto, 1, ' ', '<br />')}
					{formhelp note="The billing address should match the address on your credit card statement."}
					<a class="btn" href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=checkout_payment_address">{tr}Change Address{/tr}</a>
		{/legend}
	
		<fieldset>
			<div class="control-group">
				{formlabel label="Order Comments" for=""}
				{forminput}
					<textarea name="comments" wrap="soft" class="width95p" rows="4">{$smarty.session.comments}</textarea>
				{/forminput}
			</div>
		</fieldset>

		{if $orderTotalModules}
					<table>{$orderTotalModules->output()}</table>
		{/if}
	</div>
</div>

{/form}
