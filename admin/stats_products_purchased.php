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
//  $Id: stats_products_purchased.php,v 1.9 2005/09/28 22:38:58 spiderr Exp $
//

  require('includes/application_top.php');
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
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_NUMBER; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_PURCHASED; ?>&nbsp;</td>
              </tr>
<?php
  if (isset($_GET['page']) && ($_GET['page'] > 1)) $rows = $_GET['page'] * MAX_DISPLAY_SEARCH_RESULTS_REPORTS - MAX_DISPLAY_SEARCH_RESULTS_REPORTS;
// The following OLD query only considers the "products_ordered" value from the products table.
// The new query subsequent to this uses real order info from the orders_products table.
// Thus this older query is somewhat deprecated
//  $products_query_raw = "select p.`products_id`, p.products_ordered, pd.`products_name` from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.`products_id` = p.`products_id` and pd.`language_id` = '" . $_SESSION['languages_id']. "' and p.products_ordered > 0 group by pd.`products_id` order by p.products_ordered DESC, pd.`products_name`";

// NOTE: The following is much more accurate in output content
$products_query_raw = "select sum(op.`products_quantity`) as `products_ordered`, op.`products_name`, p.`products_id`, pd.`language_id`, o.`orders_id` from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, ".TABLE_ORDERS." o, ".TABLE_ORDERS_PRODUCTS." op where pd.`products_id` = p.`products_id` and pd.`language_id` = '" . $_SESSION['languages_id']. "' and o.`orders_id` = op.`orders_id` and op.`products_id` = p.`products_id` group by op.`products_name`, p.`products_id`, pd.`language_id`, o.`orders_id` ORDER BY 1 DESC";

//  $offset = splitPageResults::splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_REPORTS, $products_query_raw, $products_query_numrows);
  $products_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_REPORTS, $products_query_raw, $products_query_numrows);

  $rows = 0;
  $products = $db->query($products_query_raw, NULL, MAX_DISPLAY_SEARCH_RESULTS_REPORTS, $offset);
  while (!$products->EOF) {
    $rows++;

    if (strlen($rows) < 2) {
      $rows = '0' . $rows;
    }
    $cPath = zen_get_product_path($products->fields['products_id']);
?>
              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href='<?php echo zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $products->fields['products_id'] . '&page='); ?>'">
                <td class="dataTableContent" align="right"><?php echo $products->fields['products_id']; ?>&nbsp;&nbsp;</td>
                <td class="dataTableContent"><?php echo '<a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $products->fields['products_id'] . '&page=') . '">' . $products->fields['products_name'] . '</a>'; ?></td>
                <td class="dataTableContent" align="center"><?php echo $products->fields['products_ordered']; ?>&nbsp;</td>
              </tr>
<?php
    $products->MoveNext();
  }
?>
            </table></td>
          </tr>
          <tr>
            <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
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
