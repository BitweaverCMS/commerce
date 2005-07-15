<?php

// Load db classes
  global $gBitDb;
  $db = $gBitDb;

  // set the language
  if( empty( $_SESSION['languages_id'] ) || isset($_GET['language'])) {
    include( BITCOMMERCE_PKG_PATH.'includes/classes/language.php' );
    $lng = new language();

    if (isset($_GET['language']) && zen_not_null($_GET['language'])) {
      $lng->set_language($_GET['language']);
    } else {
      $lng->get_browser_language();
      $lng->set_language(DEFAULT_LANGUAGE);
    }

    if( $lng->load( $gBitLanguage->getLanguage() ) ) {
      $_SESSION['languages_id'] = $lng->mInfo['languages_id'];
	} else {
      $_SESSION['languages_id'] = 1;
	}
  }

// include the cache class
  require(DIR_FS_CLASSES . 'cache.php');
  $zc_cache = new cache;

  $configuration = $db->Execute('select configuration_key as cfgkey, configuration_value as cfgvalue
                                 from ' . TABLE_CONFIGURATION, '', true, 150);

  while (!$configuration->EOF) {
//    define($configuration->fields['cfgkey'], $configuration->fields['cfgvalue']);
    define($configuration->fields['cfgkey'], $configuration->fields['cfgvalue']);
//    echo $configuration->fields['cfgkey'] . '#';
    $configuration->MoveNext();
  }
  $configuration = $db->Execute('select configuration_key as cfgkey, configuration_value as cfgvalue
                          from ' . TABLE_PRODUCT_TYPE_LAYOUT);

  while (!$configuration->EOF) {
    define($configuration->fields['cfgkey'], $configuration->fields['cfgvalue']);
    $configuration->movenext();
  }

// sniffer class
  require(DIR_FS_CLASSES . 'sniffer.php');
  $sniffer = new sniffer;

	global $gBitCustomer;
	$gBitCustomer = new CommerceCustomer( $_SESSION['customer_id'] );
	$gBitCustomer->load();

// {{{ TIKI_MOD
	global $gBitUser;
	if( !empty( $_SESSION['customer_id'] ) && !$gBitUser->isRegistered() ) {
		// we have lost our bitweaver
		unset( $_SESSION['customer_id'] );
	} elseif( $gBitUser->isRegistered() ) {
	  CommerceCustomer::syncBitUser( $gBitUser->mInfo );
	  $_SESSION['customer_id'] = $gBitUser->mUserId;
	}

	if( $session_started && $gBitUser->isRegistered() && empty( $_SESSION['customer_id'] ) ) {
		// Set theme related directories
		$sql = "SELECT count(*) as total FROM " . TABLE_CUSTOMERS . "
				WHERE customers_id = '" . zen_db_input( $gBitUser->mUserId ) . "'";
		$check_user = $db->Execute($sql);
		if( empty( $check_user['fields']['total'] ) ) {
			$_REQUEST['action'] = 'process';
			$email_format = zen_db_prepare_input( $gBitUser->mInfo['email'] );
			if( $space = strpos( $gBitUser->mInfo['real_name'], ' ' ) ) {
				$firstname = zen_db_prepare_input( substr( $gBitUser->mInfo['real_name'], 0, $space ) );
				$lastname = zen_db_prepare_input( $gBitUser->mInfo['lastname'], $space );
			}
		}
	}

// }}} TIKI_MOD




?>
