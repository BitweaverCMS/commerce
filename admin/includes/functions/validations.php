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
//  $Id: validations.php,v 1.2 2005/10/06 21:01:45 spiderr Exp $
//


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
  //               This function is converted from a JavaScript written by
  //               Sandeep V. Tamhankar (stamhankar@hotmail.com). The original JavaScript
  //               is available at http://javascript.internet.com
  //
  // Sample Valid Addresses:
  //
  //    first.last@host.com
  //    firstlast@host.to
  //    "first last"@host.com
  //    "first@last"@host.com
  //    first-last@host.com
  //    first.last@[123.123.123.123]
  //
  // Invalid Addresses:
  //
  //    first last@host.com
  //
  //
  ////////////////////////////////////////////////////////////////////////////////////////////////
  function zen_validate_email($email) {
    $valid_address = true;

    $mail_pat = '^(.+)@(.+)$';
    $valid_chars = "[^] \(\)<>@,;:\.\\\"\[]";
    $atom = "$valid_chars+";
    $quoted_user='(\"[^\"]*\")';
    $word = "($atom|$quoted_user)";
    $user_pat = "^$word(\.$word)*$";
    $ip_domain_pat='^\[([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\]$';
    $domain_pat = "^$atom(\.$atom)*$";

    if (eregi($mail_pat, $email, $components)) {
      $user = $components[1];
      $domain = $components[2];
      // validate user
      if (eregi($user_pat, $user)) {
        // validate domain
        if (eregi($ip_domain_pat, $domain, $ip_components)) {
          // this is an IP address
      	  for ($i=1;$i<=4;$i++) {
      	    if ($ip_components[$i] > 255) {
      	      $valid_address = false;
      	      break;
      	    }
          }
        }
        else {
          // Domain is a name, not an IP
          if (eregi($domain_pat, $domain)) {
            /* domain name seems valid, but now make sure that it ends in a valid TLD or ccTLD
               and that there's a hostname preceding the domain or country. */
            $domain_components = explode(".", $domain);
            // Make sure there's a host name preceding the domain.
            if (sizeof($domain_components) < 2) {
              $valid_address = false;
            } else {
              $top_level_domain = strtolower($domain_components[sizeof($domain_components)-1]);
              // Allow all 2-letter TLDs (ccTLDs)
              if (eregi('^[a-z][a-z]$', $top_level_domain) != 1) {
                $tld_pattern = '';
                // Get authorized TLDs from text file
                $tlds = file(DIR_WS_INCLUDES . 'tld.txt');
                while (list(,$line) = each($tlds)) {
                  // Get rid of comments
                  $words = explode('#', $line);
                  $tld = trim($words[0]);
                  // TLDs should be 3 letters or more
                  if (eregi('^[a-z]{3,}$', $tld) == 1) {
                    $tld_pattern .= '^' . $tld . '$|';
                  }
                }
                // Remove last '|'
                $tld_pattern = substr($tld_pattern, 0, -1);
                if (eregi("$tld_pattern", $top_level_domain) == 0) {
                    $valid_address = false;
                }
              }
            }
          }
          else {
      	    $valid_address = false;
      	  }
      	}
      }
      else {
        $valid_address = false;
      }
    }
    else {
      $valid_address = false;
    }
    if ($valid_address && ENTRY_EMAIL_ADDRESS_CHECK == 'true') {
      if (!checkdnsrr($domain, "MX") && !checkdnsrr($domain, "A")) {
        $valid_address = false;
      }
    }
    return $valid_address;
  }
?>
