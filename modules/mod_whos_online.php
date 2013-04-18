<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id$
//
	global $gBitDb, $gBitProduct;

// test if box should display
  $show_whos_online= false;
  $show_whos_online= true;
	$user_total = 0;

  if( empty( $n_members ) ) {
  	$n_members = 0;
  }
  if( empty( $n_guests ) ) {
  	$n_guests = 0;
  }

// Set expiration time, default is 1200 secs (20 mins)
  $xx_mins_ago = (time() - 1200);

  $gBitDb->Execute("delete from " . TABLE_WHOS_ONLINE . " where time_last_click < '" . $xx_mins_ago . "'");

  $whos_online_query = $gBitDb->Execute("select customer_id from " . TABLE_WHOS_ONLINE);
  $user_total = $whos_online_query->RecordCount();
  while (!$whos_online_query->EOF) {
    if (!$whos_online_query->fields['customer_id'] == 0) {
		$n_members++;
	}
    if ($whos_online_query->fields['customer_id'] == 0) {
		$n_guests++;
	}
    $whos_online_query->MoveNext();
  }

  if ($user_total == 1) {
    $there_is_are = tra( 'There is' ) . '&nbsp;';
  } else {
    $there_is_are = tra( 'There are' ) . '&nbsp;';
  }

  if ($n_guests == 1) {
    $word_guest = '&nbsp;' . tra( 'Guest' );
  } else {
    $word_guest = '&nbsp;' . tra( 'Guests' );
  }

  if ($n_members == 1) {
    $word_member = '&nbsp;' . tra( 'Member' );
  } else {
    $word_member = '&nbsp;' . tra( 'Members' );
  }

  $textstring = $there_is_are;
  if ($n_guests >= 1) $textstring .= $n_guests . $word_guest;

  if (($n_guests >= 1) && ($n_members >= 1)) $textstring .= '&nbsp;' . tra( 'and' ) . '&nbsp;<br />';

  if ($n_members >= 1) $textstring .= $n_members . $word_member;

  $textstring .= '&nbsp;' . tra( 'online' );

	// only show if either the tutorials are active or additional links are active
	if( $user_total ) {
		$_template->tpl_vars['sideboxWhosOnline'] = new Smarty_variable( $textstring );
	}
	if( empty( $moduleTitle ) ) {
		$_template->tpl_vars['moduleTitle'] = new Smarty_variable( tra( 'Who\'s Online' ) );
	}
?>
