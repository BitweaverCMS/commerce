<?php
// +--------------------------------------------------------------------+
// | Copyright (c) 2007 bitcommerce.org									|
// | http://www.bitcommerce.org											|
// +--------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license		|
// +--------------------------------------------------------------------+
/**
 * @version	$Header: /cvsroot/bitweaver/_bit_commerce/admin/interests.php,v 1.1 2010/02/18 20:49:25 spiderr Exp $
 *
 * Product class for handling all production manipulation
 *
 * @package	bitcommerce
 * @author	 spider <spider@steelsun.com>
 */


define('HEADING_TITLE', 'Order'.( (!empty( $_REQUEST['oID'] )) ? ' #'.$_REQUEST['oID'] : 's'));

require('includes/application_top.php');
require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceStatistics.php' );


if( !empty( $_REQUEST['action'] ) ) {
	switch( $_REQUEST['action'] ) {
		case 'save':
			$gBitCustomer->storeInterest( $_REQUEST );
			bit_redirect( $_SERVER['PHP_SELF'] );
			break;
		case 'delete':
			$gBitCustomer->expungeInterest( $_REQUEST['interests_id'] );
			bit_redirect( $_SERVER['PHP_SELF'] );
			break;
		case 'edit':
			$gBitSmarty->assign( 'editInterest', $gBitCustomer->getInterest( $_REQUEST['interests_id'] ) );
			break;
	}
} elseif( !empty( $_REQUEST['save_options'] ) ) {
	$gBitSystem->storeConfig( 'commerce_register_interests', !empty( $_REQUEST['commerce_register_interests'] ) ? 'y' : NULL );
	bit_redirect( $_SERVER['PHP_SELF'] );
}

$gBitSmarty->assign_by_ref( 'interestsList', $gBitCustomer->getInterests() );
print $gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_interests.tpl' );

require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); 

?>

<!-- footer_eof //-->
<br />
</body>
</html>
<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>
