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
// $Id: header_php.php,v 1.5 2006/07/12 04:11:19 spiderr Exp $
//
  require_once(DIR_FS_MODULES . 'require_languages.php');
  $error = false;

  if ( (isset($_REQUEST['keyword']) && empty($_REQUEST['keyword'])) &&
       (isset($_REQUEST['dfrom']) && (empty($_REQUEST['dfrom']) || ($_REQUEST['dfrom'] == DOB_FORMAT_STRING))) &&
       (isset($_REQUEST['dto']) && (empty($_REQUEST['dto']) || ($_REQUEST['dto'] == DOB_FORMAT_STRING))) &&
       (isset($_REQUEST['pfrom']) && !is_numeric($_REQUEST['pfrom'])) &&
       (isset($_REQUEST['pto']) && !is_numeric($_REQUEST['pto'])) ) {
    $error = true;
    $messageStack->add_session('search', ERROR_AT_LEAST_ONE_INPUT);
  } else {
    $dfrom = '';
    $dto = '';
    $pfrom = '';
    $pto = '';
    $keywords = '';

    if (isset($_REQUEST['dfrom'])) {
      $dfrom = (($_REQUEST['dfrom'] == DOB_FORMAT_STRING) ? '' : $_REQUEST['dfrom']);
    }

    if (isset($_REQUEST['dto'])) {
      $dto = (($_REQUEST['dto'] == DOB_FORMAT_STRING) ? '' : $_REQUEST['dto']);
    }

    if (isset($_REQUEST['pfrom'])) {
      $pfrom = $_REQUEST['pfrom'];
    }

    if (isset($_REQUEST['pto'])) {
      $pto = $_REQUEST['pto'];
    }

    if (isset($_REQUEST['keyword'])) {
      $keywords = $_REQUEST['keyword'];
    }

    $date_check_error = false;
    if (zen_not_null($dfrom)) {
      if (!zen_checkdate($dfrom, DOB_FORMAT_STRING, $dfrom_array)) {
        $error = true;
        $date_check_error = true;

        $messageStack->add_session('search', ERROR_INVALID_FROM_DATE);
      }
    }

    if (zen_not_null($dto)) {
      if (!zen_checkdate($dto, DOB_FORMAT_STRING, $dto_array)) {
        $error = true;
        $date_check_error = true;

        $messageStack->add_session('search', ERROR_INVALID_TO_DATE);
      }
    }

    if (($date_check_error == false) && zen_not_null($dfrom) && zen_not_null($dto)) {
      if (mktime(0, 0, 0, $dfrom_array[1], $dfrom_array[2], $dfrom_array[0]) > mktime(0, 0, 0, $dto_array[1], $dto_array[2], $dto_array[0])) {
        $error = true;

        $messageStack->add_session('search', ERROR_TO_DATE_LESS_THAN_FROM_DATE);
      }
    }

    $price_check_error = false;
    if (zen_not_null($pfrom)) {
      if (!settype($pfrom, 'float')) {
        $error = true;
        $price_check_error = true;

        $messageStack->add_session('search', ERROR_PRICE_FROM_MUST_BE_NUM);
      }
    }

    if (zen_not_null($pto)) {
      if (!settype($pto, 'float')) {
        $error = true;
        $price_check_error = true;

        $messageStack->add_session('search', ERROR_PRICE_TO_MUST_BE_NUM);
      }
    }

    if (($price_check_error == false) && is_float($pfrom) && is_float($pto)) {
      if ($pfrom >= $pto) {
        $error = true;

        $messageStack->add_session('search', ERROR_PRICE_TO_LESS_THAN_PRICE_FROM);
      }
    }

    if (zen_not_null($keywords)) {
      if (!zen_parse_search_string($keywords, $search_keywords)) {
        $error = true;

        $messageStack->add_session('search', ERROR_INVALID_KEYWORDS);
      }
    }
  }

  if (empty($dfrom) && empty($dto) && empty($pfrom) && empty($pto) && empty($keywords)) {
    $error = true;
    // redundant should be able to remove this
    $messageStack->add_session('search', ERROR_AT_LEAST_ONE_INPUT);
  }

  if ($error == true) {
    zen_redirect(zen_href_link(FILENAME_ADVANCED_SEARCH, zen_get_all_get_params(), 'NONSSL', true, false));
  }

  $breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_ADVANCED_SEARCH));
  $breadcrumb->add(NAVBAR_TITLE_2);
?>
