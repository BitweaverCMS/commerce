<?php

$gBitSystem->verifyPermission('p_commerce_retailer');

$gBitSmarty->assign_by_ref( 'commissionList', $gBitCustomer->getCommissions() );


$gBitSmarty->display('bitpackage:bitcommerce/commissions.tpl');

?>
