<?php
require('includes/application_top.php');

$currencies = new currencies();

$oID = zen_db_prepare_input($_GET['oID']);

require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceOrder.php' );
$order = new order($oID);
$gBitSmarty->assign( 'order', $order );
?>
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
<?php

$gBitSmarty->display( 'bitpackage:bitcommerce/order_invoice.tpl' );

?>
</div>
</body>
</html>
<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>
