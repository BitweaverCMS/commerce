<?php
// :vim:tabstop=4:
// +--------------------------------------------------------------------+
// | Copyright (c) 2005-2022 bitcommerce.org							|
// | http://www.bitcommerce.org											|
// | This source file is subject to version 2.0 of the GPL license		|
// +--------------------------------------------------------------------+
// | Portions Copyright (c) 2003 The zen-cart developers				|
// | Portions Copyright (c) 2003 osCommerce								|
// +--------------------------------------------------------------------+
//

require('includes/application_top.php');
require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceReview.php' );

$title = HEADING_TITLE;
$mid = 'bitpackage:bitcommerce/admin_reviews_list.tpl';

$getAction = !empty( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';

switch ($getAction) {
	case 'new':
	case 'edit':
		$mid = 'bitpackage:bitcommerce/admin_reviews_edit.tpl';
		break;
	default:
		if( $reviewsList = CommerceReview::getList( $_REQUEST ) ) {
			$gBitSmarty->assignByRef( 'reviewsList', $reviewsList );
		}

		if( isset( $_REQUEST['listInfo'] ) ) {
			$_REQUEST['listInfo']['block_pages'] = 3;
			$_REQUEST['listInfo']['item_name'] = 'coupons';
			$gBitSmarty->assignByRef( 'listInfo', $_REQUEST['listInfo'] );
		}

		$mid = 'bitpackage:bitcommerce/admin_reviews_list.tpl';
		break;
}

$gBitSmarty->display( $mid, tra( 'Customer Reviews' ) );

require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); 
require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); 

exit;

$status_filter = (isset($_GET['status']) ? $_GET['status'] : '');
$status_list[] = array('id' => 1, 'text' => TEXT_PENDING_APPROVAL);
$status_list[] = array('id' => 2, 'text' => TEXT_APPROVED);

$rID = BitBase::getIdParameter( $_GET, 'rID' );
$pageOffset = BitBase::getParameter( $_GET, 'page', 1 );

switch ($getAction) {
	case 'setflag':
		zen_set_reviews_status($_GET['id'], $_GET['flag']);

		zen_redirect(zen_href_link_admin(FILENAME_REVIEWS, (isset($pageOffset) ? 'page=' . $pageOffset . '&' : '') . 'rID=' . $_GET['id'], 'NONSSL'));
		break;
	case 'update':
		$reviews_rating = zen_db_prepare_input($_POST['reviews_rating']);
		$reviews_text = zen_db_prepare_input($_POST['reviews_text']);

		$gBitDb->Execute("update " . TABLE_REVIEWS . " set reviews_rating = '" . zen_db_input($reviews_rating) . "', `last_modified` = now() where reviews_id = '" . (int)$reviews_id . "'");

		zen_redirect(zen_href_link_admin(FILENAME_REVIEWS, 'page=' . $pageOffset . '&rID=' . $rID));
		break;
	case 'deleteconfirm':
		// demo active test
		if (zen_admin_demo()) {
			$_GET['action']= '';
			$messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
			zen_redirect(zen_href_link_admin(FILENAME_REVIEWS, 'page=' . $pageOffset));
		}
		$gBitDb->Execute("delete from " . TABLE_REVIEWS . " where reviews_id = ?", array( $rID ) );

		zen_redirect(zen_href_link_admin(FILENAME_REVIEWS, 'page=' . $pageOffset));
		break;
}

require(DIR_FS_ADMIN_INCLUDES . 'header.php'); 

if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
	echo '<a href="' . zen_href_link_admin(FILENAME_REVIEWS) . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>&nbsp;&nbsp;';
}

echo HEADING_TITLE_SEARCH_DETAIL . ' ' . zen_draw_input_field('search');
if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
	$keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
	echo '<br/ >' . TEXT_INFO_SEARCH_DETAIL_FILTER . $keywords;
}
?>
<?php 
	echo zen_draw_form_admin('status', FILENAME_REVIEWS, '', 'get', '', true); 
	echo HEADING_TITLE_STATUS . ' ' . zen_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_STATUS)), $status_list), $status_filter, 'onChange="this.form.submit();"');
?>
	</form>
<?php
if ($getAction == 'edit') {

	$reviews = $gBitDb->Execute("select r.`reviews_id`, r.`products_id`, r.reviewer_name, r.`date_reviewed`,
											r.`last_modified`, r.reviews_read, r.reviews_text, r.reviews_rating
									 from " . TABLE_REVIEWS . " r
									 where r.`reviews_id` = '" . (int)$rID . "'");

	$products = $gBitDb->Execute("SELECT `products_image` FROM " . TABLE_PRODUCTS . " WHERE `products_id` = ?", array( (int)$reviews->fields['products_id'] ) );

	$products_name = $gBitDb->Execute("select `products_name`
										   from " . TABLE_PRODUCTS_DESCRIPTION . "
										   where `products_id` = '" . (int)$reviews->fields['products_id'] . "'
										   and `language_id` = '" . (int)$_SESSION['languages_id'] . "'");

	$rInfo_array = array_merge($reviews->fields, $products->fields, $products_name->fields);
	$rInfo = new objectInfo($rInfo_array);
?>
		<div><?php echo zen_draw_form_admin('review', FILENAME_REVIEWS, 'page=' . $pageOffset . '&rID=' . $rID . '&action=preview'); ?>
			<b><?php echo ENTRY_PRODUCT; ?></b> <?php echo $reviewHash['products_name']; ?><br><b><?php echo ENTRY_FROM; ?></b> <?php echo $reviewHash['reviewer_name']; ?><br><br><b><?php echo ENTRY_DATE; ?></b> <?php echo zen_date_short($reviewHash['date_reviewed']); ?></td>
					<?php echo zen_image(DIR_WS_CATALOG_IMAGES . $reviewHash['products_image'], $reviewHash['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"'); ?>
		</div>
		<div><?php echo ENTRY_REVIEW; ?></b><br><br><?php echo zen_draw_textarea_field('reviews_text', 'soft', '70', '15', stripslashes($reviewHash['reviews_text'])); ?></div>
			<div><?php echo ENTRY_REVIEW_TEXT; ?></div>
<table>
		</tr>
		<tr>
			<td class="main"><b><?php echo ENTRY_RATING; ?></b>&nbsp;<?php echo TEXT_BAD; ?>&nbsp;<?php for ($i=1; $i<=5; $i++) echo zen_draw_radio_field('reviews_rating', $i, '', $reviewHash['reviews_rating']) . '&nbsp;'; echo TEXT_GOOD; ?></td>
		</tr>
		<tr>
			<td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
		</tr>
		<tr>
			<td align="right" class="main"><?php echo zen_draw_hidden_field('reviews_id', $reviewHash['reviews_id']) . zen_draw_hidden_field('products_id', $reviewHash['products_id']) . zen_draw_hidden_field('reviewer_name', $reviewHash['reviewer_name']) . zen_draw_hidden_field('products_name', $reviewHash['products_name']) . zen_draw_hidden_field('products_image', $reviewHash['products_image']) . zen_draw_hidden_field('date_reviewed', $reviewHash['date_reviewed']) . zen_image_submit('button_preview.gif', IMAGE_PREVIEW) . ' <a href="' . zen_href_link_admin(FILENAME_REVIEWS, 'page=' . $pageOffset . '&rID=' . $rID) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
		</form></tr>
<?php
} elseif ($getAction == 'preview') {
	if (zen_not_null($_POST)) {
		$reviewHash = $_POST;
		$reviewHash['reviews_id'] = $_REQUEST['rID'];
	} else {
		$reviewHash = $gBitDb->getRow("select * from " . TABLE_REVIEWS . " cr WHERE cr.`reviews_id` = ?", array( (int)$rID ) );
	}
	$reviewProduct = NULL;
	if( BitBase::verifyIdParameter( $reviewHash, 'products_id' ) ) {
		$reviewProduct = CommerceProduct::getCommerceObject( array( 'products_id' => $reviewHash['products_id'] ) );
	}
?>
		<tr><?php echo zen_draw_form_admin('update', FILENAME_REVIEWS, 'page=' . $pageOffset . '&rID=' . $reviewHash['reviews_id'] . '&action=update', 'post', 'enctype="multipart/form-data"'); ?>
			<td><table border="0" width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td class="main" valign="top"><b><?php echo ENTRY_PRODUCT; ?></b> <?php if( is_object( $reviewProduct ) ) { echo $reviewProduct->getTitle(); } ?><br><b><?php echo ENTRY_FROM; ?></b> <?php echo $reviewHash['reviewer_name']; ?><br><br><b><?php echo ENTRY_DATE; ?></b> <?php echo zen_date_short($reviewHash['date_reviewed']); ?></td>
					<td class="main" align="right" valign="top"><?php if( is_object( $reviewProduct ) && $productImageUrl = $reviewProduct->getThumbnailUrl() ) { echo '<img class="img-responsive" href="'. $productImageUrl . '">'; }?> </td>
				</tr>
			</table>
		</tr>
		<tr>
			<td><table witdh="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td valign="top" class="main"><b><?php echo ENTRY_REVIEW; ?></b><div><?php if( $reviewHash['format_guid'] = 'text/html' ) { echo $reviewHash['reviews_text']; } else { echo nl2br(zen_db_output(zen_break_string($reviewHash['reviews_text'], 15))); } ?></div></td>
				</tr>
			</table></td>
		</tr>
		<tr>
			<td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
		</tr>
		<tr>
			<td class="main"><b><?php echo ENTRY_RATING; ?></b>&nbsp;<?php echo zen_image(DIR_WS_TEMPLATE_IMAGES . 'stars_' . $reviewHash['reviews_rating'] . '.gif', sprintf(TEXT_OF_5_STARS, $reviewHash['reviews_rating'])); ?>&nbsp;<small>[<?php echo sprintf(TEXT_OF_5_STARS, $reviewHash['reviews_rating']); ?>]</small></td>
		</tr>
</table>
<?php
} else {
?>
	<table class="table">
		<thead>
		<tr>
			<th><?php echo TABLE_HEADING_PRODUCTS; ?></th>
			<th align="right"><?php echo TABLE_HEADING_RATING; ?> + <?php echo TABLE_HEADING_CUSTOMER_NAME; ?></th>
			<th align="right"><?php echo TABLE_HEADING_DATE_ADDED; ?></th>
			<th align="center"><?php echo TABLE_HEADING_STATUS; ?></th>
			<th align="right"><?php echo TABLE_HEADING_ACTION; ?></th>
		</tr>
		</thead>
<?php

// create search filter
	$search = '';
	$bindVars = array();

	if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
		$keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
		$search = " and r.`reviewer_name` like '%" . $keywords . "%' or r.`reviews_text` like '%" . $keywords . "%' or pd.`products_name` like '%" . $keywords . "%' or pd.`products_description` like '%" . $keywords . "%' or p.`products_model` like '%" . $keywords . "%'";
	}

	if ($status_filter !='' && $status_filter >0) {
		$search .= " and r.`status` = ?";
		$bindVars[] = (int)$status_filter-1;
	}

	$order_by = " order by pd.`products_name`";

	$reviews_query_raw = (  "select r.*, pd.*, p.* from " . TABLE_REVIEWS . " r LEFT JOIN " . TABLE_PRODUCTS . " p on (p.`products_id`= r.`products_id`) LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON (r.`products_id` = pd.`products_id` AND pd.`language_id` ='" . (int)$_SESSION['languages_id'] . "') WHERE r.`products_id` = p.`products_id` " . $search . $order_by ." LIMIT 20 ");

	$reviews_query_raw = "select `reviews_id` as `hash_key`, r.*, length(r.`reviews_text`) as `reviews_text_size` from " . TABLE_REVIEWS . " r order by `date_reviewed` DESC";
	$reviews = $gBitDb->query($reviews_query_raw, $bindVars, MAX_DISPLAY_SEARCH_RESULTS, $pageOffset );
	while (!$reviews->EOF) {
		$reviewHash = $reviews->fields;
		if( !empty( $reviewHash['products_id'] ) ) {
			$reviewHash['average_rating'] = $gBitDb->getOne( "SELECT (AVG(`reviews_rating`) / 5 * 100) AS average_rating
												 FROM " . TABLE_REVIEWS . "
												 WHERE `products_id` = ?", array( (int)$reviewHash['products_id'] ) );

			$review_info = array_merge($reviews_text, $reviews_average, $products_name);
		}

			echo '			  <tr id="defaultSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_REVIEWS, 'page=' . $pageOffset . '&rID=' . $reviewHash['reviews_id'] . '&action=preview') . '\'">' . "\n";
?>
						<td><?php echo '<a href="' . zen_href_link_admin(FILENAME_REVIEWS, 'page=' . $pageOffset . '&rID=' . $reviews->fields['reviews_id'] . '&action=preview') . '">' . zen_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW) . '</a>&nbsp;' . zen_get_products_name($reviews->fields['products_id']); ?></td>
						<td><span class="badge"><?php echo $reviews->fields['reviews_rating']; ?> <i class="fa fal fa-star"></i></span> <?php echo $reviews->fields['reviewer_name']; ?></td>
						<td align="right"><?php echo zen_date_short($reviews->fields['date_reviewed']); ?></td>
						<td align="center">
<?php
		if ($reviews->fields['status'] == '1') {
			echo '<a href="' . zen_href_link_admin(FILENAME_REVIEWS, 'action=setflag&flag=0&id=' . $reviews->fields['reviews_id'], 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '</a>';
		} else {
			echo '<a href="' . zen_href_link_admin(FILENAME_REVIEWS, 'action=setflag&flag=1&id=' . $reviews->fields['reviews_id'], 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '</a>';
		}
?>
						</td>
						<td class="dataTableContent" align="right"><?php if( !empty( $_GET['rID'] ) && $reviewHash['reviews_id'] == $_GET['rID'] ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . zen_href_link_admin(FILENAME_REVIEWS, 'page=' . $pageOffset . '&rID=' . $reviews->fields['reviews_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
					  </tr>
<?php
		$reviews->MoveNext();
	}
?>
					</table>
<?php
	$heading = array();
	$contents = array();

	if( !empty( $getAction ) ) {
	switch ($getAction) {
		case 'delete':
			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_REVIEW . '</b>');
eb( "NOT IMPLEMENTED" );

			$contents = array('form' => zen_draw_form_admin('reviews', FILENAME_REVIEWS, 'page=' . $pageOffset . '&rID=' . $reviewHash['reviews_id'] . '&action=deleteconfirm'));
			$contents[] = array('text' => TEXT_INFO_DELETE_REVIEW_INTRO);
			$contents[] = array('text' => '<br><b>' . $rInfo->products_name . '</b>');
			$contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_href_link_admin(FILENAME_REVIEWS, 'page=' . $pageOffset . '&rID=' . $rInfo->reviews_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
			break;
		default:
eb( $getAction, "NOT IMPLEMENTED" );
		if (isset($rInfo) && is_object($rInfo)) {
			$heading[] = array('text' => '<b>' . $rInfo->products_name . '</b>');

			$contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_REVIEWS, 'page=' . $pageOffset . '&rID=' . $rInfo->reviews_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link_admin(FILENAME_REVIEWS, 'page=' . $pageOffset . '&rID=' . $rInfo->reviews_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
			$contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($rInfo->date_reviewed));
			if (zen_not_null($rInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($rInfo->last_modified));
			$contents[] = array('text' => '<br>' . zen_info_image($rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT));
			$contents[] = array('text' => '<br>' . TEXT_INFO_REVIEW_AUTHOR . ' ' . $rInfo->reviewer_name);
			$contents[] = array('text' => TEXT_INFO_REVIEW_RATING . ' ' . zen_image(DIR_WS_TEMPLATE_IMAGES . 'stars_' . $rInfo->reviews_rating . '.gif'));
			$contents[] = array('text' => TEXT_INFO_REVIEW_READ . ' ' . $rInfo->reviews_read);
			$contents[] = array('text' => '<br>' . TEXT_INFO_REVIEW_SIZE . ' ' . $rInfo->reviews_text_size . ' bytes');
			$contents[] = array('text' => '<br>' . TEXT_INFO_PRODUCTS_AVERAGE_RATING . ' ' . number_format($rInfo->average_rating, 2) . '%');
		}
			break;
	}
	}

	if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
		$box = new box;
		echo $box->infoBox($heading, $contents);
	}

}
?>

<?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>

<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>
