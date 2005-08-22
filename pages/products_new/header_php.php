<?php
// $Id: header_php.php,v 1.2 2005/08/22 04:38:27 spiderr Exp $
//

if( !empty( $_REQUEST['user_id'] ) ) {
	$gQueryUser = new BitPermUser( $_REQUEST['user_id'] );
} else {
	$gQueryUser = &$gBitUser;
}


define('NAVBAR_TITLE', tra( 'Products by' ).' '.$gQueryUser->getDisplayName( FALSE ) );
define('HEADING_TITLE', tra( 'Products by' ).' '.$gQueryUser->getDisplayName( FALSE ) );

  require(DIR_WS_MODULES . 'require_languages.php');
  $breadcrumb->add(NAVBAR_TITLE);

$listHash['user_id'] = $gQueryUser->mUserId;
$listHash['thumbnail_size'] = 'small';

/* The cool bitweaver way will have to happen later... - spiderr */
$listHash['user_id'] = $gQueryUser->mUserId;
$userProducts = $gBitProduct->getList( $listHash );
$gBitSmarty->assign( 'listProducts', $userProducts );

$gBitSmarty->assign_by_ref( 'gQueryUser', $gQueryUser );

print $gBitSmarty->fetch( 'bitpackage:bitcommerce/list_products.tpl' );


?>
