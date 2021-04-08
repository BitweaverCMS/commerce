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

require_once(DIR_WS_FUNCTIONS . 'localization.php');

global $currencies;

$activeCurrency = FALSE;
if( $activeCode = BitBase::getParameter( $_REQUEST, 'code' ) ) {
	$activeCurrency = BitBase::getParameter( $currencies->currencies, $activeCode );
}

if( $action = BitBase::getParameter( $_GET, 'action' ) ) {
	switch ($action) {
		case 'insert':
		case 'save':
			$currencies->store( $_REQUEST );

			if (isset($_POST['default']) && ($_POST['default'] == 'on')) {
				$gCommerceSystem->storeConfig( 'DEFAULT_CURRENCY', $code );
			}

			zen_redirect(zen_href_link_admin(FILENAME_CURRENCIES, 'action=edit&code=' . $activeCode));
			break;

		case 'deleteconfirm':
			if( !$currencies->expungeCurrency( $activeCode ) ) {
				$messageStack->add(ERROR_REMOVE_DEFAULT_CURRENCY, 'error');
			}
			zen_redirect(zen_href_link_admin(FILENAME_CURRENCIES, 'page=' . $_GET['page']));
			break;

		case 'api':
			$gCommerceSystem->storeConfig( 'CURRENCY_EXCHANGERATESAPI_KEY', BitBase::getParameter( $_REQUEST, 'exchangeratesapi_key' ) );
			break;

		case 'bulk':
			$currencies->bulkImport( $_REQUEST['bulk_currencies'] );
			break;

		case 'update':
			$server_used = CURRENCY_SERVER_PRIMARY;
			$output = currency_update_quotes();
			foreach( $output as $result ) {
				$messageStack->add_session($result['message'], $result['result']);
			}
			zen_redirect( zen_href_link_admin(FILENAME_CURRENCIES, 'code=' . $activeCode ) );
			break;
	}
}
?>

<div class="row">
	<div class="col-md-8">
		<table class="table table-hover">
			<tr class="dataTableHeadingRow">
				<th><?php echo TABLE_HEADING_CURRENCY_CODES; ?></th>
				<th><?php echo TABLE_HEADING_CURRENCY_NAME; ?></th>
				<th class="text-right"><?php echo TABLE_HEADING_CURRENCY_VALUE; ?></th>
				<th></th>
			</tr>
<?php
	foreach( $currencies->currencies as $curCode => $curHash ) {
		$rowClass = '';
		if( $activeCode == $curCode ) {
			$rowClass = 'info';
		} elseif( $curCode == DEFAULT_CURRENCY ) {
			$rowClass = 'success';
		}
?>
			<tr class="<?php echo $rowClass; ?>" >
				<td><?php echo $curCode; ?></td>
				<td><?php echo $curHash['title'] . ($curCode==DEFAULT_CURRENCY ?' (' . TEXT_DEFAULT . ')':'')?></td>
				<td class="text-right"><?php echo $currencies->format($curHash['currency_value'], false, $curCode ); ?></td>
				<td class="text-center">
					<a href="<?php echo zen_href_link_admin(FILENAME_CURRENCIES, 'action=edit&code=' . $curCode)?>" class="icon"><i class="icon-pencil"></i></a>
					<a href="<?php echo zen_href_link_admin(FILENAME_CURRENCIES, 'action=delete&code=' . $curCode)?>" class="icon"><i class="icon-trash"></i></a>
				</td>
			</tr>
<?php
	}
?>
				</td>
			</tr>
		</table>

<?php
	if (empty($action)) {
?>
			<div>
				<?php if (CURRENCY_SERVER_PRIMARY) { echo '<a href="' . zen_href_link_admin(FILENAME_CURRENCIES, 'action=update') . '" class="btn btn-default">' . tra( 'Update Exchange Rate' ) . '</a>'; } ?>
				<?php echo '<a href="' . zen_href_link_admin(FILENAME_CURRENCIES, 'action=new') . '" class="btn btn-default">' . tra( 'New Currency' ) . '</a>'; ?>
			</div>
<?php
	}
?>
<hr>



<?=zen_draw_form_admin('currencies', FILENAME_CURRENCIES, 'action=bulk')?>
<fieldset>
	<legend>Bulk Import Currencies</legend>
				Here you can paste in currencies values. All values should be relative to the US Dollar, and have the following example format:
<textarea class="form-control" rows="10" name="bulk_currencies">
USD United States Dollars		1.0000000000		1.0000000000
EUR Euro				1.2186191347		0.8206009339
GBP United Kingdom Pounds		1.7684362222		0.5654713399
CAD Canada Dollars			0.8253906445		1.2115475341
AUD Australia Dollars		0.7620792397		1.3121995036
JPY Japan Yen				0.0089098765		112.2350011215
</textarea>
<span class="help-block">[three letter abbreviatinon] [Name separated by at most one space] [dollar/currency] [currency/dollar]</span>

<br/><input type="submit" name="bulk_submit" value="Bulk Update"  class="btn btn-default"/>
</fieldset>
</form>

<?=zen_draw_form_admin('currencies', FILENAME_CURRENCIES, 'action=api')?>
<fieldset>
	<legend>Exchangerates API</legend>
<p>API Key from https://exchangeratesapi.io</p>
<input type="text" name="exchangeratesapi_key" value="<?php echo $gCommerceSystem->getConfig('CURRENCY_EXCHANGERATESAPI_KEY')?>" class="form-control">
<input type="submit" name="exapikey_submit" value="Save"  class="btn btn-default"/>
</fieldset>
</form>

	</div>
	<div class="col-md-4">

<?php
	$heading = array();
	$contents = array();
	
	switch ($action) {
		case 'new':
			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_CURRENCY . '</b>');

			$contents = array('form' => zen_draw_form_admin('currencies', FILENAME_CURRENCIES, 'action=insert'));
			$contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
			$contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_TITLE . '<br>' . zen_draw_input_field('title'));
			$contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_CODE . '<br>' . zen_draw_input_field('code'));
			$contents[] = array('text' => '<br>' . tra( 'Symbol Left:' ) . '<br>' . zen_draw_input_field('symbol_left'));
			$contents[] = array('text' => '<br>' . tra( 'Symbol Right:' ) . '<br>' . zen_draw_input_field('symbol_right'));
			$contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_DECIMAL_POINT . '<br>' . zen_draw_input_field('decimal_point'));
			$contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_THOUSANDS_POINT . '<br>' . zen_draw_input_field('thousands_point'));
			$contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_DECIMAL_PLACES . '<br>' . zen_draw_input_field('decimal_places'));
			$contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_VALUE . '<br>' . zen_draw_input_field('currency_value'));
			$contents[] = array('text' => '<br>' . zen_draw_checkbox_field('default') . ' ' . TEXT_INFO_SET_AS_DEFAULT);
			$contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_insert.gif', IMAGE_INSERT) . ' <a href="' . zen_href_link_admin(FILENAME_CURRENCIES) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
			break;
		case 'edit':				
			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_CURRENCY . '</b>');
			
			$contents = array('form' => zen_draw_form_admin('currencies', FILENAME_CURRENCIES, 'code=' . $activeCode . '&action=save'));
			$contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
			$contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_TITLE . '<br>' . zen_draw_input_field('title', $activeCurrency['title'],'','text',$reinsert_value=false));
			$contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_CODE . '<br>' . zen_draw_input_field('code', $activeCurrency['code']));
			$contents[] = array('text' => '<br>' . tra( 'Symbol Left:' ) . '<br>' . zen_draw_input_field('symbol_left', htmlspecialchars($activeCurrency['symbol_left'])));
			$contents[] = array('text' => '<br>' . tra( 'Symbol Right:' ) . '<br>' . zen_draw_input_field('symbol_right', htmlspecialchars($activeCurrency['symbol_right'])));
			$contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_DECIMAL_POINT . '<br>' . zen_draw_input_field('decimal_point', $activeCurrency['decimal_point']));
			$contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_THOUSANDS_POINT . '<br>' . zen_draw_input_field('thousands_point', $activeCurrency['thousands_point']));
			$contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_DECIMAL_PLACES . '<br>' . zen_draw_input_field('decimal_places', $activeCurrency['decimal_places']));
			$contents[] = array('text' => '<br>' . TEXT_INFO_CURRENCY_VALUE . '<br>' . zen_draw_input_field('currency_value', $activeCurrency['currency_value']));
			$contents[] = array('text' => zen_draw_checkbox_field('default', 'on', (DEFAULT_CURRENCY == $activeCurrency['code']), NULL, TEXT_INFO_SET_AS_DEFAULT ) );
			$contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . zen_href_link_admin(FILENAME_CURRENCIES, 'code=' . $activeCode) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' );
			break;
		case 'delete':
			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_CURRENCY . '</b>');

			$contents[] = array('text' => tra( 'Are you sure you want to delete this currency?' ) );
			$contents[] = array('text' => '<br><b>' . $activeCurrency['title'] . '</b>');
			$contents[] = array('align' => 'center', 'text' => '<br>' . (($activeCurrency['code'] != DEFAULT_CURRENCY ) ? '<a href="' . zen_href_link_admin(FILENAME_CURRENCIES, 'code=' . $activeCode . '&action=deleteconfirm') . '" class="btn btn-default">' . tra( 'Delete' ) . '</a>' : tra( 'The default currency cannot be deleted.' ) ) . ' <a href="' . zen_href_link_admin(FILENAME_CURRENCIES) . '" class="btn btn-default">' . tra( 'Cancel' ) . '</a>');
			break;
	}

	if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
		echo '						<td width="25%" valign="top">' . "\n";

		$box = new box;
		echo $box->infoBox($heading, $contents);

		echo '						</td>' . "\n";
	}
?>

	</div>
</div>

<!-- footer //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>
