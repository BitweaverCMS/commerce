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
//  $Id: option_values.php,v 1.9 2005/09/28 22:38:57 spiderr Exp $
//
?>
<?php
  require('includes/application_top.php');

  // verify option values exist
  $chk_option_values = $db->Execute("select * from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where `products_options_values_id` != '0' and `language_id` ='" . $_SESSION['languages_id'] . "'", 1);
  if ($chk_option_values->RecordCount() < 1) {
    $messageStack->add_session(ERROR_DEFINE_OPTION_VALUES, 'caution');
    zen_redirect(zen_href_link_admin(FILENAME_OPTIONS_VALUES_MANAGER));
  }

  
  $currencies = new currencies();

  switch($_GET['action']) {
    case ('update_sort_order'):
      foreach($_POST['options_values_new_sort_order'] as $id => $new_sort_order) {
        $row++;

        $db->Execute("UPDATE " . TABLE_PRODUCTS_OPTIONS_VALUES . " set products_ov_sort_order= " . $_POST['options_values_new_sort_order'][$id] . " where `products_options_values_id` = $id");
      }
      $messageStack->add_session(SUCCESS_OPTION_VALUES_SORT_ORDER . ' ' . zen_options_name($_GET['options_id']), 'success');
      $_GET['action']='';
      zen_redirect(zen_href_link_admin(FILENAME_PRODUCTS_OPTIONS_VALUES));
      break;
// update by product
    case ('update_product'):
      $messageStack->add_session(SUCCESS_PRODUCT_UPDATE_SORT . $_POST['products_update_id'] . ' ' . zen_get_products_name($_POST['products_update_id'], $_SESSION['languages_id']), 'success');
      zen_update_attributes_products_option_values_sort_order($_POST['products_update_id']);
      $action='';
      zen_redirect(zen_href_link_admin(FILENAME_PRODUCTS_OPTIONS_VALUES));
      break;
// update by category
    case ('update_categories_attributes'):
      $all_products_attributes= $db->Execute("select ptoc.`products_id`, pa.products_attributes_id from " .
      TABLE_PRODUCTS_TO_CATEGORIES . " ptoc, " .
      TABLE_PRODUCTS_ATTRIBUTES . " pa " . "
      where ptoc.`categories_id` = '" . $_POST['categories_update_id'] . "' and
      pa.`products_id` = ptoc.`products_id`"
      );
      while (!$all_products_attributes->EOF) {
        $count++;
        $product_id_updated .= ' - ' . $all_products_attributes->fields['products_id'] . ':' . $all_products_attributes->fields['products_attributes_id'];
        zen_update_attributes_products_option_values_sort_order($all_products_attributes->fields['products_id']);
        $all_products_attributes->MoveNext();
      }
      $messageStack->add_session(SUCCESS_CATEGORIES_UPDATE_SORT . $_POST['categories_update_id'] . ' ' . zen_get_category_name($_POST['categories_update_id'], $_SESSION['languages_id']), 'success');
      $action='';
      zen_redirect(zen_href_link_admin(FILENAME_PRODUCTS_OPTIONS_VALUES));
      break;
// update all products in catalog
    case ('update_all_products_attributes_sort_order'):
      $all_products_attributes= $db->Execute("select p.`products_id`, pa.products_attributes_id from " .
      TABLE_PRODUCTS . " p, " .
      TABLE_PRODUCTS_ATTRIBUTES . " pa " . "
      where p.`products_id`= pa.`products_id`"
      );
      while (!$all_products_attributes->EOF) {
        $count++;
        zen_update_attributes_products_option_values_sort_order($all_products_attributes->fields['products_id']);
        $all_products_attributes->MoveNext();
      }
      $messageStack->add_session(SUCCESS_PRODUCT_UPDATE_SORT_ALL, 'success');
      $action='';
      zen_redirect(zen_href_link_admin(FILENAME_PRODUCTS_OPTIONS_VALUES));
      break;
  } // switch
?>
<!doctsype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
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
<?php
if ($_GET['options_id']=='') {
?>
  <table border="1" cellspacing="1" cellpadding="2" bordercolor="gray">
    <tr class="dataTableHeadingRow">
      <td colspan="3" align="center" class="dataTableHeadingContent"><?php echo TEXT_UPDATE_OPTION_VALUES; ?></td>
    </tr>
    <tr class="dataTableHeadingRow">
<?php echo zen_draw_form('quick_jump', FILENAME_PRODUCTS_OPTIONS_VALUES, '', 'get'); ?>
      <td class="dataTableHeadingContent"> <?php echo TEXT_SELECT_OPTION; ?> </td>
      <td class="dataTableHeadingContent">&nbsp;<select name="options_id">
<?php
        $options_values = $db->Execute("select * from " . TABLE_PRODUCTS_OPTIONS . " where `language_id` = '" . $_SESSION['languages_id'] . "' and `products_options_name` !='' and `products_options_type` !='" . PRODUCTS_OPTIONS_TYPE_TEXT . "' and `products_options_type` !='" . PRODUCTS_OPTIONS_TYPE_FILE . "' order by `products_options_name`");
        while(!$options_values->EOF) {
            echo "\n" . '<option name="' . $options_values->fields['products_options_name'] . '" value="' . $options_values->fields['products_options_id'] . '">' . $options_values->fields['products_options_name'] . '</option>';
            $options_values->MoveNext();
        }
?>
      </select>&nbsp;</td>
      <td align="center" class="dataTableHeadingContent">&nbsp;<?php echo zen_image_submit('button_edit.gif', IMAGE_EDIT); ?>&nbsp;</td>
      </form>
    </tr>
  </table>
<?php
} else {
?>
  <table border="1" cellspacing="3" cellpadding="2" bordercolor="gray">
    <tr class="dataTableHeadingRow">
      <td colspan="3" class="dataTableHeadingContent" align="center"><?php echo TEXT_EDIT_OPTION_NAME; ?> <?php echo zen_options_name($_GET['options_id']); ?></td>
    </tr>
<?php // echo zen_draw_form('update', zen_href_link_admin(FILENAME_PRODUCTS_OPTIONS_VALUES, 'action=update_sort_order&options_id=' . $_GET['options_id'], 'NONSSL'), '', 'post'); ?>
<?php echo zen_draw_form('update', FILENAME_PRODUCTS_OPTIONS_VALUES, 'action=update_sort_order&options_id=' . $_GET['options_id'], 'post'); ?>
<?php
    echo '    <tr class="dataTableHeadingRow"><td class="dataTableHeadingContent">Option ID</td><td class="dataTableHeadingContent">Option Value Name</td><td class="dataTableHeadingContent">Sort Order</td></tr><tr>';

    $row = $db->Execute("SELECT * FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov, " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " povtpo WHERE povtpo.`products_options_values_id` = pov.`products_options_values_id` and povtpo.`products_options_id`='" . $_GET['options_id'] . "' and pov.`language_id` = '" . $_SESSION['languages_id'] . "' ORDER BY pov.`products_ov_sort_order`, pov.`products_options_values_id`");

    if (!$row->EOF) {
       $option_values_exist = true;
        while (!$row->EOF) {
            echo '      <td align="right" class="dataTableContent">' . $row->fields["products_options_values_id"] . '</td>' . "\n";
            echo '      <td class="dataTableContent">' . $row->fields["products_options_values_name"] . '</td>' . "\n";
            echo '      <td class="dataTableContent" align="center">' . "<input type=\"text\" name=\"options_values_new_sort_order[".$row->fields['products_options_values_id']."]\" value={$row->fields['products_ov_sort_order']} size=\"4\">" . '</td>' . "\n";
            echo '    </tr>' . "\n";
          $row->MoveNext();
        }
//        while($row = mysql_fetch_array($result));
    } else {
       $option_values_exist = false;
       echo '      <td colspan="3" height="50" align="center" valign="middle" class="dataTableContent">' . TEXT_NO_OPTION_VALUE . zen_options_name($_GET['options_id']) . '</td>' . "\n";
    }
?>
    <tr class="dataTableHeadingRow">
      <?php
        if ($option_values_exist == true) {
      ?>
      <td colspan="2" height="50" align="center" valign="middle" class="dataTableHeadingContent">
        <input type="submit" value="<?php echo TEXT_UPDATE_SUBMIT; ?>">
      </td>
      <?php
        }
      ?>
      <td colspan="<?php echo ($option_values_exist == true ? '1' : '3'); ?>"height="50" align="center" valign="middle" class="dataTableHeadingContent"><?php echo '<a href="' . zen_href_link_admin(FILENAME_PRODUCTS_OPTIONS_VALUES) . '">'; ?><?php echo zen_image_button('button_cancel.gif', IMAGE_CANCEL); ?></a></td>
    </tr>
  </form>
  </table>
<?php
} // which table
?>
<?php
//////////////////////////////////////////
// BOF: Update by Product, Category or All products
// only show when not updating Option Value Sort Order
if (empty($_GET['options_id'])) {

// select from all product with attributes
?>
      <tr>
        <td colspan="2" class="main" align="left"><br /><?php echo TEXT_UPDATE_SORT_ORDERS_OPTIONS; ?></td>
      </tr>

      <tr>
        <td colspan="2" class="main" align="left"><br /><?php echo TEXT_UPDATE_SORT_ORDERS_OPTIONS_PRODUCTS; ?></td>
      </tr>
      <tr><form name="update_product_attributes" <?php echo 'action="' . zen_href_link_admin(FILENAME_PRODUCTS_OPTIONS_VALUES, 'action=update_product') . '"'; ?> method="post"><?php echo zen_draw_hidden_field('products_update_id', $_GET['products_update_id']); ?>
        <td colspan="2"><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo zen_draw_products_pull_down_attributes('products_update_id'); ?></td>
            <td class="main" align="right" valign="top"><?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE); ?></td>
          </tr>
        </table></td>
      </form></tr>

<?php
// select from all categories with products with attributes
?>
      <tr>
        <td colspan="2" class="main" align="left"><br /><?php echo TEXT_UPDATE_SORT_ORDERS_OPTIONS_CATEGORIES; ?></td>
      </tr>
      <tr><form name="update_categories_attributes" <?php echo 'action="' . zen_href_link_admin(FILENAME_PRODUCTS_OPTIONS_VALUES, 'action=update_categories_attributes') . '"'; ?> method="post"><?php echo zen_draw_hidden_field('categories_update_id', $_GET['categories_update_id']); ?>
        <td colspan="2"><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" align="left" valign="top"><?php echo zen_draw_products_pull_down_categories_attributes('categories_update_id'); ?></td>
            <td class="main" align="right" valign="middle"><?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE); ?></td>
          </tr>
        </table></td>
      </form></tr>

<?php
// select the catalog and update all products with attributes
?>
      <tr>
        <td colspan="2"><br /><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" align="left" valign="top"><?php echo TEXT_INFO_ATTRIBUTES_FEATURES_UPDATES; ?></td>
            <td class="main" align="right" valign="middle"><?php echo '<a href="' . zen_href_link_admin(FILENAME_PRODUCTS_OPTIONS_VALUES, 'action=update_all_products_attributes_sort_order') . '">' . zen_image_button('button_update.gif', IMAGE_UPDATE) . '</a>'; ?></td>
          </tr>
        </table></td>
      </tr>
<?php
}
// EOF: Update by Product, Category or All products
//////////////////////////////////////////
?>

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
