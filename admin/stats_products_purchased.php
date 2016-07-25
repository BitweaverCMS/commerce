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

  require('includes/application_top.php');
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
</head>
<body>
<!-- header //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table class="width100p"><tr><td><table class="width100p">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent">#</td>
                <td class="dataTableHeadingContent">ID</td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                <td class="dataTableHeadingContent" align="center">Quantity</td>
                <td class="dataTableHeadingContent" align="center">First Purchase</td>
                <td class="dataTableHeadingContent" align="center">Last Purchase</td>
              </tr>
<?php
	$rows = 0;
	if( isset( $_GET['page'] ) && ($_GET['page'] > 1) ) {
		$rows = $_GET['page'] * MAX_DISPLAY_SEARCH_RESULTS_REPORTS - MAX_DISPLAY_SEARCH_RESULTS_REPORTS;
	}

	// NOTE: The following is much more accurate in output content
	$products_query_raw = "select sum(op.`products_quantity`) as `products_ordered`, MAX(o.`date_purchased`) AS `last_purchased`, MIN(o.`date_purchased`) AS `first_purchased`, op.`products_name`, p.`products_id`, pd.`language_id`, o.`orders_id` from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, ".TABLE_ORDERS." o, ".TABLE_ORDERS_PRODUCTS." op where pd.`products_id` = p.`products_id` and pd.`language_id` = '" . $_SESSION['languages_id']. "' and o.`orders_id` = op.`orders_id` and op.`products_id` = p.`products_id` group by op.`products_name`, p.`products_id`, pd.`language_id`, o.`orders_id` ORDER BY 1 DESC";

	$products_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_REPORTS, $products_query_raw, $products_query_numrows);

	$products = $gBitDb->query($products_query_raw, FALSE, MAX_DISPLAY_SEARCH_RESULTS_REPORTS, $rows);
	while (!$products->EOF) {
		$rows++;

		if (strlen($rows) < 2) {
			$rows = '0' . $rows;
		}
?>
              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href='<?php print CommerceProduct::getDisplayUrlFromId( $products->fields['products_id'] ); ?>'">
				<td class="dataTableContent"><?php echo $rows;?></td>
                <td class="dataTableContent" align="right"><?php echo $products->fields['products_id']; ?>&nbsp;&nbsp;</td>
                <td class="dataTableContent"><?php echo '<a href="' . CommerceProduct::getDisplayUrlFromId( $products->fields['products_id'] ) . '">' . $products->fields['products_name'] . '</a>'; ?></td>
                <td class="dataTableContent" align="center"><?php echo $products->fields['products_ordered']; ?>&nbsp;</td>
                <td class="dataTableContent" align="center"><?php echo substr( $products->fields['first_purchased'], 0, strpos( $products->fields['first_purchased'], ' ' ) ); ?>&nbsp;</td>
                <td class="dataTableContent" align="center"><?php if( $products->fields['first_purchased'] != $products->fields['last_purchased'] ) { echo substr( $products->fields['last_purchased'], 0, strpos( $products->fields['last_purchased'], ' ' ) ); } ?>&nbsp;</td>
              </tr>
<?php
    $products->MoveNext();
  }
?>
            </table></td>
          </tr>
          <tr>
            <td colspan="3"><table>
              <tr>
                <td class="smallText" valign="top"><?php echo $products_split->display_count($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_REPORTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></td>
                <td class="smallText" align="right"><?php echo $products_split->display_links($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_REPORTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?>&nbsp;</td>
              </tr>
            </table></td>
          </tr>
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
