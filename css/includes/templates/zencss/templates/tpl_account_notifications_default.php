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
// $Id: tpl_account_notifications_default.php,v 1.1 2005/07/05 05:59:28 bitweaver Exp $
//
?>
<?php echo zen_draw_form('account_notifications', zen_href_link(FILENAME_ACCOUNT_NOTIFICATIONS, '', 'SSL')) . zen_draw_hidden_field('action', 'process'); ?> 
<h1><?php echo HEADING_TITLE; ?></h1>
<?php echo MY_NOTIFICATIONS_DESCRIPTION; ?>
<?php echo GLOBAL_NOTIFICATIONS_TITLE; ?> 
<table border="0" width="100%" cellspacing="1" cellpadding="1">
  <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="checkBox('product_global')"> 
    <td width="15" valign="bottom"><?php echo zen_draw_checkbox_field('product_global', '1', (($global->fields['global_product_notifications'] == '1') ? true : false), 'onclick="checkBox(\'product_global\')"'); ?></td>
    <td class="plainBoxHeading"><?php echo GLOBAL_NOTIFICATIONS_TITLE; ?></td>
  </tr>
</table>
<?php echo GLOBAL_NOTIFICATIONS_DESCRIPTION; ?> 
<?php
  if ($global->fields['global_product_notifications'] != '1') {
?>
<?php echo NOTIFICATIONS_TITLE; ?> 
<table border="0" width="100%" cellspacing="1" cellpadding="1">
  <?php
    $products_check_query = "select count(*) as total 
                             from   " . TABLE_PRODUCTS_NOTIFICATIONS . " 
                             where  customers_id = '" . (int)$_SESSION['customer_id'] . "'";

    $products_check = $db->Execute($products_check_query);

    if ($products_check->fields['total'] > 0) {
?>
  <tr> 
    <td colspan="2"><?php echo NOTIFICATIONS_DESCRIPTION; ?></td>
  </tr>
  <?php
      $counter = 0;
      $products_query = "select pd.products_id, pd.products_name 
                         from   " . TABLE_PRODUCTS_DESCRIPTION . " pd, 
                                " . TABLE_PRODUCTS_NOTIFICATIONS . " pn 
                         where  pn.customers_id = '" . (int)$_SESSION['customer_id'] . "' 
                         and    pn.products_id = pd.products_id 
                         and    pd.language_id = '" . (int)$_SESSION['languages_id'] . "' 
                         order by pd.products_name";

      $products = $db->Execute($products_query);
      while (!$products->EOF) {
?>
  <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="checkBox('products[<?php echo $counter; ?>]')"> 
    <td width="15" valign="bottom"><?php echo zen_draw_checkbox_field('products[' . $counter . ']', $products->fields['products_id'], true, 'onclick="checkBox(\'products[' . $counter . ']\')"'); ?></td>
    <td><b><?php echo $products->fields['products_name']; ?></b></td>
  </tr>

<?php
        $counter++;
        $products->MoveNext();
      }
    } else {
?>
<?php echo NOTIFICATIONS_NON_EXISTING; ?> 
<?php
    }
?>
</table>
<?php
  }
?>

<?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . zen_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?> 
<?php echo zen_image_submit('button_update.gif', IMAGE_BUTTON_UPDATE); ?> </form> 
