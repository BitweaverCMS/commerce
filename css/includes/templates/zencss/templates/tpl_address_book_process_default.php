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
// $Id: tpl_address_book_process_default.php,v 1.1 2005/07/05 05:59:28 bitweaver Exp $
//
?>
<?php if (!isset($_GET['delete'])) echo zen_draw_form('addressbook', zen_href_link(FILENAME_ADDRESS_BOOK_PROCESS, (isset($_GET['edit']) ? 'edit=' . $_GET['edit'] : ''), 'SSL'), 'post', 'onsubmit="return check_form(addressbook);"'); ?>
<?php if (isset($_GET['edit'])) { echo HEADING_TITLE_MODIFY_ENTRY; } elseif (isset($_GET['delete'])) { echo HEADING_TITLE_DELETE_ENTRY; } else { echo HEADING_TITLE_ADD_ENTRY; } ?>
<?php
  if ($messageStack->size('addressbook') > 0) {
?>
<?php echo $messageStack->output('addressbook'); ?> 
<?php
  }

  if (isset($_GET['delete'])) {
?>
<?php echo DELETE_ADDRESS_DESCRIPTION; ?> <?php echo SELECTED_ADDRESS; ?><br />
<?php echo zen_image(DIR_WS_TEMPLATE_IMAGES . 'arrow_south_east.gif'); ?> <?php echo zen_address_label($_SESSION['customer_id'], $_GET['delete'], true, ' ', '<br />'); ?> 
<?php echo '<a href="' . zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL') . '">' . zen_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?> 
<?php echo '<a href="' . zen_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'delete=' . $_GET['delete'] . '&action=deleteconfirm', 'SSL') . '">' . zen_image_button('button_delete.gif', IMAGE_BUTTON_DELETE) . '</a>'; ?> 
<?php
  } else {
?>
<?php require(DIR_WS_MODULES . 'address_book_details.php'); ?>
<?php
    if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
?>
<?php echo '<a href="' . zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL') . '">' . zen_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?> 
<?php echo zen_draw_hidden_field('action', 'update') . zen_draw_hidden_field('edit', $_GET['edit']) . zen_image_submit('button_update.gif', IMAGE_BUTTON_UPDATE); ?> 
<?php
    } else {
      if (sizeof($_SESSION['navigation']->snapshot) > 0) {
        $back_link = zen_href_link('index.php', zen_array_to_string($_SESSION['navigation']->snapshot['get'], array(zen_session_name())), $_SESSION['navigation']->snapshot['mode']);
      } else {
        $back_link = zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL');
      }
?>
<?php echo '<a href="' . $back_link . '">' . zen_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?> 
<?php echo zen_draw_hidden_field('action', 'process') . zen_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?> 
<?php
    }
  }
?>
<?php if (!isset($_GET['delete'])) echo '</form>'; ?>
