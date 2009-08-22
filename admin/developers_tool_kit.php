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
//  $Id: developers_tool_kit.php,v 1.11 2009/08/22 21:29:03 spiderr Exp $
//

  require('includes/application_top.php');

  
  $currencies = new currencies();

  $languages = zen_get_languages();

  $configuration_key_lookup = zen_db_prepare_input($_POST['configuration_key']);

  function getDirList ($dirName) {
    global $directory_array, $sub_dir_files;
// add directory name to the sub_dir_files list;
    $sub_dir_files[] = $dirName;
    $d = dir($dirName);
    $file_extension = '.php';
    while($entry = $d->read()) {
      if ($entry != "." && $entry != "..") {
        if (is_dir($dirName."/".$entry)) {
          if ($entry == 'CVS') {
          // skip
          } else {
            getDirList($dirName."/".$entry);
          }
        } else {
          if (substr($entry, strrpos($entry, '.')) == $file_extension) {
//echo 'I AM HERE 2 ' . $dirName."/".$entry . '<br>';
//            $directory_array[] .= $dirName."/".$entry;
          } else {
//echo 'I AM HERE 3 ' . $dirName."/".$entry . '<br>';
          }
        }
      }
    }
    $d->close();

    return $sub_dir_files;
  }

  function zen_display_files() {
    global $check_directory, $found, $configuration_key_lookup;
    for ($i = 0, $n = sizeof($check_directory); $i < $n; $i++) {
//echo 'I SEE ' . $check_directory[$i] . '<br>';

      $dir_check = $check_directory[$i];
      $file_extension = '.php';

      if ($dir = @dir($dir_check)) {
        while ($file = $dir->read()) {
          if (!is_dir($dir_check . $file)) {
            if (substr($file, strrpos($file, '.')) == $file_extension) {
              $directory_array[] = $dir_check . $file;
            }
          }
        }
        if (sizeof($directory_array)) {
          sort($directory_array);
        }
        $dir->close();
      }
    }
/*
// display filenames found
              $file_cnt=0;
              for ($i = 0, $n = sizeof($directory_array); $i < $n; $i++) {
                $file_cnt++;
                $file = $directory_array[$i];

                if (file_exists($file)) {
                  echo $file . '<br>';
                }
              }
*/

// show path and filename
    echo '<table border="0" width="100%" cellspacing="2" cellpadding="1" align="center">' . "\n";
    echo '<tr><td>&nbsp;</td></tr>';
    echo '<tr class="infoBoxContent"><td class="dataTableHeadingContent">' . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . TEXT_INFO_SEARCHING . sizeof($directory_array) . TEXT_INFO_FILES_FOR . $configuration_key_lookup . '</td></tr></table>' . "\n\n";
    echo '<tr><td>&nbsp;</td></tr>';

// check all files located
    $file_cnt = 0;
    $cnt_found=0;
    for ($i = 0, $n = sizeof($directory_array); $i < $n; $i++) {
    // build file content of matching lines
      $file_cnt++;
      $file = $directory_array[$i];

      // clean path name
      while (strstr($file, '//')) $file = str_replace('//', '/', $file);

      $show_file = '';
      if (file_exists($file)) {
        $show_file .= "\n" . '<table border="2" width="95%" cellspacing="2" cellpadding="1" align="center"><tr><td class="main">' . "\n";
        $show_file .= '<tr class="infoBoxContent"><td class="dataTableHeadingContent">';
        $show_file .= '<strong>' . $file . '</strong>';
        $show_file .= '</td></tr>';
        $show_file .= '<tr><td class="main">';

        // put file into an array to be scanned
        $lines = file($file);
        $found_line = 'false';
        // loop through the array, show line and line numbers
        foreach ($lines as $line_num => $line) {
          $cnt_lines++;
          if (strstr(strtoupper($line), strtoupper($configuration_key_lookup))) {
            $found_line= 'true';
            $found = 'true';
            $cnt_found++;
            $show_file .= "<br />Line #<strong>{$line_num}</strong> : " ;
            //prevent db pwd from being displayed, for sake of security
            $show_file .= (substr_count($line,"'DB_SERVER_PASSWORD'")) ? '***HIDDEN***' : htmlspecialchars($line);
            $show_file .= "<br />\n";
          } else {
            if ($cnt_lines >= 5) {
//            $show_file .= ' .';
              $cnt_lines=0;
            }
          }
        }
      }
      $show_file .= '</td></tr></table>' . "\n";

      // if there was a match, show lines
      if ($found_line == 'true') {
        echo $show_file . '<table><tr><td>&nbsp;</td></tr></table>';
      } // show file
    }
    echo '<table border="0" width="100%" cellspacing="2" cellpadding="1" align="center"><tr class="infoBoxContent"><td class="dataTableHeadingContent">' . TEXT_INFO_MATCHES_FOUND . $cnt_found . '</td></tr></table>';
  } // zen_display_files


  $productsId = (isset($_GET['products_id']) ? $_GET['products_id'] : $productsId);

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  $current_category_id = (isset($_GET['current_category_id']) ? $_GET['current_category_id'] : $current_category_id);
  $found= 'true';

  switch($action) {
    case ('locate_configuration'):
      if ($configuration_key_lookup == '') {
        $messageStack->add_session(ERROR_CONFIGURATION_KEY_NOT_ENTERED, 'caution');
        zen_redirect(zen_href_link_admin(FILENAME_DEVELOPERS_TOOL_KIT));
      }
      $found = 'false';
      $zv_files_group = $_POST['zv_files'];

      $check_configure = $gBitDb->Execute("select * from " . TABLE_CONFIGURATION . " where `configuration_key`='" . $_POST['configuration_key'] . "'");
      if ($check_configure->RecordCount() < 1) {
        $check_configure = $gBitDb->Execute("select * from " . TABLE_PRODUCT_TYPE_LAYOUT . " where `configuration_key`='" . $_POST['configuration_key'] . "'");
        if ($check_configure->RecordCount() < 1) {
          // build filenames to search
          switch ($zv_files_group) {
            case (0): // none
              $filename_listing = '';
              break;
            case (1): // all english.php files
              $check_directory = array();
              $check_directory[] = DIR_FS_CATALOG_LANGUAGES . $gBitCustomer->getLanguage() . '/';
              $check_directory[] = DIR_FS_CATALOG_LANGUAGES . $template_dir . '/' . $gBitCustomer->getLanguage() . '/';
              $check_directory[] = DIR_FS_CATALOG_LANGUAGES . $gBitCustomer->getLanguage() . '/' . $template_dir . '/';
              $check_directory[] = DIR_FS_CATALOG_LANGUAGES . $gBitCustomer->getLanguage(). '/extra_definitions/';
              $check_directory[] = DIR_FS_CATALOG_LANGUAGES . $gBitCustomer->getLanguage(). '/extra_definitions/' . $template_dir . '/';
              $check_directory[] = DIR_FS_CATALOG_LANGUAGES . $gBitCustomer->getLanguage(). '/modules/payment/';
              $check_directory[] = DIR_FS_CATALOG_LANGUAGES . $gBitCustomer->getLanguage(). '/modules/shipping/';
              $check_directory[] = DIR_FS_CATALOG_LANGUAGES . $gBitCustomer->getLanguage(). '/modules/order_total/';
              $check_directory[] = DIR_FS_CATALOG_LANGUAGES . $gBitCustomer->getLanguage(). '/modules/product_types/';
              $check_directory[] = DIR_FS_ADMIN . DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/';
              $check_directory[] = DIR_FS_ADMIN . DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/modules/newsletters/';
              break;
            case (2): // all catalog /language/*.php
              $check_directory = array();
              $check_directory[] = DIR_FS_CATALOG_LANGUAGES;
              break;
            case (3): // all catalog /language/english/*.php
              $check_directory = array();
              $check_directory[] = DIR_FS_CATALOG_LANGUAGES . $gBitCustomer->getLanguage() . '/';
              break;
            case (4): // all admin /language/*.php
              $check_directory = array();
              $check_directory[] = DIR_FS_ADMIN . DIR_WS_LANGUAGES;
              break;
            case (5): // all admin /language/english/*.php
              // set directories and files names
              $check_directory = array();
              $check_directory[] = DIR_FS_ADMIN . DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/';
              break;
            } // eof: switch

              // Check for new databases and filename in extra_datafiles directory

              zen_display_files();

        } else {
          $show_products_type_layout = 'true';
          $show_configuration_info = 'true';
          $found = 'true';
        }
      } else {
        $show_products_type_layout = 'false';
        $show_configuration_info = 'true';
        $found = 'true';
      }

      break;

    case ('locate_function'):
      if ($configuration_key_lookup == '') {
        $messageStack->add_session(ERROR_CONFIGURATION_KEY_NOT_ENTERED, 'caution');
        zen_redirect(zen_href_link_admin(FILENAME_DEVELOPERS_TOOL_KIT));
      }
      $found = 'false';
      $zv_files_group = $_POST['zv_files'];

          // build filenames to search
          switch ($zv_files_group) {
            case (0): // none
              $filename_listing = '';
              break;
            case (1): // all admin/catalog function files
              $check_directory = array();
              $check_directory[] = DIR_FS_CATALOG . DIR_WS_FUNCTIONS;
              $check_directory[] = DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'extra_functions/';
              $check_directory[] = DIR_FS_ADMIN . DIR_WS_FUNCTIONS;
              $check_directory[] = DIR_FS_ADMIN . DIR_WS_FUNCTIONS . 'extra_functions/';
              break;
            case (2): // all catalog function files
              $check_directory = array();
              $check_directory[] = DIR_FS_CATALOG . DIR_WS_FUNCTIONS;
              $check_directory[] = DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'extra_functions/';
              break;
            case (3): // all admin function files
              $check_directory = array();
              $check_directory[] = DIR_FS_ADMIN . DIR_WS_FUNCTIONS;
              $check_directory[] = DIR_FS_ADMIN . DIR_WS_FUNCTIONS . 'extra_functions/';
              break;
            } // eof: switch

              // Check for new databases and filename in extra_datafiles directory

              zen_display_files();

      break;

    case ('locate_class'):
      if ($configuration_key_lookup == '') {
        $messageStack->add_session(ERROR_CONFIGURATION_KEY_NOT_ENTERED, 'caution');
        zen_redirect(zen_href_link_admin(FILENAME_DEVELOPERS_TOOL_KIT));
      }
      $found = 'false';
      $zv_files_group = $_POST['zv_files'];

          // build filenames to search
          switch ($zv_files_group) {
            case (0): // none
              $filename_listing = '';
              break;
            case (1): // all admin/catalog classes files
              $check_directory = array();
              $check_directory[] = DIR_FS_CATALOG . DIR_WS_CLASSES;
              $check_directory[] = DIR_FS_ADMIN . DIR_WS_CLASSES;
              break;
            case (2): // all catalog classes files
              $check_directory = array();
              $check_directory[] = DIR_FS_CATALOG . DIR_WS_CLASSES;
              break;
            case (3): // all admin function files
              $check_directory = array();
              $check_directory[] = DIR_FS_ADMIN . DIR_WS_CLASSES;
              break;
            } // eof: switch

              // Check for new databases and filename in extra_datafiles directory

              zen_display_files();

      break;

    case ('locate_template'):
      if ($configuration_key_lookup == '') {
        $messageStack->add_session(ERROR_CONFIGURATION_KEY_NOT_ENTERED, 'caution');
        zen_redirect(zen_href_link_admin(FILENAME_DEVELOPERS_TOOL_KIT));
      }
      $found = 'false';
      $zv_files_group = $_POST['zv_files'];

          // build filenames to search
          switch ($zv_files_group) {
            case (0): // none
              $filename_listing = '';
              break;
            case (1): // all template files
              $check_directory = array();
              $check_directory[] = DIR_FS_CATALOG_TEMPLATES . 'template_default/templates' . '/';
              $check_directory[] = DIR_FS_CATALOG_TEMPLATES . 'template_default/sideboxes' . '/';
              $check_directory[] = DIR_FS_CATALOG_MODULES;
              $check_directory[] = DIR_FS_CATALOG_MODULES . 'sideboxes/';

              $check_directory[] = DIR_FS_CATALOG_TEMPLATES . $template_dir . '/templates' . '/';
              $check_directory[] = DIR_FS_CATALOG_TEMPLATES . $template_dir . '/sideboxes' . '/';

              $sub_dir_files = array();
              getDirList(DIR_FS_CATALOG_MODULES . 'pages');

              $check_dir = array_merge($check_directory, $sub_dir_files);
              for ($i = 0, $n = sizeof($check_dir); $i < $n; $i++) {
                $check_directory[] = $check_dir[$i] . '/';
              }

              break;
            case (2): // all /templates files
              $check_directory = array();
              $check_directory[] = DIR_FS_CATALOG_TEMPLATES . 'template_default/templates' . '/';
              $check_directory[] = DIR_FS_CATALOG_TEMPLATES . $template_dir . '/templates' . '/';
              break;
            case (3): // all sideboxes files
              $check_directory = array();
              $check_directory[] = DIR_FS_CATALOG_TEMPLATES . 'template_default/sideboxes' . '/';
              $check_directory[] = DIR_FS_CATALOG_MODULES . 'sideboxes/';
              $check_directory[] = DIR_FS_CATALOG_TEMPLATES . $template_dir . '/sideboxes' . '/';
              break;
            case (4): // all /pages files
              $check_directory = array();
              //$check_directory[] = DIR_FS_CATALOG_MODULES . 'pages/';
              $sub_dir_files = array();
              getDirList(DIR_FS_CATALOG_MODULES . 'pages');

              $check_dir = array_merge($check_directory, $sub_dir_files);
              for ($i = 0, $n = sizeof($check_dir); $i < $n; $i++) {
                $check_directory[] = $check_dir[$i] . '/';
              }

              break;
            } // eof: switch

              // Check for new databases and filename in extra_datafiles directory

              zen_display_files();

      break;


/// all files
    case ('locate_all_files'):
      if ($configuration_key_lookup == '') {
        $messageStack->add_session(ERROR_CONFIGURATION_KEY_NOT_ENTERED, 'caution');
        zen_redirect(zen_href_link_admin(FILENAME_DEVELOPERS_TOOL_KIT));
      }
      $found = 'false';
      $zv_files_group = $_POST['zv_files'];
//echo 'Who am I template ' . $template_dir . ' sess lang ' . $gBitCustomer->getLanguage();
      switch ($zv_files_group) {
        case (0): // none
          $filename_listing = '';
          break;
        case (1): // all
          $filename_listing = '';

          $check_directory = array();

          $sub_dir_files = array();
          getDirList(DIR_FS_CATALOG);
          $sub_dir_files_catalog = $sub_dir_files;

          $sub_dir_files = array();
          getDirList(DIR_FS_ADMIN);
          $sub_dir_files_admin= $sub_dir_files;

          $check_dir = array_merge($sub_dir_files_catalog, $sub_dir_files_admin);
          for ($i = 0, $n = sizeof($check_dir); $i < $n; $i++) {
            $check_directory[] = $check_dir[$i] . '/';
          }
          break;

        case (2): // all catalog
          $filename_listing = '';

          $check_directory = array();

          $sub_dir_files = array();
          getDirList(DIR_FS_CATALOG);
          $sub_dir_files_catalog = $sub_dir_files;

          $check_dir = array_merge($sub_dir_files_catalog);
          for ($i = 0, $n = sizeof($check_dir); $i < $n; $i++) {
            $zv_add_dir= str_replace('//', '/', $check_dir[$i] . '/');
            if (strstr($zv_add_dir, DIR_WS_ADMIN) == '') {
              $check_directory[] = $zv_add_dir;
            }
          }
          break;

        case (3): // all admin
          $filename_listing = '';

          $check_directory = array();

          $sub_dir_files = array();
          getDirList(DIR_FS_ADMIN);
          $sub_dir_files_admin = $sub_dir_files;

          $check_dir = array_merge($sub_dir_files_admin);
          for ($i = 0, $n = sizeof($check_dir); $i < $n; $i++) {
            $check_directory[] = $check_dir[$i] . '/';
          }
          break;
        }
          zen_display_files();

      break;
    } // eof: action

    // if no matches in either databases or selected language directory give an error
    if ($found == 'false') {
      $messageStack->add(ERROR_CONFIGURATION_KEY_NOT_FOUND . ' ' . $configuration_key_lookup, 'caution');
    } else {
      echo '<table width="90%" align="center"><tr><td>' . zen_draw_separator('pixel_black.gif', '100%', '2') . '</td></tr><tr><td>&nbsp;</td></tr></table>' . "\n";
    }

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" src="includes/general.js"></script>

<script type="text/javascript">
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  }
  // -->
</script>
</head>
<body onload="init()">
<!-- header //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
        <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
      </tr>

<?php
if ($show_configuration_info == 'true') {
  $show_configuration_info = 'false';
?>
      <tr><td colspan="2">
        <table border="3" cellspacing="4" cellpadding="4">
          <tr class="infoBoxContent">
            <td colspan="2" class="pageHeading" align="center"><?php echo TABLE_CONFIGURATION_TABLE; ?></td>
          </tr>
          <tr>
            <td class="infoBoxHeading"><?php echo TABLE_TITLE_KEY; ?></td>
            <td class="dataTableHeadingContentWhois"><?php echo $check_configure->fields['configuration_key']; ?></td>
          </tr>
          <tr>
            <td class="infoBoxHeading"><?php echo TABLE_TITLE_TITLE; ?></td>
            <td class="dataTableHeadingContentWhois"><?php echo $check_configure->fields['configuration_title']; ?></td>
          </tr>
          <tr>
            <td class="infoBoxHeading"><?php echo TABLE_TITLE_DESCRIPTION; ?></td>
            <td class="dataTableHeadingContentWhois"><?php echo $check_configure->fields['configuration_description']; ?></td>
          </tr>
<?php
  if ($show_products_type_layout == 'true') {
    $check_configure_group = $gBitDb->Execute("select * from " . TABLE_PRODUCT_TYPES . " where `type_id`='" . $check_configure->fields['product_type_id'] . "'");
  } else {
    $check_configure_group = $gBitDb->Execute("select * from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_id='" . $check_configure->fields['configuration_group_id'] . "'");
  }
?>

<?php
  if ($show_products_type_layout == 'true') {
?>
          <tr>
            <td class="infoBoxHeading"><?php echo TABLE_TITLE_GROUP; ?></td>
            <td class="dataTableHeadingContentWhois"><?php echo 'Product Type Layout'; ?></td>
          </tr>
<?php } else { ?>
          <tr>
            <td class="infoBoxHeading"><?php echo TABLE_TITLE_VALUE; ?></td>
            <td class="dataTableHeadingContentWhois"><?php echo $check_configure->fields['configuration_value']; ?></td>
          </tr>
          <tr>
            <td class="infoBoxHeading"><?php echo TABLE_TITLE_GROUP; ?></td>
            <td class="dataTableHeadingContentWhois">
            <?php
              if ($check_configure_group->fields['configuration_group_id'] == '6') {
                $id_note = TEXT_INFO_CONFIGURATION_HIDDEN;
              } else {
                $id_note = '';
              }
              echo 'ID#' . $check_configure_group->fields['configuration_group_id'] . ' ' . $check_configure_group->fields['configuration_group_title'] . $id_note;
            ?>
            </td>
          </tr>
<?php } ?>
          <tr>
            <td class="main" align="right" valign="middle">
              <?php
                if ($show_products_type_layout == 'false' and ($check_configure->fields['configuration_id'] != 0 and $check_configure->fields['configuration_group_id'] != 6)) {
                  echo '<a href="' . zen_href_link_admin(FILENAME_CONFIGURATION, 'gID=' . $check_configure_group->fields['configuration_group_id'] . '&cID=' . $check_configure->fields['configuration_id']) . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a>';
                } else {
                  $page= '';
                  if (strstr($check_configure->fields['configuration_key'], 'MODULE_SHIPPING')) $page .= 'shipping';
                  if (strstr($check_configure->fields['configuration_key'], 'MODULE_PAYMENT')) $page .= 'payment';
                  if (strstr($check_configure->fields['configuration_key'], 'MODULE_ORDER_TOTAL')) $page .= 'ordertotal';

                  if ($show_products_type_layout == 'true') {
                    echo '<a href="' . zen_href_link_admin(FILENAME_PRODUCT_TYPES) . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a>';
                  } else {
                    if ($page != '') {
                      echo '<a href="' . zen_href_link_admin(FILENAME_MODULES, 'set=' . $page) . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a>';
                    } else {
                      echo TEXT_INFO_NO_EDIT_AVAILABLE . '<br />';
                    }
                  }
                }
              ?>
              </td>
            <td class="main" align="center" valign="middle"><?php echo '<a href="' . zen_href_link_admin(FILENAME_DEVELOPERS_TOOL_KIT) . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>'; ?></td>
          </tr>
        </table>
      </td></tr>
<?php
} else {
?>

<?php
// disabled and here for an example
if (false) {
?>
<!-- bof: update all products price sorter -->
      <tr>
        <td colspan="2"><br /><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" align="left" valign="top"><?php echo TEXT_INFO_PRODUCTS_PRICE_SORTER_UPDATE; ?></td>
            <td class="main" align="right" valign="middle"><?php echo '<a href="' . zen_href_link_admin(FILENAME_DEVELOPERS_TOOL_KIT, 'action=update_all_lowest_purchase_price') . '">' . zen_image_button('button_update.gif', IMAGE_UPDATE) . '</a>'; ?></td>
          </tr>
        </table></td>
      </tr>
<!-- eof: update all products price sorter -->
<?php } ?>

<!-- bof: Locate a configuration constant -->
      <tr>
        <td colspan="2"><br /><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td colspan="3" class="main" align="left" valign="middle"><?php echo TEXT_CONFIGURATION_CONSTANT; ?></td>
          </tr>

          <tr><form name = "locate_configure" action="<?php echo zen_href_link_admin(FILENAME_DEVELOPERS_TOOL_KIT, 'action=locate_configuration', 'NONSSL'); ?>"' method="post">
            <td class="main" align="left" valign="bottom"><?php echo '<strong>' . TEXT_CONFIGURATION_KEY . '</strong>' . '<br />' . zen_draw_input_field('configuration_key'); ?></td>
            <td class="main" align="left" valign="middle">
              <?php
                $za_lookup = array(array('id' => '0', 'text' => TEXT_LOOKUP_NONE),
                                              array('id' => '1', 'text' => TEXT_LANGUAGE_LOOKUP_CURRENT_LANGUAGE),
                                              array('id' => '2', 'text' => TEXT_LANGUAGE_LOOKUP_CURRENT_CATALOG),
                                              array('id' => '3', 'text' => TEXT_LANGUAGE_LOOKUP_CURRENT_CATALOG_TEMPLATE),
                                              array('id' => '4', 'text' => TEXT_LANGUAGE_LOOKUP_CURRENT_ADMIN),
                                              array('id' => '5', 'text' => TEXT_LANGUAGE_LOOKUP_CURRENT_ADMIN_LANGUAGE)
                                                    );
//                                              array('id' => '6', 'text' => TEXT_LANGUAGE_LOOKUP_CURRENT_ALL)

                echo '<strong>' . TEXT_LANGUAGE_LOOKUPS . '</strong>' . '<br />' . zen_draw_pull_down_menu('zv_files', $za_lookup, '0');
              ?>
            </td>
            <td class="main" align="right" valign="bottom"><?php echo zen_image_submit('button_search.gif', IMAGE_SEARCH); ?></td>
          </form></tr>
          <tr>
            <td colspan="4" class="main" align="left" valign="top"><?php echo TEXT_INFO_CONFIGURATION_UPDATE; ?></td>
          </tr>
        </table></td>
      </tr>
<!-- eof: Locate a configuration constant -->


<!-- bof: Locate a function -->
      <tr>
        <td colspan="2"><br /><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td colspan="3" class="main" align="left" valign="middle"><?php echo TEXT_FUNCTION_CONSTANT; ?></td>
          </tr>

          <tr><form name = "locate_function" action="<?php echo zen_href_link_admin(FILENAME_DEVELOPERS_TOOL_KIT, 'action=locate_function', 'NONSSL'); ?>"' method="post">
            <td class="main" align="left" valign="bottom"><?php echo '<strong>' . TEXT_CONFIGURATION_KEY . '</strong>' . '<br />' . zen_draw_input_field('configuration_key'); ?></td>
            <td class="main" align="left" valign="middle">
              <?php
                $za_lookup = array(array('id' => '0', 'text' => TEXT_LOOKUP_NONE),
                                              array('id' => '1', 'text' => TEXT_FUNCTION_LOOKUP_CURRENT),
                                              array('id' => '2', 'text' => TEXT_FUNCTION_LOOKUP_CURRENT_CATALOG),
                                              array('id' => '3', 'text' => TEXT_FUNCTION_LOOKUP_CURRENT_ADMIN)
                                                    );
//                                              array('id' => '6', 'text' => TEXT_LANGUAGE_LOOKUP_CURRENT_ALL)

                echo '<strong>' . TEXT_FUNCTION_LOOKUPS . '</strong>' . '<br />' . zen_draw_pull_down_menu('zv_files', $za_lookup, '0');
              ?>
            </td>
            <td class="main" align="right" valign="bottom"><?php echo zen_image_submit('button_search.gif', IMAGE_SEARCH); ?></td>
          </form></tr>
          <tr>
            <td colspan="4" class="main" align="left" valign="top"><?php echo TEXT_INFO_CONFIGURATION_UPDATE; ?></td>
          </tr>
        </table></td>
      </tr>
<!-- eof: Locate a function -->

<!-- bof: Locate a class -->
      <tr>
        <td colspan="2"><br /><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td colspan="3" class="main" align="left" valign="middle"><?php echo TEXT_CLASS_CONSTANT; ?></td>
          </tr>

          <tr><form name = "locate_class" action="<?php echo zen_href_link_admin(FILENAME_DEVELOPERS_TOOL_KIT, 'action=locate_class', 'NONSSL'); ?>"' method="post">
            <td class="main" align="left" valign="bottom"><?php echo '<strong>' . TEXT_CONFIGURATION_KEY . '</strong>' . '<br />' . zen_draw_input_field('configuration_key'); ?></td>
            <td class="main" align="left" valign="middle">
              <?php
                $za_lookup = array(array('id' => '0', 'text' => TEXT_LOOKUP_NONE),
                                              array('id' => '1', 'text' => TEXT_CLASS_LOOKUP_CURRENT),
                                              array('id' => '2', 'text' => TEXT_CLASS_LOOKUP_CURRENT_CATALOG),
                                              array('id' => '3', 'text' => TEXT_CLASS_LOOKUP_CURRENT_ADMIN)
                                                    );
//                                              array('id' => '6', 'text' => TEXT_LANGUAGE_LOOKUP_CURRENT_ALL)

                echo '<strong>' . TEXT_CLASS_LOOKUPS . '</strong>' . '<br />' . zen_draw_pull_down_menu('zv_files', $za_lookup, '0');
              ?>
            </td>
            <td class="main" align="right" valign="bottom"><?php echo zen_image_submit('button_search.gif', IMAGE_SEARCH); ?></td>
          </form></tr>
          <tr>
            <td colspan="4" class="main" align="left" valign="top"><?php echo TEXT_INFO_CONFIGURATION_UPDATE; ?></td>
          </tr>
        </table></td>
      </tr>
<!-- eof: Locate a class -->

<!-- bof: Locate a template files -->
      <tr>
        <td colspan="2"><br /><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td colspan="3" class="main" align="left" valign="middle"><?php echo TEXT_TEMPLATE_CONSTANT; ?></td>
          </tr>

          <tr><form name = "locate_template" action="<?php echo zen_href_link_admin(FILENAME_DEVELOPERS_TOOL_KIT, 'action=locate_template', 'NONSSL'); ?>"' method="post">
            <td class="main" align="left" valign="bottom"><?php echo '<strong>' . TEXT_CONFIGURATION_KEY . '</strong>' . '<br />' . zen_draw_input_field('configuration_key'); ?></td>
            <td class="main" align="left" valign="middle">
              <?php
                $za_lookup = array(array('id' => '0', 'text' => TEXT_LOOKUP_NONE),
                                              array('id' => '1', 'text' => TEXT_TEMPLATE_LOOKUP_CURRENT),
                                              array('id' => '2', 'text' => TEXT_TEMPLATE_LOOKUP_CURRENT_TEMPLATES),
                                              array('id' => '3', 'text' => TEXT_TEMPLATE_LOOKUP_CURRENT_SIDEBOXES),
                                              array('id' => '4', 'text' => TEXT_TEMPLATE_LOOKUP_CURRENT_PAGES)
                                                    );
//                                              array('id' => '6', 'text' => TEXT_LANGUAGE_LOOKUP_CURRENT_ALL)

                echo '<strong>' . TEXT_TEMPLATE_LOOKUPS . '</strong>' . '<br />' . zen_draw_pull_down_menu('zv_files', $za_lookup, '0');
              ?>
            </td>
            <td class="main" align="right" valign="bottom"><?php echo zen_image_submit('button_search.gif', IMAGE_SEARCH); ?></td>
          </form></tr>
          <tr>
            <td colspan="4" class="main" align="left" valign="top"><?php echo TEXT_INFO_CONFIGURATION_UPDATE; ?></td>
          </tr>
        </table></td>
      </tr>
<!-- eof: Locate template Files -->


<!-- bof: Locate all files -->
      <tr>
        <td colspan="2"><br /><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td colspan="3" class="main" align="left" valign="middle"><?php echo TEXT_ALL_FILES_CONSTANT; ?></td>
          </tr>

          <tr><form name = "locate_all_files" action="<?php echo zen_href_link_admin(FILENAME_DEVELOPERS_TOOL_KIT, 'action=locate_all_files', 'NONSSL'); ?>"' method="post">
            <td class="main" align="left" valign="bottom"><?php echo '<strong>' . TEXT_CONFIGURATION_KEY . '</strong>' . '<br />' . zen_draw_input_field('configuration_key'); ?></td>
            <td class="main" align="left" valign="middle">
              <?php
                $za_lookup = array(array('id' => '0', 'text' => TEXT_LOOKUP_NONE),
                                              array('id' => '1', 'text' => TEXT_ALL_FILES_LOOKUP_CURRENT),
                                              array('id' => '2', 'text' => TEXT_ALL_FILES_LOOKUP_CURRENT_CATALOG),
                                              array('id' => '3', 'text' => TEXT_ALL_FILES_LOOKUP_CURRENT_ADMIN)
                                                    );

                echo '<strong>' . TEXT_ALL_FILES_LOOKUPS . '</strong>' . '<br />' . zen_draw_pull_down_menu('zv_files', $za_lookup, '0');
              ?>
            </td>
            <td class="main" align="right" valign="bottom"><?php echo zen_image_submit('button_search.gif', IMAGE_SEARCH); ?></td>
          </form></tr>
          <tr>
            <td colspan="4" class="main" align="left" valign="top"><?php echo TEXT_INFO_CONFIGURATION_UPDATE; ?></td>
          </tr>
        </table></td>
      </tr>
<!-- eof: Locate all files -->

<?php
} // eof configure
?>
      <tr>
        <td colspan="2"><?php echo '<br />' . zen_draw_separator('pixel_black.gif', '100%', '2'); ?></td>
      </tr>


    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>