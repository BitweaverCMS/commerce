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
// $messageStack->add('REGISTERED GLOBALS ARE TURNED OFF IN .htaccess ','caution');

if (isset($_GET['vcheck']) && $_GET['vcheck']!='') $version_check_requested=true;

// Show Languages Dropdown for convenience only if main filename and directory exists
if ((basename($_SERVER['SCRIPT_NAME']) != FILENAME_DEFINE_LANGUAGE . '.php') and (basename($_SERVER['SCRIPT_NAME']) != FILENAME_PRODUCTS_OPTIONS_NAME . '.php') and empty($action)) {
  $languages = zen_get_languages();
  if (sizeof($languages) > 1) {
    $languages_array = array();
    $languages_selected = $_GET['language'];
    $missing_languages='';
    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
      $test_directory= DIR_WS_LANGUAGES . $languages[$i]['directory'];
      $test_file= DIR_WS_LANGUAGES . $languages[$i]['directory'] . '.php';
      if ( file_exists($test_file) and file_exists($test_directory) ) {
        $count++;
        $languages_array[] = array('id' => $languages[$i]['code'],
                                 'text' => $languages[$i]['name']);
//        if ($languages[$i]['directory'] == $language) {
        if ($languages[$i]['directory'] == $gBitCustomer->getLanguage()) {
          $languages_selected = $languages[$i]['code'];
        }
      } else {
        $missing_languages .= ' ' . ucfirst($languages[$i]['directory']) . ' ' . $languages[$i]['name'];
      }
    }

// if languages in table do not match valid languages show error message
    if ($count != sizeof($languages)) {
      $messageStack->add('MISSING LANGUAGE FILES OR DIRECTORIES ...' . $missing_languages,'caution');
    }
    $hide_languages= false;
  } else {
    $hide_languages= true;
  } // more than one language
} else {
  $hide_languages= true;
} // hide when other language dropdown is used

// check database version against source code
  $zv_db_patch_ok = true; // we start with true
  if (WARN_DATABASE_VERSION_PROBLEM != 'false') {
    $result = $gBitDb->Execute("SELECT `project_version_major`, `project_version_minor` FROM " . TABLE_PROJECT_VERSION . " WHERE `project_version_key` = 'Zen-Cart Database'");
    $zv_db_patch_level_found = $result->fields['project_version_major']. '.' . $result->fields['project_version_minor'];
    $zv_db_patch_level_expected = EXPECTED_DATABASE_VERSION_MAJOR . '.' . EXPECTED_DATABASE_VERSION_MINOR;
    if ($zv_db_patch_level_expected=='.' || ($zv_db_patch_level_found < $zv_db_patch_level_expected) ) {
      $zv_db_patch_ok = false;
      $messageStack->add(WARNING_DATABASE_VERSION_OUT_OF_DATE, 'warning');
    }
  }

// display alerts/error messages, if any
  if ($messageStack->size > 0) {
    echo $messageStack->output();
  }

/*
SPIDERKILL for obvious reasons...
// check version with zen-cart server
  // ignore version-check if INI file setting has been set
  if (file_exists(DIR_FS_ADMIN . 'includes/local/skip_version_check.ini')) {
    $lines=@file(DIR_FS_ADMIN . 'includes/local/skip_version_check.ini');
    foreach($lines as $line) {
      if (substr($line,0,14)=='version_check=') $version_from_ini=substr(trim(strtolower(str_replace('version_check=','',$line))),0,3);
    }
  }
  // ignore version check if not enabled or if not on main page or sysinfo page
  if ((SHOW_VERSION_UPDATE_IN_HEADER == 'true' && $version_from_ini !='off' && ($version_check_sysinfo==true || $version_check_index==true) && $zv_db_patch_ok == true) || $version_check_requested==true ) {
    $new_version = TEXT_VERSION_CHECK_CURRENT; //set to "current" by default
    $lines = @file(NEW_VERSION_CHECKUP_URL);
    //check for major/minor version info
    if ((trim($lines[0]) > PROJECT_VERSION_MAJOR) || (trim($lines[0]) == PROJECT_VERSION_MAJOR && trim($lines[1]) > PROJECT_VERSION_MINOR)) {
      $new_version = TEXT_VERSION_CHECK_NEW_VER . trim($lines[0]) . '.' . trim($lines[1]) . ' :: ' . $lines[2];
    }
    //check for patch version info
    // first confirm that we're at latest major/minor -- otherwise no need to check patches:
    if (trim($lines[0]) == PROJECT_VERSION_MAJOR && trim($lines[1]) == PROJECT_VERSION_MINOR) {
      //check to see if either patch needs to be applied
      if (trim($lines[3]) > intval(PROJECT_VERSION_PATCH1) || trim($lines[4]) > intval(PROJECT_VERSION_PATCH2)) {
        // reset update message, since we WILL be advising of an available upgrade
        if ($new_version == TEXT_VERSION_CHECK_CURRENT) $new_version = '';
        //check for patch #1
        if (trim($lines[3]) > intval(PROJECT_VERSION_PATCH1)) {
//          if ($new_version != '') $new_version .= '<br />';
          $new_version .= (($new_version != '') ? '<br />' : '') . '<span class="alert">' . TEXT_VERSION_CHECK_NEW_PATCH . trim($lines[0]) . '.' . trim($lines[1]) . ' - ' .TEXT_VERSION_CHECK_PATCH .': [' . trim($lines[3]) . '] :: ' . $lines[5] . '</span>';
        }
        if (trim($lines[4]) > intval(PROJECT_VERSION_PATCH2)) {
//          if ($new_version != '') $new_version .= '<br />';
          $new_version .= (($new_version != '') ? '<br />' : '') . '<span class="alert">' . TEXT_VERSION_CHECK_NEW_PATCH . trim($lines[0]) . '.' . trim($lines[1]) . ' - ' .TEXT_VERSION_CHECK_PATCH .': [' . trim($lines[4]) . '] :: ' . $lines[5] . '</span>';
        }
      }
    }
    // display download link
    if ($new_version != '' && $new_version != TEXT_VERSION_CHECK_CURRENT) $new_version .= '<br /><a href="' . $lines[6] . '" target="_blank">'. TEXT_VERSION_CHECK_DOWNLOAD .'</a>';
  } else {
    // display the "check for updated version" button.  The button link should be the current page and all param's
    $url=($_SERVER['REQUEST_URI']!='') ? $_SERVER['REQUEST_URI'] : zen_href_link_admin(FILENAME_DEFAULT);
    $url .= (strpos($url,'?')>5) ? '&vcheck=yes' : '?vcheck=yes';
    if ($zv_db_patch_ok == true || $version_check_sysinfo==true ) $new_version = '<a href="' . $url . '">' . zen_image_button('button_check_new_version.gif',IMAGE_CHECK_VERSION) . '</a>';
  }
*/
// check GV release queue and alert store owner
  if (SHOW_GV_QUEUE==true) {
    if( $new_gv_queue= $gBitDb->getOne("select COUNT(*) from " . TABLE_COUPON_GV_QUEUE . " where `release_flag`='N'") ) {
      $new_gv_queue_cnt= $new_gv_queue;
      $goto_gv = '<a href="' . zen_href_link_admin(FILENAME_GV_QUEUE) . '">' . zen_image_button('button_gift_queue.gif',IMAGE_GIFT_QUEUE) . '</a>';
    }
  }
/*
?>
<table border="0" cellspacing="0" cellpadding="0" width="100%">
  <tr class="headerBar" height="20" width="100%">

    <td class="headerBarContent" align="left">
      <?php
      if (!$hide_languages) {
        echo zen_draw_form_admin('languages', basename($_SERVER['SCRIPT_NAME']), '', 'get');
        echo DEFINE_LANGUAGE . '&nbsp;&nbsp;' . (sizeof($languages) > 1 ? zen_draw_pull_down_menu('language', $languages_array, $languages_selected, 'onChange="this.form.submit();"') : '');
        echo '</form>';
      } else {
        echo '&nbsp;';
      }
    ?>
    </td>
    <td class="headerBarContent" align="center"><b><?php echo date("r", time()) . 'GMT'  . '&nbsp;[' .  $_SERVER['REMOTE_ADDR'] . ' ]&nbsp;'; ?></b></td>
    <td class="headerBarContent" align="right"><?php echo '<a href="' . zen_href_link_admin(FILENAME_DEFAULT, '', 'NONSSL') . '" class="headerLink">' . HEADER_TITLE_TOP . '</a>&nbsp;|&nbsp;<a href="' . BITCOMMERCE_PKG_URL . '" class="headerLink" target="_blank">' . HEADER_TITLE_ONLINE_CATALOG . '</a>&nbsp;|&nbsp;<a href="http://www.bitcommerce.org/" class="headerLink" target="_blank">' . HEADER_TITLE_SUPPORT_SITE . '</a>'; ?></td>
  </tr>
</table>
*/
?>
