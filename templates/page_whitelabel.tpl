<header class="page-header">
	<h1>{tr}Storefont Customization{/tr}</h1>
</header>

{$messageStack->output('whitelabel')}

{form name='cart_quantity' action=whitelabel|zen_href_link}

	{forminput label="checkbox"}
		<input type="checkbox" name="commerce_order_auto_process" value="y" id="auto_process" {if $gQueryUser->getPreference( 'commerce_order_auto_process' ) == 'y'}checked{/if}/>{booticon iname="fa-conveyor-belt-boxes"} {tr}Auto Process Orders{/tr}
		{formhelp note="Orders will go directly to fulfillment, and bypass any manual review by staff"}
	{/forminput}

		<div class="form-group">
			{formfeedback error=$errors.payment_message}
			{formlabel label="Custom Return Address" for=""}
				<div class="alert alert-warning"><ol>
					<li>{booticon iname="fa-location-question" iexplain="question"} Make sure this is a valid deliverable address. We will not verify it.</li>
					<li>{booticon iname="fa-umbrella" iexplain="Umbrella"} Make certain your return location is <strong>secure and dry</strong>. {booticon iname="fa-lock" iexplain="Lock"}</li>
					<li>{booticon iname="fa-box-open-full" iexplain="box"} We are not responsible for merchandise returned to custom addresses.</li>
				</ol></div>
			{forminput}
				<textarea name="oem_return_address" rows="6" class="form-control">{$gQueryUser->getPreference('oem_return_address')|escape}</textarea>
				{formhelp note="Enter complete address."}
			{/forminput}
		</div>
	<input type="submit" name="save_whitelabel" value="{tr}Save{/tr}" class="btn btn-primary"> <button class="btn btn-default" onclick="$('#form-edit-payment').hide();return false;">{tr}Cancel{/tr}</button>

{/form}
