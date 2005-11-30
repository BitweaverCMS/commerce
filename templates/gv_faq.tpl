{strip}
<div class="floaticon">{bithelp}</div>
<div class="edit bitcommerce">
		<div class="header">
			<h1>{tr}Gift Certificates FAQ{/tr}</h1>
		</div>

		<div class="body">


	<ul style="clear:none">
		<li><a href="#balance">{$smarty.const.TEXT_GV_NAME} Balance</a></li>
		<li><a href="#purchasing">Purchasing {$smarty.const.TEXT_GV_NAMES}</a></li>
		<li><a href="#howtosend">How to send {$smarty.const.TEXT_GV_NAMES}</a></li>
		<li><a href="#buying">Buying with {$smarty.const.TEXT_GV_NAMES}</a></li>
		<li><a href="#redeeming">Redeeming {$smarty.const.TEXT_GV_NAMES}</a></li>
		<li><a href="#problems">{tr}When problems occur{/tr}</a></li>
	</ul>

	<h2><a href="#balance"></a>{$smarty.const.TEXT_GV_NAME} Balance</h2>
	<div class="row">
		{formlabel label="Gift Certificate Balance"}
		{forminput}{$gvBalance}{if $gvBalance} <a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=gv_send">{tr}Send {$smarty.const.TEXT_GV_NAME}{/tr}</a> {/if}{/forminput}
    </div>

{if $smarty.session.gv_id}
	<div class="row">
		{formlabel label="`$smarty.const.TEXT_GV_NAME` Redeemed"}
		{forminput}{$couponAmount}{/forminput}
    </div>
{/if}



	<h2><a name="purchasing"></a>Purchasing {$smarty.const.TEXT_GV_NAMES}</h2>
		<p>{$smarty.const.TEXT_GV_NAMES} are purchased just like any other item in our store. You can pay for them using the stores standard payment method(s).</p>
		<p>{tr}Once purchased the value of the {$smarty.const.TEXT_GV_NAME} will be added to your own personal {$smarty.const.TEXT_GV_NAME} Account. If you have funds in your {$smarty.const.TEXT_GV_NAME} Account, you will notice that the amount now shows in he Shopping Cart box, and also provides a link to a page where you can send the {$smarty.const.TEXT_GV_NAME} to some one via email.{/tr}</p>

	<h2><a name="howtosend"></a>How to send {$smarty.const.TEXT_GV_NAMES}</h2>
		<p>To send a {$smarty.const.TEXT_GV_NAME} you need to go to our <a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=gv_send">{tr}Send {$smarty.const.TEXT_GV_NAME}{/tr}</a>.</p>
		<p>When you send a {$smarty.const.TEXT_GV_NAME}, you need to specify the following:</p>
		<ul>
			<li>The name of the person you are sending the {$smarty.const.TEXT_GV_NAME} to.</li>
			<li>The email address of the person you are sending the {$smarty.const.TEXT_GV_NAME} to.</li>
			<li>The amount you want to send. (Note you don\'t have to send the full amount that
			is in your {$smarty.const.TEXT_GV_NAME} Account.)</li>
			<li>A short message which will apear in the email.</li>
		</ul>
		<p>Please ensure that you have entered all of the information correctly, although you will be given the opportunity to change this as much as you want before the email is actually sent.</p>

	<h2><a name="buying"></a>Buying with {$smarty.const.TEXT_GV_NAMES}</h2>
		<p>If you have funds in your {$smarty.const.TEXT_GV_NAME} Account, you can use those funds to purchase other items in out store. At the checkout stage, an extra box will appear. Enter the amount to apply from the funds in your {$smarty.const.TEXT_GV_NAME} Account.</p>
		<p>Please note, you will still have to select another payment method if there is not enough in your {$smarty.const.TEXT_GV_NAME} Account to cover the cost of your purchase. If you have more funds in your {$smarty.const.TEXT_GV_NAME} Account than the total cost of your purchase the balance will be left in your {$smarty.const.TEXT_GV_NAME} Account for the future.</p>

	<h2><a name="redeeming"></a>Redeeming {$smarty.const.TEXT_GV_NAMES}</h2>
		<p>If you receive a {$smarty.const.TEXT_GV_NAME} by email it will contain details of who sent you the {$smarty.const.TEXT_GV_NAME}, along with possibly a short message from them. The Email will also contain the {$smarty.const.TEXT_GV_NAME} {$smarty.const.TEXT_GV_REDEEM}. It is probably a good idea to print out this email for future reference. You can now redeem the  {$smarty.const.TEXT_GV_NAME} in two ways.</p>

		<ol>
	  		<li>By clicking on the link contained within the email for this express purpose. This will take you to the store's Redeem  {$smarty.const.TEXT_GV_NAME} page. You will the be requested to create an account, before the {$smarty.const.TEXT_GV_NAME} is validated and placed in your {$smarty.const.TEXT_GV_NAME} Account ready for you to spend it on whatever you want.</li>
			<li>During the checkout procces, on the same page that you select a payment method
there will be a box to enter a {$smarty.const.TEXT_GV_REDEEM} {$smarty.const.TEXT_GV_REDEEM}. Enter the {$smarty.const.TEXT_GV_REDEEM} here, and click the redeem button. The {$smarty.const.TEXT_GV_REDEEM} will be validated and added to your {$smarty.const.TEXT_GV_NAME} account. You Can then use the amount to purchase any item from our store</li>
		</ol>


	<h2><a name="problems">{tr}When problems occur{/tr}</a></h2>
		<p>For any queries regarding the {$smarty.const.TEXT_GV_NAME} System, please contact the store by email at  <a href="mailto:{$smarty.const.STORE_OWNER_EMAIL_ADDRESS}">{$smarty.const.STORE_OWNER_EMAIL_ADDRESS}</a>. Please make sure you give as much information as possible in the email. </p>
<hr>

		<a href="javascript:history.back()">{tr}Back{/tr}</a>


	</div><!-- end .body -->
</div>
{/strip}
