{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
	<tr>
<!-- body_text //-->
		<td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
			<tr>
				<td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
					<tr>
						<td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
						<td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td><table class="data">
					<tr>
						<td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
							<tr class="dataTableHeadingRow">
								<th>{tr}Senders Name{/tr}</th>
								<th>{tr}Emailed To{/tr}</th>
								<th>{tr}Amount{/tr}</th>
								<th>{tr}Code{/tr}</th>
								<th>{tr}Date Sent{/tr}</th>
								<th>{tr}Redeemed{/tr}</th>
							</tr>
						{foreach from=$couponList item=coupon}
							<tr>
								<td class="dataTableContent">{$coupon.sent_firstname} {$coupon.sent_lastname}</td>
								<td class="dataTableContent">{$coupon.emailed_to}</td>
								<td class="dataTableContent">{$gCommerceCurrencies->format($coupon.coupon_amount)}</td>
								<td class="dataTableContent">{$coupon.coupon_code}</td>
								<td class="dataTableContent">{$coupon.date_sent|zen_date_short}</td>
								<td>{if $coupon.redeem_date}{$coupon.redeem_date|zen_date_short} by {displayname user_id=$coupon.customer_id} {if $coupon.order_id} on <a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$coupon.order_id}">Order {$coupon.order_id}</a>{/if}{else}<div class="floaticon">{biticon iname="edit-delete" iexplain="Delete Gift Certificate"}</a>{/if}</td>
							</tr>
						{/foreach}
							<tr>
								<td colspan="6"><table border="0" width="100%" cellspacing="0" cellpadding="2">
									<tr>
										<td class="smallText" valign="top"><?php echo $gv_split->display_count($gv_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_GIFT_VOUCHERS); ?></td>
										<td class="smallText" align="right"><?php echo $gv_split->display_links($gv_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
									</tr>
								</table></td>
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

