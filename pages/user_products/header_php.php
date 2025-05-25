<?php
// $Id$
//

if( !empty( $_REQUEST['user_id'] ) && $_REQUEST['user_id'] != $gBitUser->mUserId ) {
	$gQueryUser = new BitPermUser( $_REQUEST['user_id'] );
	$gQueryUser->load();
} else {
	$gQueryUser = &$gBitUser;
}


define('NAVBAR_TITLE', tra( 'Products by' ).' '.$gQueryUser->getDisplayName() );
$gCommerceSystem->setHeadingTitle( tra( 'Products by' ).' '.$gQueryUser->getDisplayName() );

  require_once(DIR_FS_MODULES . 'require_languages.php');
  $breadcrumb->add(NAVBAR_TITLE);

if( $gQueryUser->mUserId == $gBitUser->mUserId ) {
	$listHash['all_status'] = TRUE;
}

$listHash['user_id'] = $gQueryUser->mUserId;
$listHash['thumbnail_size'] = 'small';

/* The cool bitweaver way will have to happen later... - spiderr */
$listHash['user_id'] = $gQueryUser->mUserId;
$userProducts = $gBitProduct->getList( $listHash );
$gBitProduct->invokeServices( 'content_list_function', $listHash );
$gBitSmarty->assign( 'listProducts', $userProducts );
$gBitSmarty->assign( 'listTitle', tra( 'Products by' ).' '.$gQueryUser->getDisplayName() );
$gBitSmarty->assign( 'listInfo', $listHash );

$gBitSmarty->assignByRef( 'gQueryUser', $gQueryUser );

print $gBitSmarty->fetch( 'bitpackage:bitcommerce/list_products.tpl' );


?>
