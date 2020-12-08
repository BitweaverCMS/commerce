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
//  $Id$
//

require_once( BITCOMMERCE_PKG_PATH.'includes/functions/html_output.php' );
////
// The HTML href link wrapper function
function zen_href_link_admin($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true) {
	global $request_type, $session_started, $http_domain, $https_domain;

	$link = DIR_WS_HTTPS_ADMIN;

	if (!strstr($page, '.php')) {
		$page .= '.php';
	}

	if ($parameters == '') {
		$link = $link . $page;
		$separator = '?';
	} else {
		$link = $link . $page . '?' . $parameters;
		$separator = '&';
	}

	while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);

// Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
	if ( ($add_session_id == true) && ($session_started == true) ) {
		if (defined('SID') && zen_not_null(SID)) {
			$sid = SID;
		} elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL_ADMIN == 'true') ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
//die($connection);
			if ($http_domain != $https_domain) {
				$sid = zen_session_name() . '=' . zen_session_id();
			}
		}
	}

	if (isset($sid)) {
		$link .= $separator . $sid;
	}

	return $link;
}

////
// Draw a 1 pixel black line
  function zen_black_line() {
    return zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '100%', '1');
  }


////
// javascript to dynamically update the states/provinces list when the country is changed
// TABLES: zones
  function zen_js_zone_list($country, $form, $field) {
    global $gBitDb;
    $countries = $gBitDb->Execute("select distinct zone_country_id
                               from " . TABLE_ZONES . "
                               order by zone_country_id");

    $num_country = 1;
    $output_string = '';
    while (!$countries->EOF) {
      if ($num_country == 1) {
        $output_string .= '  if (' . $country . ' == "' . $countries->fields['zone_country_id'] . '") {' . "\n";
      } else {
        $output_string .= '  } else if (' . $country . ' == "' . $countries->fields['zone_country_id'] . '") {' . "\n";
      }

      $states = $gBitDb->Execute("select `zone_name`, `zone_id`
                              from " . TABLE_ZONES . "
                              where `zone_country_id` = '" . $countries->fields['zone_country_id'] . "'
                              order by `zone_name`");


      $num_state = 1;
      while (!$states->EOF) {
        if ($num_state == '1') $output_string .= '    ' . $form . '.' . $field . '.options[0] = new Option("' . PLEASE_SELECT . '", "");' . "\n";
        $output_string .= '    ' . $form . '.' . $field . '.options[' . $num_state . '] = new Option("' . $states->fields['zone_name'] . '", "' . $states->fields['zone_id'] . '");' . "\n";
        $num_state++;
        $states->MoveNext();
      }
      $num_country++;
      $countries->MoveNext();
    }
    $output_string .= '  } else {' . "\n" .
                      '    ' . $form . '.' . $field . '.options[0] = new Option("' . TYPE_BELOW . '", "");' . "\n" .
                      '  }' . "\n";

    return $output_string;
  }

////
// Output a form
  function zen_draw_form_admin($name, $action, $parameters = '', $method = 'post', $params = '', $usessl = 'false') {
	global $gBitUser;
    $form = '<form name="' . zen_output_string($name) . '" action="';
	$sslType = (isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on') ? 'SSL' : 'NONSSL';
    $form .= zen_href_link_admin( $action, $parameters, $sslType );
    $form .= '" method="' . zen_output_string($method) . '"';
    if (zen_not_null($params)) {
      $form .= ' ' . $params;
    }
    $form .= '>';
	$form .= '<input type="hidden" name="tk" value="'.$gBitUser->mTicket.'" />';
    return $form;
  }


?>
