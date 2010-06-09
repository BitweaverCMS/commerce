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
// $Id$
//
?>
<?php
  if (!isset($_GET['main_page']) || !zen_not_null($_GET['main_page'])) $_GET['main_page'] = 'index';
  if (!isset($_GET['disp_order'])) {
    $_GET['disp_order'] = $disp_order_default;
    $disp_order = $disp_order_default;
  } else {
    $disp_order = $_GET['disp_order'];
  }
echo zen_draw_form('sorter', zen_href_link($_GET['main_page']), 'get');
echo zen_draw_hidden_field('main_page', $_GET['main_page']);

// NOTE: to remove a sort order option add an HTML comment around the option to be removed
?>
  <tr>
    <td class="main" align="right" colspan="2"><?php echo TEXT_INFO_SORT_BY; ?>
    <select name="disp_order" onChange="this.form.submit();">
<?php if ($disp_order != $disp_order_default) { ?>
    <option value="<?php echo $disp_order_default; ?>" <?php echo ($disp_order == $disp_order_default ? 'selected="selected"' : ''); ?>><?php echo PULL_DOWN_ALL_RESET; ?></option>
<?php } // reset to store default ?>
    <option value="1" <?php echo ($disp_order == '1' ? 'selected="selected"' : ''); ?>><?php echo TEXT_INFO_SORT_BY_PRODUCTS_NAME; ?></option>
    <option value="2" <?php echo ($disp_order == '2' ? 'selected="selected"' : ''); ?>><?php echo TEXT_INFO_SORT_BY_PRODUCTS_NAME_DESC; ?></option>
    <option value="3" <?php echo ($disp_order == '3' ? 'selected="selected"' : ''); ?>><?php echo TEXT_INFO_SORT_BY_PRODUCTS_PRICE; ?></option>
    <option value="4" <?php echo ($disp_order == '4' ? 'selected="selected"' : ''); ?>><?php echo TEXT_INFO_SORT_BY_PRODUCTS_PRICE_DESC; ?></option>
    <option value="5" <?php echo ($disp_order == '5' ? 'selected="selected"' : ''); ?>><?php echo TEXT_INFO_SORT_BY_PRODUCTS_MODEL; ?></option>
    <option value="6" <?php echo ($disp_order == '6' ? 'selected="selected"' : ''); ?>><?php echo TEXT_INFO_SORT_BY_PRODUCTS_DATE_DESC; ?></option>
    <option value="7" <?php echo ($disp_order == '7' ? 'selected="selected"' : ''); ?>><?php echo TEXT_INFO_SORT_BY_PRODUCTS_DATE; ?></option>
    </select></td>
  </tr></form>

<?php
  switch (true) {
    case ($_GET['disp_order'] == 0):
      // reset and let reset continue
      $_GET['disp_order'] = $disp_order_default;
      $disp_order = $disp_order_default;
    case ($_GET['disp_order'] == 1):
      $order_by = " order by pd.`products_name`";
      break;
    case ($_GET['disp_order'] == 2):
      $order_by = " order by pd.`products_name` DESC";
      break;
    case ($_GET['disp_order'] == 3):
      $order_by = " order by p.`lowest_purchase_price`, pd.`products_name`";
      break;
    case ($_GET['disp_order'] == 4):
      $order_by = " order by p.`lowest_purchase_price` DESC, pd.`products_name`";
      break;
    case ($_GET['disp_order'] == 5):
      $order_by = " order by p.`products_model`";
      break;
    case ($_GET['disp_order'] == 6):
      $order_by = " order by p.`products_date_added` DESC, pd.`products_name`";
      break;
    case ($_GET['disp_order'] == 7):
      $order_by = " order by p.`products_date_added`, pd.`products_name`";
      break;
    default:
      $order_by = " order by p.`products_sort_order`";
      break;
  }
?>
