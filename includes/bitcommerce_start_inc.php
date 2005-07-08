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


?>
