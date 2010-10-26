<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
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
/**
 * @package ZenCart_Functions
*/

  ////////////////////////////////////////////////////////////////////////////////////////////////
  //
  // Function    : zen_validate_email
  //
  // Arguments   : email   email address to be checked
  //
  // Return      : true  - valid email address
  //               false - invalid email address
  //
  // Description : function for validating email address that conforms to RFC 822 specs
  //
  //
  // Sample Valid Addresses:
  //
  //    first.last@host.com
  //    firstlast@host.to
  //    "first last"@host.com
  //    "first@last"@host.com
  //    first-last@host.com
  //	first's-address@e-mail.host.4somewhere.com
  //    first.last@[123.123.123.123]
  //
  //	hosts with either external IP addresses or from 2-6 characters will pass (e.g. .jp or .museum)
  //
  // Invalid Addresses:
  //
  //    first last@host.com
  //	'first@host.com
  //
  ////////////////////////////////////////////////////////////////////////////////////////////////

  function zen_validate_email($email) {
    $valid_address = true;

// split the e-mail address into user and domain parts
// need to update to trap for addresses in the format of "first@last"@someplace.com
// this method will most likely break in that case	
	@list( $user, $domain ) = explode( "@", $email );
	$valid_ip_form = '[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}';
	$valid_email_pattern = '^[a-z0-9]+[a-z0-9_\.\'\-]*@[a-z0-9]+[a-z0-9\.\-]*\.(([a-z]{2,6})|([0-9]{1,3}))$';
	$space_check = '[ ]';
 
// strip beginning and ending quotes, if and only if both present
	if( (preg_match('/^["]/', $user) && preg_match('/["]$/', $user)) ){
		$user = preg_replace ( '/^["]/', '', $user );
		$user = preg_replace ( '/["]$/', '', $user );
		$user = preg_replace ( "/$space_check/", '', $user ); //spaces in quoted addresses OK per RFC (?)
		$email = $user."@".$domain; // contine with stripped quotes for remainder
	}

// if e-mail domain part is an IP address, check each part for a value under 256
	if( preg_match( "/$valid_ip_form/", $domain ) ) {
	  $digit = explode( ".", $domain );
	  for($i=0; $i<4; $i++) {
		if ($digit[$i] > 255) {
		  $valid_address = false;
		  return $valid_address;
		  exit;
		}
// stop crafty people from using internal IP addresses
		if (($digit[0] == 192) || ($digit[0] == 10)) {
		  $valid_address = false;
		  return $valid_address;
		  exit;
		}
	  }
	}
	
	if (!preg_match("/$space_check/", $email)) { // trap for spaces in 
	  if ( preg_match( "/$valid_email_pattern/", $email ) ) { // validate against valid e-mail patterns
		$valid_address = true;
	  } else {
		$valid_address = false;
		return $valid_address;
		exit;
	  	}
	  }
  
// Verify e-mail has an associated MX and/or A record.
// Need alternate method to deal with Verisign shenanigans and with Windows Servers
//		if (!checkdnsrr($domain, "MX") && !checkdnsrr($domain, "A")) {
//		  $valid_address = false;
//		}
  
    return $valid_address;
  }  
?>
