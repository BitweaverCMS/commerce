----------------------------------------------------------------------------------------
{tr}Congratulations!{/tr} {tr}You have received a {$gCommerceSystem->getConfig('TEXT_GV_NAME')} worth{/tr} {$gvAmount}
----------------------------------------------------------------------------------------

{$gvSender} {tr}has sent you a {$gCommerceSystem->getConfig('TEXT_GV_NAME')}{/tr} {if $gvMessage}{tr}with a message saying:{/tr}{/if}

{$gvMessage}

----------------------------------------------------------------------------------------

{tr}To redeem this {$gCommerceSystem->getConfig('TEXT_GV_NAME')}, please click on the link below. In case you have problems please also write down your code.{/tr}


Redemption Code: {$gvCode}  

{tr}To redeem please click:{/tr} 

{$gvRedeemUrl}

{tr}If you have problems redeeming the {$gCommerceSystem->getConfig('TEXT_GV_NAME')} using the automated link above, you can also enter the {$gCommerceSystem->getConfig('TEXT_GV_NAME')} Redemption Code during the checkout process at our store.{/tr}


-----
{tr}IMPORTANT{/tr}: {tr}For your protection and to prevent malicious use, all emails sent via this web site are logged and the contents recorded and available to the store owner. If you feel that you have received this email in error, please send an email to{/tr} {$gCommerceSystem->getConfig('STORE_OWNER_EMAIL_ADDRESS')}

