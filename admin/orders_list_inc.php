<table border="0" style="width:100%" cellspacing="2" cellpadding="2">
  <tr>
  
<?php if (empty($action)) { ?>
<!-- search -->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
         <tr><?php echo zen_draw_form_admin('search', FILENAME_ORDERS, '', 'get', '', true); ?>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td colspan="2" class="smallText" align="right">
<?php
// show reset search
  if ((isset($_GET['search']) && zen_not_null($_GET['search'])) or $_GET['cID'] !='') {
    echo '<a href="' . zen_href_link_admin(FILENAME_ORDERS, '', 'NONSSL') . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a><br />';
  }
?>
<?php
  echo HEADING_TITLE_SEARCH_DETAIL . ' ' . zen_draw_input_field('search');
  if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
    $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
    echo '<br/ >' . TEXT_INFO_SEARCH_DETAIL_FILTER . $keywords;
  }
?>
            </td>
          </form></tr>
        </table></td>
      </tr>
<!-- search -->
<?php } ?>



	<tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td align="right"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr><?php echo zen_draw_form_admin('orders', FILENAME_ORDERS, '', 'get', '', true); ?>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_SEARCH . ' ' . zen_draw_input_field('oID', '', 'size="12"') . zen_draw_hidden_field('action', 'edit'); ?></td>
              </form></tr>
              <tr><?php echo zen_draw_form_admin('status', FILENAME_ORDERS, '', 'get', '', true); ?>
                <td class="smallText" align="right">
                  <?php
                    echo HEADING_TITLE_STATUS . ' ' . zen_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_ORDERS)), $orders_statuses), $_GET['status'], 'onChange="this.form.submit();"');
                  ?>
                </td>
              </form></tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="smallText"><?php echo TEXT_LEGEND . ' ' . zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10) . ' ' . TEXT_BILLING_SHIPPING_MISMATCH; ?>
          </td>
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
<?php
// Sort Listing
          switch ($_GET['list_order']) {
              case "id-asc":
              $disp_order = "c.`customers_id`";
              break;
              case "firstname":
              $disp_order = "c.`customers_firstname`";
              break;
              case "firstname-desc":
              $disp_order = "c.`customers_firstname` DESC";
              break;
              case "lastname":
              $disp_order = "c.`customers_lastname`, c.`customers_firstname`";
              break;
              case "lastname-desc":
              $disp_order = "c.`customers_lastname` DESC, c.`customers_firstname`";
              break;
              case "company":
              $disp_order = "a.`entry_company`";
              break;
              case "company-desc":
              $disp_order = "a.`entry_company` DESC";
              break;
              default:
              $disp_order = "c.`customers_id` DESC";
          }
?>
                <th align="center"><?php echo TABLE_HEADING_ORDERS_ID; ?></th>
                <th align="left" width="50"><?php echo TABLE_HEADING_PAYMENT_METHOD; ?></th>
                <th><?php echo TABLE_HEADING_CUSTOMERS; ?></th>
                <th align="right"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></th>
                <th align="center"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></th>
                <th align="right"><?php echo TABLE_HEADING_STATUS; ?></th>
                <th align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
              </tr>

<?php
// create search filter
  $search = '';
  if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
    $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
    $search = " and (o.`customers_city` like '%" . $keywords . "%' or o.`customers_postcode` like '%" . $keywords . "%' or o.`date_purchased` like '%" . $keywords . "%' or o.`billing_name` like '%" . $keywords . "%' or o.`billing_company` like '%" . $keywords . "%' or o.`billing_street_address` like '%" . $keywords . "%' or o.`delivery_city` like '%" . $keywords . "%' or o.`delivery_postcode` like '%" . $keywords . "%' or o.`delivery_name` like '%" . $keywords . "%' or o.`delivery_company` like '%" . $keywords . "%' or o.`delivery_street_address` like '%" . $keywords . "%' or o.`billing_city` like '%" . $keywords . "%' or o.`billing_postcode` like '%" . $keywords . "%' or o.`customers_email_address` like '%" . $keywords . "%' or o.`customers_name` like '%" . $keywords . "%' or o.`customers_company` like '%" . $keywords . "%' or o.`customers_street_address`  like '%" . $keywords . "%' or o.`customers_telephone` like '%" . $keywords . "%' or o.`ip_address`  like '%" . $keywords . "%')";
  }
?>
<?php
    $new_fields = ", o.`customers_street_address`, o.`delivery_name`, o.`delivery_street_address`, o.`billing_name`, o.`billing_street_address`, o.`payment_module_code`, o.`shipping_module_code`, o.`ip_address` ";
    if (isset($_GET['cID'])) {
      $cID = zen_db_prepare_input($_GET['cID']);
      $orders_query_raw = "select o.`orders_id`, o.`customers_id`, o.`customers_name`, o.`customers_id`, o.`payment_method`, o.`date_purchased`, o.`last_modified`, o.`currency`, o.`currency_value`, s.`orders_status_name`, ot.`text` as `order_total`" . $new_fields . " from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.`orders_id` = ot.`orders_id`), " . TABLE_ORDERS_STATUS . " s where o.`customers_id` = '" . (int)$cID . "' and o.`orders_status` = s.`orders_status_id` and s.`language_id` = '" . (int)$_SESSION['languages_id'] . "' and ot.`class` = 'ot_total' order by orders_id DESC";
    } elseif ($_GET['status'] != '') {
      $status = zen_db_prepare_input($_GET['status']);
      $orders_query_raw = "select o.`orders_id`, o.`customers_id`, o.`customers_name`, o.`payment_method`, o.`date_purchased`, o.`last_modified`, o.`currency`, o.`currency_value`, s.`orders_status_name`, ot.`text` as `order_total`" . $new_fields . " from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.`orders_id` = ot.`orders_id`), " . TABLE_ORDERS_STATUS . " s where o.`orders_status` = s.`orders_status_id` and s.`language_id` = '" . (int)$_SESSION['languages_id'] . "' and s.`orders_status_id` = '" . (int)$status . "' and ot.`class` = 'ot_total'  " . $search . " order by o.`orders_id` DESC";
    } else {
      $orders_query_raw = "select o.`orders_id`, o.`customers_id`, o.`customers_name`, o.`payment_method`, o.`date_purchased`, o.`last_modified`, o.`currency`, o.`currency_value`, s.`orders_status_name`, ot.`text` as `order_total`" . $new_fields . " from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.`orders_id` = ot.`orders_id`), " . TABLE_ORDERS_STATUS . " s where o.`orders_status` = s.`orders_status_id` and s.`language_id` = '" . (int)$_SESSION['languages_id'] . "' and ot.`class` = 'ot_total'  " . $search . " order by o.`orders_id` DESC";
    }
    $orders_query_numrows = '';
    $orders_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_ORDERS, $orders_query_raw, $orders_query_numrows);
    $orders = $gBitDb->Execute($orders_query_raw);
    while (!$orders->EOF) {
    if ((!isset($_GET['oID']) || (isset($_GET['oID']) && ($_GET['oID'] == $orders->fields['orders_id']))) && !isset($oInfo)) {
        $oInfo = new objectInfo($orders->fields);
      }

      if (isset($oInfo) && is_object($oInfo) && ($orders->fields['orders_id'] == $oInfo->orders_id)) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit', 'NONSSL') . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('oID')) . 'oID=' . $orders->fields['orders_id'], 'NONSSL') . '\'">' . "\n";
      }

      $show_difference = '';
      if (($orders->fields['delivery_name'] != $orders->fields['billing_name'] and $orders->fields['delivery_name'] != '')) {
        $show_difference = zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10) . '&nbsp;';
      }
      if (($orders->fields['delivery_street_address'] != $orders->fields['billing_street_address'] and $orders->fields['delivery_street_address'] != '')) {
        $show_difference = zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10) . '&nbsp;';
      }
      $show_payment_type = $orders->fields['payment_module_code'] . '<br />' . $orders->fields['shipping_module_code'];
?>
                <td class="dataTableContent" align="right"><?php echo $show_difference . $orders->fields['orders_id']; ?></td>
                <td class="dataTableContent" align="left" width="50"><?php echo $show_payment_type; ?></td>
                <td class="dataTableContent"><?php echo '<a href="' . zen_href_link_admin(FILENAME_CUSTOMERS, 'cID=' . $orders->fields['customers_id'], 'NONSSL') . '">' . zen_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW . ' ' . TABLE_HEADING_CUSTOMERS) . '</a>&nbsp;' . $orders->fields['customers_name']; ?></td>
                <td class="dataTableContent" align="right"><?php echo strip_tags($orders->fields['order_total']); ?></td>
                <td class="dataTableContent" align="center"><?php echo zen_datetime_short($orders->fields['date_purchased']); ?></td>
                <td class="dataTableContent" align="right"><?php echo $orders->fields['orders_status_name']; ?></td>

                <td class="dataTableContent" align="right"><?php if (isset($oInfo) && is_object($oInfo) && ($orders->fields['orders_id'] == $oInfo->orders_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('oID')) . 'oID=' . $orders->fields['orders_id'], 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
      $orders->MoveNext();
    }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $orders_split->display_count($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_ORDERS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></td>
                    <td class="smallText" align="right"><?php echo $orders_split->display_links($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_ORDERS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], zen_get_all_get_params(array('page', 'oID', 'action'))); ?></td>
                  </tr>
<?php
  if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
?>
                  <tr>
                    <td class="smallText" align="right" colspan="2">
                      <?php
                        echo '<a href="' . zen_href_link_admin(FILENAME_ORDERS, '', 'NONSSL') . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>';
                        if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
                          $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
                          echo '<br/ >' . TEXT_INFO_SEARCH_DETAIL_FILTER . $keywords;
                        }
                      ?>
                    </td>
                  </tr>
<?php
  }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_ORDER . '</strong>');

      $contents = array('form' => zen_draw_form_admin('orders', FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=deleteconfirm', 'post', '', true));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO . '<br /><br /><strong>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</strong>');
      $contents[] = array('text' => '<br />' . zen_draw_checkbox_field('restock') . ' ' . TEXT_INFO_RESTOCK_PRODUCT_QUANTITY);
      $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id, 'NONSSL') . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($oInfo) && is_object($oInfo)) {
        $heading[] = array('text' => '<strong>[' . $oInfo->orders_id . ']&nbsp;&nbsp;' . zen_datetime_short($oInfo->date_purchased) . '</strong>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit', 'NONSSL') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=delete', 'NONSSL') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_ORDERS_INVOICE, 'oID=' . $oInfo->orders_id) . '" TARGET="_blank">' . zen_image_button('button_invoice.gif', IMAGE_ORDERS_INVOICE) . '</a> <a href="' . zen_href_link_admin(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $oInfo->orders_id) . '" TARGET="_blank">' . zen_image_button('button_packingslip.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a>');
        $contents[] = array('text' => '<br />' . TEXT_DATE_ORDER_CREATED . ' ' . zen_date_short($oInfo->date_purchased));
        if (zen_not_null($oInfo->last_modified)) 
			$contents[] = array('text' => TEXT_DATE_ORDER_LAST_MODIFIED . ' ' . zen_date_short($oInfo->last_modified));
        $contents[] = array('text' => '<br />' . TEXT_INFO_PAYMENT_METHOD . ' '  . $oInfo->payment_method);
        $contents[] = array('text' => TEXT_INFO_IP_ADDRESS . ' ' . $oInfo->ip_address);

// check if order has open gv
        $gv_check = $gBitDb->getOne("select `order_id`, `unique_id`
                                  from " . TABLE_COUPON_GV_QUEUE ."
                                  where `order_id` = '" . $oInfo->orders_id . "' and `release_flag` ='N'");
        if( $gv_check ) {
          $goto_gv = '<a href="' . zen_href_link_admin(FILENAME_GV_QUEUE, 'order=' . $oInfo->orders_id) . '">' . zen_image_button('button_gift_queue.gif',IMAGE_GIFT_QUEUE) . '</a>';
          $contents[] = array('text' => '<br />' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif','','100%','3'));
          $contents[] = array('align' => 'center', 'text' => $goto_gv);
        }
      }

// indicate if comments exist
		if ( !empty($oInfo->orders_id) ) {
			$orders_history_query = $gBitDb->Execute("select `orders_status_id`, `date_added`, `customer_notified`, `comments` from " . TABLE_ORDERS_STATUS_HISTORY . " where `orders_id` = '" . $oInfo->orders_id . "' and `comments` !='" . "'" );
			if ($orders_history_query->RecordCount() > 0) {
				$contents[] = array('align' => 'left', 'text' => '<br />' . TABLE_HEADING_COMMENTS);
			}
		}

      $contents[] = array('text' => '<br />' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif','','100%','3'));
      $order = new order($oInfo->orders_id);
      $contents[] = array('text' => 'Products Ordered: ' . sizeof($order->products) );
      for ($i=0; $i<sizeof($order->products); $i++) {
        $contents[] = array('text' => $order->products[$i]['quantity'] . '&nbsp;x&nbsp;' . $order->products[$i]['name']);

        if (sizeof($order->products[$i]['attributes']) > 0) {
          for ($j=0; $j<sizeof($order->products[$i]['attributes']); $j++) {
            $contents[] = array('text' => '&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['orders_value'] . '</i></nobr>' );
          }
        }
        if ($i > MAX_DISPLAY_RESULTS_ORDERS_DETAILS_LISTING and MAX_DISPLAY_RESULTS_ORDERS_DETAILS_LISTING != 0) {
          $contents[] = array('align' => 'left', 'text' => TEXT_MORE);
          break;
        }
      }

      if (sizeof($order->products) > 0) {
        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit', 'NONSSL') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a>');
      }
      break;
  }

  if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
