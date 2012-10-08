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

  switch ($_GET['action']) {
    case 'banner':
      $banner_query = "select `banners_url` 
                       from " . TABLE_BANNERS . " 
                       where `banners_id` = '" . (int)$_GET['goto'] . "'";
      $banner = $gBitDb->Execute($banner_query);

      if ($banner->RecordCount() > 0) {
		require_once( BITCOMMERCE_PKG_PATH.'includes/functions/banner.php' );
        zen_update_banner_click_count($_GET['goto']);

        zen_redirect($banner->fields['banners_url']);
      }
      break;

    case 'url':
      if (isset($_GET['goto']) && zen_not_null($_GET['goto'])) {
        zen_redirect('http://' . $_GET['goto']);
      }
      break;

    case 'manufacturer':
      if (isset($_GET['manufacturers_id']) && zen_not_null($_GET['manufacturers_id'])) {
        $manufacturer = $gBitDb->Execute("select `manufacturers_url` 
                                      from " . TABLE_MANUFACTURERS_INFO . " 
                                      where `manufacturers_id` = '" . (int)$_GET['manufacturers_id'] . "' 
                                      and `languages_id` = '" . (int)$_SESSION['languages_id'] . "'");

        if ($manufacturer->RecordCount()) {
// url exists in selected language

          if (zen_not_null($manufacturer->fields['manufacturers_url'])) {
            $gBitDb->Execute("update " . TABLE_MANUFACTURERS_INFO . " 
                          set `url_clicked` = url_clicked+1, `date_last_click` = now() 
                          where `manufacturers_id` = '" . (int)$_GET['manufacturers_id'] . "' 
                          and `languages_id` = '" . (int)$_SESSION['languages_id'] . "'");


            zen_redirect($manufacturer->fields['manufacturers_url']);
          }
        } else {
// no url exists for the selected language, lets use the default language then
          $manufacturer = $gBitDb->Execute("select mi.`languages_id`, mi.`manufacturers_url` 
                                        from " . TABLE_MANUFACTURERS_INFO . " mi, " . TABLE_LANGUAGES . " l 
                                        where mi.`manufacturers_id` = '" . (int)$_GET['manufacturers_id'] . "' 
                                        and mi.`languages_id` = l.`languages_id`
                                        and l.`code` = " . DEFAULT_LANGUAGE . "'");

          if ($manufacturer->RecordCount > 0) {

            if (zen_not_null($manufacturer->fields['manufacturers_url'])) {
              $gBitDb->Execute("update " . TABLE_MANUFACTURERS_INFO . " 
                            set `url_clicked` = url_clicked+1, `date_last_click` = now() 
                            where `manufacturers_id` = '" . (int)$_GET['manufacturers_id'] . "' 
                            and `languages_id` = '" . (int)$manufacturer['languages_id'] . "'");


              zen_redirect($manufacturer->fields['manufacturers_url']);
            }
          }
        }
      }
      break;
  }

  zen_redirect(zen_href_link(FILENAME_DEFAULT));
?>
