<?php
// We must include this file separately as admin/ has different ideas of templates and constants, etc.

require_once( BITCOMMERCE_PKG_PATH.'includes/functions/html_output.php');
require_once( BITCOMMERCE_PKG_PATH.'includes/functions/functions_general.php');


// define general functions used application-wide
require_once(DIR_FS_FUNCTIONS . 'functions_email.php');

// load extra functions
require_once(DIR_FS_MODULES . 'extra_functions.php');

// set host_address once per session to reduce load on server
if( empty( $_SESSION['customers_host_address'] ) ) {
	if (SESSION_IP_TO_HOST_ADDRESS == 'true') {
		$_SESSION['customers_host_address']= gethostbyaddr($_SERVER['REMOTE_ADDR']);
	} else {
		$_SESSION['customers_host_address'] = OFFICE_IP_TO_HOST_ADDRESS;
	}
}

// verify the ssl_session_id if the feature is enabled
if ( ($request_type == 'SSL') && $gCommerceSystem->isConfigActive( 'SESSION_CHECK_SSL_SESSION_ID' ) && (ENABLE_SSL == 'true') && ($session_started == true) ) {
	$ssl_session_id = $_SERVER['SSL_SESSION_ID'];
	if (!$_SESSION['SSL_SESSION_ID']) {
		$_SESSION['SESSION_SSL_ID'] = $ssl_session_id;
	}

	if ($_SESSION['SESSION_SSL_ID'] != $ssl_session_id) {
		session_destroy();
		zen_redirect(zen_href_link(FILENAME_SSL_CHECK));
	}
}

// verify the browser user agent if the feature is enabled
if( $gCommerceSystem->isConfigActive( 'SESSION_CHECK_USER_AGENT' ) ) {
	$http_user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (!$_SESSION['SESSION_USER_AGENT']) {
		$_SESSION['SESSION_USER_AGENT'] = $http_user_agent;
	}

	if ($_SESSION['SESSION_USER_AGENT'] != $http_user_agent) {
		session_destroy();
		zen_redirect(FILENAME_LOGIN);
	}
}

// verify the IP address if the feature is enabled
if( $gCommerceSystem->isConfigActive( 'SESSION_CHECK_IP_ADDRESS' ) ) {
	$ip_address = zen_get_ip_address();
	if (!$_SESSION['SESSION_IP_ADDRESS']) {
		$_SESSION['SESSION_IP_ADDRESS'] = $ip_address;
	}

	if ($_SESSION['SESSION_IP_ADDRESS'] != $ip_address) {
		session_destroy();
		zen_redirect(FILENAME_LOGIN);
	}
}

// include the mail classes
require(DIR_FS_CLASSES . 'mime.php');
require(DIR_FS_CLASSES . 'email.php');

// include the language translations
// include template specific language files
if (file_exists(DIR_WS_LANGUAGES . $gCommerceSystem->mTemplateDir . '/' . $gBitCustomer->getLanguage() . '.php')) {
	$template_dir_SELECT = $gCommerceSystem->mTemplateDir . '/';
	//die('Yes ' . DIR_WS_LANGUAGES . $gCommerceSystem->mTemplateDir . '/' . $gBitCustomer->getLanguage() . '.php');
} else {
	//die('NO ' . DIR_WS_LANGUAGES . $gCommerceSystem->mTemplateDir . '/' . $gBitCustomer->getLanguage() . '.php');
	$template_dir_SELECT = '';
}

$langFile = DIR_WS_LANGUAGES . $template_dir_SELECT . $gBitCustomer->getLanguage() . '.php';
if( !file_exists( $langFile ) ) {
	$langFile = DIR_WS_LANGUAGES . $template_dir_SELECT . 'en.php';
}
require( $langFile );

// include the extra language translations
include(DIR_FS_MODULES . 'extra_definitions.php');

// currency
if( empty( $_SESSION['currency'] ) || isset($_REQUEST['currency']) || ( (USE_DEFAULT_LANGUAGE_CURRENCY == 'true') && (LANGUAGE_CURRENCY != $_SESSION['currency']) ) ) {
	if (isset($_REQUEST['currency'])) {
		if ( zen_currency_exists($_REQUEST['currency'])) {
			$_SESSION['currency'] = $_REQUEST['currency'];
		} else {
			$_SESSION['currency'] = (USE_DEFAULT_LANGUAGE_CURRENCY == 'true') ? LANGUAGE_CURRENCY : DEFAULT_CURRENCY;
		}
	}
}

