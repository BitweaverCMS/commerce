{strip}
<div class="floaticon">{bithelp}</div>
<div class="edit bitcommerce">
		<div class="header">
			<h1>{tr}Redeem {$smarty.const.TEXT_GV_NAME}{/tr}</h1>
		</div>

		<div class="body">
		    <p>{tr}For more information regarding {$smarty.const.TEXT_GV_NAMES}, please see our {$smarty.const.TEXT_GV_NAME} FAQ.{/tr}</p>

		<div class="row">
			{formfeedback hash=$feedback}
		</div>

{if !$smarty.get.gv_no || $feedback.error}
    <form action="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=gv_redeem" method="post">
		<div class="row">
			{formlabel label="Enter `$smarty.const.TEXT_GV_NAME` Code"}
			{forminput}<input type="text" name="gv_no" size="10" maxlength="32" />{/forminput}
		</div>
		<div class="row">
			{forminput}<input type="submit" name="action" value="Submit" />{/forminput}
		</div>
	</form>
{/if}

	</div><!-- end .body -->
</div>
{/strip}
