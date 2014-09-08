{strip}
<div class="floaticon">{bithelp}</div>
<div class="edit bitcommerce">
		<div class="page-header">
			<h1>{tr}Send Gift Certificate{/tr}</h1>
		</div>
		<div class="body">

	{if !$gBitUser->isRegistered()}
		Please login.
	{else}

		<div class="form-group">
          {formlabel label="Current Available Balance:"}
          {forminput}{$gvBalance}{/forminput}
        </div>
{if $smarty.get.action == 'doneprocess'}
	<div class="form-group submit">
		{formfeedback success="Congratulations, your `$smarty.const.TEXT_GV_NAME` has successfully been sent"}
		{forminput}
			<form action="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=gv_send" method="post">
			<input type="submit" class="btn btn-default" name="" value="{tr}Send More Certificates{/tr}" />
			</form>
		{/forminput}
	</div>
{elseif $smarty.get.action == 'send' && !$feedback.error}
    <form action="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=gv_send" method="post">

		<div class="form-group">
			{formlabel label="To:"}
			{forminput}{$smarty.post.to_name} &lt;{$smarty.post.email}&gt;
			{/forminput}
		</div>
		<div class="form-group">
			{formlabel label="Amount:"}
			{forminput}{$gvAmount}{/forminput}
		</div>
		<div class="form-group">
			{formlabel label="Message Preview:"}
			{forminput}
				<p class="bitbox">
				{$mainMessage}
				{if $smarty.post.message}
					<br/>{$smarty.post.message|escape:html}
				{/if}
				<br/><br/>{$smarty.const.EMAIL_ADVISORY|replace:'-----':''}
				</p>
			{/forminput}
		</div>

		<input type="hidden" name="send_name" value="{$gBitUser->getDisplayName|escape:html}" />
		<input type="hidden" name="to_name" value="{$smarty.post.to_name}" />
		<input type="hidden" name="email" value="{$smarty.post.email}" />
		<input type="hidden" name="amount" value="{$smarty.post.amount}" />
		<input type="hidden" name="message" value="{$smarty.post.message|escape:html}" />

	<div class="form-group submit">
		<input type="submit" class="btn btn-default" name="action" value="{tr}Back{/tr}" />
        <input type="submit" class="btn btn-default" name="action" value="{tr}Process{/tr}" />
	</div>
    </form>
{elseif !$smarty.get.action || $feedback.error}

	<p>{tr}Please enter below the details of the Gift Certificate you wish to send. For more information, please see our <a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=gv_faq">Gift Certificate FAQ</a>.{/tr}</p>

    <form action="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=gv_send&action=send" method="post">
	<div class="form-group">
        {formlabel label="Recipients Name"}
        {forminput}<input type="text" name="to_name" value ="{$smarty.post.to_name|escape:html}" size="40" />{/forminput}
	</div>
	<div class="form-group">
        {formfeedback error=$feedback.error.error_email}
        {formlabel label="Recipients E-Mail Address"}
        {forminput}<input type="text" name="email" value ="{$smarty.post.email}" size="40" />{/forminput}
	</div>
	<div class="form-group">
		{formfeedback error=$feedback.error.error_amount}
        {formlabel label="Amount of Gift Certificate"}
       	{forminput}<input type="text" name="amount" value ="{$smarty.post.amount|default:$gvBalance}" />{/forminput}
	</div>
	<div class="form-group">
        {formlabel label="Message to Recipients"}
        {forminput}<textarea name='message' rows="15" cols="50" >{$smarty.post.message|escape:html}</textarea>{/forminput}
	</div>

	<div class="form-group submit">
        <input type="submit" class="btn btn-default" name="send_now" value="{tr}Send{/tr}" />
	</div>
	</form>
    <h4>{tr}This message is included with all emails sent from this site{/tr}:</h4>
    <div class="bitbox">{$smarty.const.EMAIL_ADVISORY|replace:'-----':''}</div>

  {/if}

	{/if}
		</div><!-- end .body -->
</div>
{/strip}
