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
//  $Id: option_name.php,v 1.10 2006/12/19 00:11:28 spiderr Exp $
//
?>
<?php
  require('includes/application_top.php');

  // verify option names and values
  $chk_option_names = $gBitDb->getOne("select * from " . TABLE_PRODUCTS_OPTIONS .
		" where `language_id` ='" . $_SESSION['languages_id'] . "'");
  if ( !$chk_option_names ) {
    $messageStack->add_session(ERROR_DEFINE_OPTION_NAMES, 'caution');
    zen_redirect(zen_href_link_admin(FILENAME_OPTIONS_NAME_MANAGER));
  }

//  if (!$lng_id) $_GET['lng_id'] = $_SESSION['languages_id'];
//  if (!$_GET['lng_id']) $_GET['lng_id'] = $_SESSION['languages_id'];

  $languages_array = array();
  $languages = zen_get_languages();
  $_GET['lng_exists'] = false;
  for ($i=0; $i<sizeof($languages); $i++) {
    if ($languages[$i]['id'] == $_GET['lng_id']) $_GET['lng_exists'] = true;

    $languages_array[] = array('id' => $languages[$i]['id'],
                               'text' => $languages[$i]['name']);
  }
  if (!$_GET['lng_exists']==true) $_GET['lng_id'] = $_SESSION['languages_id'];


if ($_GET['action'] == "update_sort_order") {
    foreach($_POST['products_options_sort_order'] as $id => $new_sort_order) {
      $row++;
      $gBitDb->Execute("UPDATE " . TABLE_PRODUCTS_OPTIONS . " set `products_options_sort_order` = " . $_POST['products_options_sort_order'][$id] . " where `products_options_id` = $id and `language_id` =" . $_GET['lng_id']);
    }
        $messageStack->add_session(SUCCESS_OPTION_SORT_ORDER, 'success');
        $_GET['action']='';
        zen_redirect(zen_href_link_admin(FILENAME_PRODUCTS_OPTIONS_NAME, 'options_id=' . $options_id . '&lng_id=' . $_GET['lng_id']));
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
<body onload="init()" marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <table border="1" cellspacing="3" cellpadding="2" bordercolor="gray">
            <tr class="dataTableHeadingRow">
              <td colspan="<?php echo ($_GET['lng_id']==$_SESSION['languages_id'] ? '5' : '8'); ?>" align="center" class="dataTableHeadingContent"><?php echo TEXT_EDIT_ALL; ?></td>
            </tr>
            <tr class="dataTableHeadingRow">
              <td colspan="3" align="center" class="dataTableHeadingContent"><?php echo ($_GET['lng_id'] !=$_SESSION['languages_id'] ? 'Current Language' : '&nbsp;'); ?></td>
              <?php echo zen_draw_form_admin('lng', FILENAME_PRODUCTS_OPTIONS_NAME, '', 'get'); ?>
              <td colspan="<?php echo ($_GET['lng_id']==$_SESSION['languages_id'] ? '2' : '5'); ?>" class="dataTableHeadingContent" align="center" valign="top"><?php echo  TEXT_SELECTED_LANGUAGE . zen_get_language_icon($_GET['lng_id']); ?>&nbsp;&nbsp;&nbsp;<?php echo zen_draw_pull_down_menu('lng_id', $languages_array, $_GET['lng_id'], 'onChange="this.form.submit();"'); ?></td>
              </form>
            </tr>

            <form name = "update" action="<?php echo zen_href_link_admin(FILENAME_PRODUCTS_OPTIONS_NAME, 'action=update_sort_order&lng_id=' . $_GET['lng_id'], 'NONSSL'); ?>"' method="post"
<?php
    echo '<tr class="dataTableHeadingRow">';

    if ($_GET['lng_id'] != $_SESSION['languages_id']) {
    echo '  <td class="dataTableHeadingContent">&nbsp;</td>
            <td class="dataTableHeadingContent">' . TEXT_CURRENT_NAME . '</td>
            <td class="dataTableHeadingContent">' . TEXT_SORT_ORDER . '</td>';
    }
    echo '  <td class="dataTableHeadingContent">&nbsp;</td>
            <td class="dataTableHeadingContent">' . TEXT_OPTION_ID . '</td>
            <td class="dataTableHeadingContent">' . TEXT_OPTION_TYPE . '</td>
            <td class="dataTableHeadingContent">' . TEXT_OPTION_NAME . '</td>
            <td class="dataTableHeadingContent">' . TEXT_SORT_ORDER . '</td>
          </tr>
          <tr>';
    $row = $gBitDb->Execute("SELECT * FROM " . TABLE_PRODUCTS_OPTIONS . " WHERE `language_id` = '" . $_GET['lng_id'] . "' ORDER BY `products_options_sort_order`, `products_options_id`");
    while (!$row->EOF) {
      switch (true) {
        case ($row->fields['products_options_type']==PRODUCTS_OPTIONS_TYPE_RADIO):
          $the_attributes_type= '(RADIO)';
          break;
        case ($row->fields['products_options_type']==PRODUCTS_OPTIONS_TYPE_TEXT):
          $the_attributes_type= '(TEXT)';
          break;
        case ($row->fields['products_options_type']==PRODUCTS_OPTIONS_TYPE_FILE):
          $the_attributes_type= '(FILE)';
          break;
        case ($row->fields['products_options_type']==PRODUCTS_OPTIONS_TYPE_CHECKBOX):
          $the_attributes_type= '(CHECKBOX)';
          break;
        default:
          $the_attributes_type='(DROPDOWN)';
          break;
      }

    if ($_GET['lng_id'] !=$_SESSION['languages_id']) {
            echo '<td align="center" class="dataTableContent">' . zen_get_language_icon($_SESSION['languages_id']) . '</td>' . "\n";
            echo '<td align="left" class="dataTableContent">' . zen_get_option_name_language($row->fields["products_options_id"], $_SESSION['languages_id']) . '</td>' . "\n";
            echo '<td align="right" class="dataTableContent">' . zen_get_option_name_language_sort_order($row->fields["products_options_id"], $_SESSION['languages_id']) . '&nbsp;&nbsp;</td>' . "\n";
    }
            echo '<td align="center" class="dataTableContent">' . zen_get_language_icon($_GET['lng_id']) . '</td>' . "\n";
            echo '<td align="right" class="dataTableContent">' . $row->fields["products_options_id"] . '</td>' . "\n";
            echo '<td class="dataTableContent" align="center">' . $the_attributes_type . '</td>' . "\n";
            echo '<td class="dataTableContent">' . $row->fields["products_options_name"] . '</td>' . "\n";
            echo '<td class="dataTableContent" align="center">' . "<input type=\"text\" name=\"products_options_sort_order[".$row->fields['products_options_id']."]\" value={$row->fields['products_options_sort_order']} size=\"4\">" . '</td>' . "\n";
            echo '</tr>' . "\n";
      $row->MoveNext();
    }
?>
            <tr class="dataTableHeadingRow">
              <td colspan="<?php echo ($_GET['lng_id']==$_SESSION['languages_id'] ? '1' : '4'); ?>" height="50" align="center" valign="middle" class="dataTableHeadingContent">&nbsp;</td>
              <td colspan="4" height="50" align="center" valign="middle" class="dataTableHeadingContent"><input type="submit" value="Update Sort Order"></td>
            </tr>
            </form>
          </table>
        </table></td>
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
