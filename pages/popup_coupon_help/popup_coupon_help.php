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
// $Id: popup_coupon_help.php,v 1.4 2006/12/19 00:11:37 spiderr Exp $
//
?>
<body onload="resize();">
<?php
  $coupon = $gBitDb->Execute("select * from " . TABLE_COUPONS . " where `coupon_id` = '" . $_GET['cID'] . "'");
  $coupon_desc = $gBitDb->Execute("select * from " . TABLE_COUPONS_DESCRIPTION . " where `coupon_id` = '" . $_GET['cID'] . "' and `language_id` = '" . $_SESSION['languages_id'] . "'");
  $text_coupon_help = TEXT_COUPON_HELP_HEADER;
  $text_coupon_help .= sprintf(TEXT_COUPON_HELP_NAME, $coupon_desc->fields['coupon_name']);
  if (zen_not_null($coupon_desc->fields['coupon_description'])) $text_coupon_help .= sprintf(TEXT_COUPON_HELP_DESC, $coupon_desc->fields['coupon_description']);
  $coupon_amount = $coupon->fields['coupon_amount'];
  switch ($coupon->fields['coupon_type']) {
    case 'F':
    $text_coupon_help .= sprintf(TEXT_COUPON_HELP_FIXED, $currencies->format($coupon->fields['coupon_amount']));
    break;
    case 'P':
    $text_coupon_help .= sprintf(TEXT_COUPON_HELP_FIXED, number_format($coupon->fields['coupon_amount'],2). '%');
    break;
    case 'S':
    $text_coupon_help .= TEXT_COUPON_HELP_FREESHIP;
    break;
    default:
  }
  if ($coupon->fields['coupon_minimum_order'] > 0 ) $text_coupon_help .= sprintf(TEXT_COUPON_HELP_MINORDER, $currencies->format($coupon->fields['coupon_minimum_order']));
  $text_coupon_help .= sprintf(TEXT_COUPON_HELP_DATE, zen_date_short($coupon->fields['coupon_start_date']),zen_date_short($coupon->fields['coupon_expire_date']));
  $text_coupon_help .= '<b>' . TEXT_COUPON_HELP_RESTRICT . '</b>';
  $text_coupon_help .= '<br><br>' .  TEXT_COUPON_HELP_CATEGORIES;
  $get_result=$gBitDb->Execute("select `restrict_to_categories` from " . TABLE_COUPONS . " where `coupon_id` ='".$_GET['cID']."'");

  $cat_ids = split("[,]", $get_result->fields['restrict_to_categories']);
  for ($i = 0; $i < count($cat_ids); $i++) {
    $result = $gBitDb->query("SELECT * FROM " . TABLE_CATEGORIES . " cat, " . TABLE_CATEGORIES_DESCRIPTION . " catd  WHERE cat.`categories_id` = catd.`categories_id` and catd.`language_id` = ? and cat.`categories_id`=?", array( $_SESSION['languages_id'], (int)$cat_ids[$i] ) );
    if ($result->RecordCount() > 0) {
    $cats .= '<br>' . $result->fields["categories_name"];
    }
  }
  if ($cats=='') $cats = '<br>NONE';
  $text_coupon_help .= $cats;
  $text_coupon_help .= '<br><br>' .  TEXT_COUPON_HELP_PRODUCTS;
  $get_result=$gBitDb->Execute("select restrict_to_products from " . TABLE_COUPONS . "  where coupon_id='".$_GET['cID']."'");

  $pr_ids = split("[,]", $get_result->fields['restrict_to_products']);
  for ($i = 0; $i < count($pr_ids); $i++) {
    $result = $gBitDb->query("SELECT * FROM " . TABLE_PRODUCTS ." pr, " . TABLE_PRODUCTS_DESCRIPTION . " prd WHERE pr.`products_id` = prd.`products_id` and prd.`language_id` = ? AND pr.`products_id` = ?", array( $_SESSION['languages_id'], (int)$pr_ids[$i] ) );
    if ($result->RecordCount() > 0 )  {
      $prods .= '<br>' . $result->fields["products_name"];
    }
  }
  if ($prods=='') $prods = '<br>NONE';
  $text_coupon_help .= $prods . '<br /><br />' . TEXT_COUPON_GV_RESTRICTION;
  echo $text_coupon_help;
?>
<p class="smallText" align="right"><?php echo '<a href="javascript:window.close()">' . TEXT_CLOSE_WINDOW . '</a>'; ?></p>
</body>
